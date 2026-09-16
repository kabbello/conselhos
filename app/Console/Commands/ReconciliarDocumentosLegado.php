<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Reconcilia documentos do portal legado (PHP Maker / Peruíbe) com o novo sistema.
 *
 * CONTEXTO DO PROBLEMA
 * ---------------------
 * O portal legado exibia TODOS os documentos da tabela `arquivos` publicamente,
 * independente do campo `Publico` (que era 0 por padrão). O comando de importação
 * original tratou `Publico=0` como "privado" (publico=false), causando a discrepância:
 *   - Portal antigo: 73 docs (Conselho da Cidade), 52 (CAE), 71 (COMBEM), etc.
 *   - Portal novo:   10 docs, 1 doc, 16 docs, etc. (apenas os publicados manualmente)
 *
 * MODOS DE OPERAÇÃO
 * -----------------
 *   --dry-run          Mostra relatório sem gravar nada (padrão implícito)
 *   --publicar         Publica todos os docs legados ainda privados
 *   --importar-faltantes  Importa docs legados ausentes no novo DB (requer banco legado)
 *   --conselhos=7,20   Filtra por legacy_id dos conselhos (padrão: os 6 problemáticos)
 *
 * IDEMPOTÊNCIA
 * ------------
 * Todas as operações verificam o estado atual antes de agir.
 * Executar múltiplas vezes é seguro.
 *
 * PRÉ-REQUISITOS para --importar-faltantes
 *   mysql -u root --default-character-set=utf8mb4 \
 *         -e "CREATE DATABASE IF NOT EXISTS peruibe_legacy" && \
 *   mysql -u root peruibe_legacy < peruibe_conselhos.sql
 *   (+ configurar DB_LEGACY_* no .env)
 */
class ReconciliarDocumentosLegado extends Command
{
    protected $signature = 'documentos:reconciliar-legado
                            {--dry-run           : Exibe relatório sem gravar (default)}
                            {--publicar          : Publica documentos legados ainda privados}
                            {--importar-faltantes: Importa docs ausentes (requer banco legado)}
                            {--conselhos=        : legacy_ids separados por vírgula (default: 3,7,14,19,20,26)}';

    protected $description = 'Reconcilia documentos do portal legado de Peruíbe com o novo sistema';

    /** Mapeamento dos 6 conselhos auditados: legacy_id → nome */
    private const CONSELHOS_AUDITADOS = [
        7  => 'Conselho da Cidade',
        20 => 'CAE',
        14 => 'COMBEM',
        3  => 'FUNDEB',
        26 => 'CMAS',
        19 => 'CMS (Saúde)',
    ];

    /** Contagens esperadas no portal legado (referência da auditoria de 16/09/2026) */
    private const ESPERADO_LEGADO = [
        7  => 73,
        20 => 52,
        14 => 71,
        3  => 29,
        26 => 3,
        19 => 31,
    ];

    private bool $dryRun;

    public function handle(): int
    {
        $this->dryRun = ! $this->option('publicar') && ! $this->option('importar-faltantes');

        if ($this->dryRun) {
            $this->warn('MODO DRY-RUN — nenhuma alteração será gravada.');
            $this->line('  Use --publicar ou --importar-faltantes para executar ações.');
            $this->newLine();
        }

        // ── Determina conselhos alvo ─────────────────────────────────────────
        $legacyIds = $this->option('conselhos')
            ? array_map('intval', explode(',', $this->option('conselhos')))
            : array_keys(self::CONSELHOS_AUDITADOS);

        // ── Carrega mapa legacy_id → novo conselho_id ────────────────────────
        $conselhoMap = DB::table('conselhos')
            ->whereIn('legacy_id', $legacyIds)
            ->pluck('id', 'legacy_id')
            ->all();

        if (empty($conselhoMap)) {
            $this->error('Nenhum conselho encontrado com os legacy_ids informados.');
            $this->error('Verifique se a importação inicial já foi executada (import:peruibe-legacy).');
            return Command::FAILURE;
        }

        // ── Relatório por conselho ───────────────────────────────────────────
        $this->info('=== RELATÓRIO DE RECONCILIAÇÃO ===');
        $this->newLine();

        $totalPublicar  = 0;
        $totalFaltantes = 0;
        $publicados     = 0;
        $importados     = 0;

        foreach ($legacyIds as $legacyId) {
            $conselhoId = $conselhoMap[$legacyId] ?? null;
            if (! $conselhoId) {
                $this->warn("  [id={$legacyId}] Conselho não encontrado no novo DB — execute import:peruibe-legacy primeiro.");
                continue;
            }

            $nome     = self::CONSELHOS_AUDITADOS[$legacyId] ?? "Conselho id={$legacyId}";
            $esperado = self::ESPERADO_LEGADO[$legacyId] ?? '?';

            // Conta no novo DB
            $totalNovo    = DB::table('documentos')->where('conselho_id', $conselhoId)->whereNull('deleted_at')->count();
            $publicosNovo = DB::table('documentos')->where('conselho_id', $conselhoId)->where('publico', true)->whereNull('deleted_at')->count();
            $privadosNovo = $totalNovo - $publicosNovo;

            // Legacy IDs presentes no novo DB
            $legacyIdsPresentes = DB::table('documentos')
                ->where('conselho_id', $conselhoId)
                ->whereNotNull('legacy_id')
                ->whereNull('deleted_at')
                ->pluck('legacy_id')
                ->all();

            $legacyIdsPrivados = DB::table('documentos')
                ->where('conselho_id', $conselhoId)
                ->whereNotNull('legacy_id')
                ->where('publico', false)
                ->whereNull('deleted_at')
                ->pluck('legacy_id')
                ->all();

            $faltantes = $esperado === '?' ? '?' : max(0, $esperado - $totalNovo);
            $totalPublicar  += count($legacyIdsPrivados);
            $totalFaltantes += is_int($faltantes) ? $faltantes : 0;

            $this->line("<fg=cyan;options=bold>[{$nome}]</> (legacy_id={$legacyId} → novo_id={$conselhoId})");
            $this->line("  Portal legado mostrava : {$esperado} docs");
            $this->line("  Novo DB (total/público): {$totalNovo}/{$publicosNovo}");
            $this->line("  Privados com legacy_id : " . count($legacyIdsPrivados));
            $this->line("  Faltantes (estimado)   : {$faltantes}");

            if (! empty($legacyIdsPrivados)) {
                $this->line("  <fg=yellow>  → " . count($legacyIdsPrivados) . " docs legados podem ser publicados</>");
            }
            if ($faltantes > 0) {
                $this->line("  <fg=red>  → {$faltantes} docs ausentes — use --importar-faltantes</>");
            }

            // ── Publicar ─────────────────────────────────────────────────────
            if ($this->option('publicar') && ! empty($legacyIdsPrivados)) {
                $affected = DB::table('documentos')
                    ->where('conselho_id', $conselhoId)
                    ->whereIn('legacy_id', $legacyIdsPrivados)
                    ->where('publico', false)
                    ->whereNull('deleted_at')
                    ->update([
                        'publico'    => true,
                        'updated_at' => now(),
                    ]);
                $publicados += $affected;
                $this->line("  <fg=green>  ✓ {$affected} docs publicados.</>");
            }

            $this->newLine();
        }

        // ── Importar faltantes (requer banco legado) ─────────────────────────
        if ($this->option('importar-faltantes')) {
            $importados = $this->importarFaltantes($conselhoMap, $legacyIds);
        }

        // ── Resumo final ─────────────────────────────────────────────────────
        $this->info('=== RESUMO ===');
        $this->table(
            ['Ação', 'Contagem'],
            [
                ['Docs legados privados (candidatos a publicar)', $totalPublicar],
                ['Docs faltantes estimados', $totalFaltantes],
                $this->option('publicar') ? ['Docs publicados nesta execução', $publicados] : null,
                $this->option('importar-faltantes') ? ['Docs importados nesta execução', $importados] : null,
            ]
        );

        if ($this->dryRun && ($totalPublicar > 0 || $totalFaltantes > 0)) {
            $this->newLine();
            $this->warn('Para corrigir as discrepâncias:');
            if ($totalPublicar > 0) {
                $this->line('  php artisan documentos:reconciliar-legado --publicar');
            }
            if ($totalFaltantes > 0) {
                $this->line('  php artisan documentos:reconciliar-legado --importar-faltantes');
            }
        }

        return Command::SUCCESS;
    }

    // ── Importar docs ausentes via banco legado ──────────────────────────────

    private function importarFaltantes(array $conselhoMap, array $legacyIds): int
    {
        try {
            DB::connection('legacy')->getPdo();
        } catch (\Exception $e) {
            $this->error('Banco legado não acessível: ' . $e->getMessage());
            $this->error('Importe o dump primeiro:');
            $this->line('  mysql -u root peruibe_legacy < peruibe_conselhos.sql');
            return 0;
        }

        $this->info('→ Importando faltantes do banco legado...');

        // Mapa: legacy tp_doc id → novo tipo_documento id
        $municipioId = DB::table('conselhos')->where('id', reset($conselhoMap))->value('municipio_id');
        $tipoDocMap  = $this->construirTipoDocMap($municipioId);

        $rows = DB::connection('legacy')
            ->table('arquivos')
            ->whereIn('id_conselho', $legacyIds)
            ->get();

        $count = 0;

        foreach ($rows as $row) {
            $conselhoId = $conselhoMap[$row->id_conselho] ?? null;
            if (! $conselhoId) {
                continue;
            }

            // Idempotente: skip se já existe
            if (DB::table('documentos')->where('legacy_id', $row->id)->exists()) {
                continue;
            }

            $arquivoUrl = ($row->Arquivo && trim($row->Arquivo) !== '')
                ? 'https://conselhos.perui.be/arquivos_conselhos/' . rawurlencode(trim($row->Arquivo))
                : null;

            // A porta legada exibia TODOS publicamente; mantemos o mesmo comportamento
            $publico = true;

            if (! $this->dryRun) {
                DB::table('documentos')->insert([
                    'conselho_id'       => $conselhoId,
                    'tipo_documento_id' => $tipoDocMap[$row->Tipo] ?? null,
                    'titulo'            => mb_substr(trim($row->Titulo), 0, 255),
                    'descricao'         => $row->Observacoes ? mb_substr(trim($row->Observacoes), 0, 1000) : null,
                    'data_documento'    => $this->parseDate($row->Data ?? null),
                    'data_publicacao'   => $this->parseDate($row->Data ?? null),
                    'publico'           => $publico,
                    'arquivo_url'       => $arquivoUrl,
                    'legacy_id'         => $row->id,
                    'created_at'        => $row->atualizado_em ?? now(),
                    'updated_at'        => $row->atualizado_em ?? now(),
                ]);
            }
            $count++;
        }

        $this->line("  {$count} documentos faltantes importados.");
        return $count;
    }

    private function construirTipoDocMap(?int $municipioId): array
    {
        if (! $municipioId) {
            return [];
        }

        $tiposLegados = DB::connection('legacy')->table('tp_doc')->get();
        $map = [];

        foreach ($tiposLegados as $tipo) {
            $id = DB::table('tipos_documento')
                ->where('municipio_id', $municipioId)
                ->where('nome', $tipo->Tipo)
                ->value('id');
            if ($id) {
                $map[$tipo->id] = $id;
            }
        }

        return $map;
    }

    private function parseDate(?string $value): ?string
    {
        if (! $value || str_starts_with($value, '0000')) {
            return null;
        }
        try {
            return \Carbon\Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
