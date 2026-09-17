<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Importa dados do banco legado (PHP Maker) para o novo schema Laravel.
 *
 * Pré-requisito:
 *   mysql -u root peruibe_legacy < peruibe_conselhos-atualizada.sql
 *
 * Uso:
 *   php artisan import:peruibe-legacy
 *   php artisan import:peruibe-legacy --fresh  (apaga dados existentes antes)
 */
class ImportLegacyPeruibe extends Command
{
    protected $signature = 'import:peruibe-legacy
                            {--fresh : Limpa dados existentes de Peruíbe antes de importar}
                            {--dry-run : Mostra contagens sem gravar}';

    protected $description = 'Importa dados do banco legado (PHP Maker) de Peruíbe para o novo schema';

    /** Mapeamento: legacy id_conselho → novo conselho_id */
    private array $conselhoMap = [];

    /** Mapeamento: legacy id_conselheiro → novo conselheiro_id */
    private array $conselheirosMap = [];

    /** Mapeamento: legacy tp_convocacao id → novo tipos_reuniao id */
    private array $tipoReuniaoMap = [];

    /** Mapeamento: legacy tp_doc id → novo tipos_documento id */
    private array $tipoDocMap = [];

    private bool $dryRun = false;

    private int $municipioId;

    /** C09: relatório de rejeições para diagnóstico pós-importação */
    private array $rejeicoes = [];

    public function handle(): int
    {
        $this->dryRun = (bool) $this->option('dry-run');

        // ── Testa conexão com o banco legado ──────────────────────────────────
        try {
            DB::connection('legacy')->getPdo();
        } catch (\Exception $e) {
            $this->error('Não foi possível conectar ao banco legado (peruibe_legacy).');
            $this->error($e->getMessage());
            $this->line('');
            $this->line('Execute primeiro:');
            $this->line('  mysql -u root --default-character-set=utf8mb4 -e "CREATE DATABASE IF NOT EXISTS peruibe_legacy CHARACTER SET utf8mb4"');
            $this->line('  mysql -u root peruibe_legacy < D:/projetos/CONSELHOS/peruibe_conselhos-atualizada.sql');
            return Command::FAILURE;
        }

        $this->info('Conectado ao banco legado. Iniciando importação...');
        $this->line('');

        // C09: --fresh proibido em produção — apaga dados antes da transação principal
        if ($this->option('fresh')) {
            if (app()->isProduction()) {
                $this->error('--fresh não é permitido em produção. Faça backup e use um ambiente isolado.');
                return Command::FAILURE;
            }

            $this->warn('--fresh solicitado em ambiente: ' . app()->environment());
            if (! $this->confirm('Todos os dados de Peruíbe serão apagados antes da importação. Continuar?')) {
                return Command::FAILURE;
            }

            $this->limparDadosPeruibe();
        }

        $this->info('Iniciando transação principal...');

        DB::transaction(function () {
            $this->importarMunicipio();
            $this->importarTiposReuniao();
            $this->importarTiposDocumento();
            $this->importarTiposLegislacao();
            $this->importarConselheiros();
            $this->importarConselhos();
            $this->importarComposicao();
            $this->importarReunioesConvocacoes();
            $this->importarReuniaoLinks();
            $this->importarDocumentosArquivos();
            $this->importarLegislacao();
        });

        $this->line('');
        $this->info('✓ Importação concluída com sucesso!');

        // C09: relatório de rejeições
        if (! empty($this->rejeicoes)) {
            $this->newLine();
            $this->warn(count($this->rejeicoes) . ' registros rejeitados:');
            $this->table(['Tabela', 'Legacy ID', 'Motivo'], $this->rejeicoes);
        }

        $this->table(
            ['Tabela', 'Registros'],
            [
                ['municipios',      DB::table('municipios')->where('slug', 'peruibe')->count()],
                ['conselhos',       DB::table('conselhos')->where('municipio_id', $this->municipioId ?? 0)->count()],
                ['conselheiros',    DB::table('conselheiros')->where('municipio_id', $this->municipioId ?? 0)->count()],
                ['composicao',      DB::table('composicao')->whereIn('conselho_id', array_values($this->conselhoMap))->count()],
                ['reunioes',        DB::table('reunioes')->whereIn('conselho_id', array_values($this->conselhoMap))->count()],
                ['reuniao_links',   DB::table('reuniao_links')->whereIn('reuniao_id', DB::table('reunioes')->whereIn('conselho_id', array_values($this->conselhoMap))->pluck('id'))->count()],
                ['documentos',      DB::table('documentos')->whereIn('conselho_id', array_values($this->conselhoMap))->count()],
                ['legislacao',      DB::table('legislacao')->whereIn('conselho_id', array_values($this->conselhoMap))->count()],
            ]
        );

        return Command::SUCCESS;
    }

    // ── Limpeza ───────────────────────────────────────────────────────────────

    private function limparDadosPeruibe(): void
    {
        $this->warn('--fresh: limpando dados existentes de Peruíbe...');

        $municipio = DB::table('municipios')->where('slug', 'peruibe')->first();
        if (! $municipio) {
            return;
        }

        $conselhoIds = DB::table('conselhos')->where('municipio_id', $municipio->id)->pluck('id');

        // Query Builder não tem forceDelete; usamos delete() e também limpamos o deleted_at via whereNotNull
        DB::table('legislacao')->whereIn('conselho_id', $conselhoIds)->delete();
        DB::table('documentos')->whereIn('conselho_id', $conselhoIds)->delete();
        DB::table('reunioes')->whereIn('conselho_id', $conselhoIds)->delete();
        DB::table('composicao')->whereIn('conselho_id', $conselhoIds)->delete();
        DB::table('conselhos')->where('municipio_id', $municipio->id)->delete();
        DB::table('conselheiros')->where('municipio_id', $municipio->id)->delete();
        DB::table('municipios')->where('id', $municipio->id)->delete();

        $this->line('  Dados removidos.');
    }

    // ── Município ─────────────────────────────────────────────────────────────

    private function importarMunicipio(): void
    {
        $this->info('→ Município...');

        $existing = DB::table('municipios')->where('slug', 'peruibe')->first();
        if ($existing) {
            $this->municipioId = $existing->id;
            $this->line('  Já existe (id=' . $this->municipioId . '). Pulando.');
            return;
        }

        if (! $this->dryRun) {
            $this->municipioId = DB::table('municipios')->insertGetId([
                'nome'       => 'Peruíbe',
                'slug'       => 'peruibe',
                'sigla'      => 'PRB',
                'uf'         => 'SP',
                'ativo'      => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->line("  Criado município Peruíbe/SP (id={$this->municipioId})");
    }

    // ── Tipos de Reunião ──────────────────────────────────────────────────────

    private function importarTiposReuniao(): void
    {
        $this->info('→ Tipos de reunião...');

        $rows = DB::connection('legacy')->table('tp_convocacao')->get();
        $count = 0;

        foreach ($rows as $row) {
            $existing = DB::table('tipos_reuniao')
                ->where('municipio_id', $this->municipioId)
                ->where('nome', $row->Nome)
                ->first();

            if ($existing) {
                $this->tipoReuniaoMap[$row->id] = $existing->id;
                continue;
            }

            if (! $this->dryRun) {
                $newId = DB::table('tipos_reuniao')->insertGetId([
                    'municipio_id' => $this->municipioId,
                    'nome'         => $row->Nome,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
                $this->tipoReuniaoMap[$row->id] = $newId;
            }
            $count++;
        }

        $this->line("  {$count} tipos de reunião importados.");
    }

    // ── Tipos de Documento ────────────────────────────────────────────────────

    private function importarTiposDocumento(): void
    {
        $this->info('→ Tipos de documento...');

        $rows = DB::connection('legacy')->table('tp_doc')->get();
        $count = 0;

        foreach ($rows as $row) {
            $existing = DB::table('tipos_documento')
                ->where('municipio_id', $this->municipioId)
                ->where('nome', $row->Tipo)
                ->first();

            if ($existing) {
                $this->tipoDocMap[$row->id] = $existing->id;
                continue;
            }

            if (! $this->dryRun) {
                $newId = DB::table('tipos_documento')->insertGetId([
                    'municipio_id' => $this->municipioId,
                    'nome'         => $row->Tipo,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
                $this->tipoDocMap[$row->id] = $newId;
            }
            $count++;
        }

        $this->line("  {$count} tipos de documento importados.");
    }

    // ── Tipos de Legislação ───────────────────────────────────────────────────

    private function importarTiposLegislacao(): void
    {
        $this->info('→ Tipos de legislação...');

        $rows = DB::connection('legacy')->table('tp_legislacao')->get();
        $count = 0;

        foreach ($rows as $row) {
            $existing = DB::table('tipos_legislacao')
                ->where('municipio_id', $this->municipioId)
                ->where('nome', $row->Tipo)
                ->first();

            if ($existing) {
                continue;
            }

            if (! $this->dryRun) {
                DB::table('tipos_legislacao')->insert([
                    'municipio_id' => $this->municipioId,
                    'nome'         => $row->Tipo,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }
            $count++;
        }

        $this->line("  {$count} tipos de legislação importados.");
    }

    // ── Conselheiros ──────────────────────────────────────────────────────────

    private function importarConselheiros(): void
    {
        $this->info('→ Conselheiros...');

        $rows = DB::connection('legacy')->table('conselheiros')->get();
        $count = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            // Trata e-mails placeholder gerados pelo sistema legado
            $email = $row->email;
            if ($email && str_contains($email, 'sem-email+')) {
                $email = null;
            }

            // Verifica se já existe por legacy_id ou email
            $existing = DB::table('conselheiros')
                ->where('municipio_id', $this->municipioId)
                ->where(function ($q) use ($row, $email) {
                    $q->where('legacy_id', $row->id_conselheiro);
                    if ($email) {
                        $q->orWhere('email', $email);
                    }
                })
                ->first();

            if ($existing) {
                $this->conselheirosMap[$row->id_conselheiro] = $existing->id;
                $skipped++;
                continue;
            }

            if (! $this->dryRun) {
                $newId = DB::table('conselheiros')->insertGetId([
                    'municipio_id' => $this->municipioId,
                    'legacy_id'    => $row->id_conselheiro,
                    'nome'         => $row->nome,
                    'email'        => $email,
                    'telefone'     => $this->normalizar($row->Telefone ?? null, 50),
                    'ativo'        => (bool) ($row->ativo ?? true),
                    'created_at'   => $row->criado_em ?? now(),
                    'updated_at'   => $row->atualizado_em ?? now(),
                ]);
                $this->conselheirosMap[$row->id_conselheiro] = $newId;
            }
            $count++;
        }

        $this->line("  {$count} conselheiros importados, {$skipped} já existiam.");
    }

    // ── Conselhos ─────────────────────────────────────────────────────────────

    private function importarConselhos(): void
    {
        $this->info('→ Conselhos...');

        $rows = DB::connection('legacy')->table('conselho')->get();
        $count = 0;

        foreach ($rows as $row) {
            $slug = Str::slug($row->sigla ?: $row->Nome);

            $existing = DB::table('conselhos')
                ->where('municipio_id', $this->municipioId)
                ->where('legacy_id', $row->id)
                ->first();

            if ($existing) {
                $this->conselhoMap[$row->id] = $existing->id;
                continue;
            }

            // Garante slug único
            $baseSlug = $slug;
            $suffix   = 1;
            while (DB::table('conselhos')->where('municipio_id', $this->municipioId)->where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $suffix++;
            }

            // Extrai descrição: legado usa campo `apresentacao` com HTML
            $descricao = $row->apresentacao
                ? trim(strip_tags($row->apresentacao))
                : null;
            if ($descricao && strlen($descricao) > 2000) {
                $descricao = substr($descricao, 0, 1997) . '...';
            }

            $logoUrl = ($row->Logotipo && trim($row->Logotipo) !== '')
                ? $this->urlLogo(trim($row->Logotipo))
                : null;

            if (! $this->dryRun) {
                $newId = DB::table('conselhos')->insertGetId([
                    'municipio_id' => $this->municipioId,
                    'legacy_id'    => $row->id,
                    'nome'         => $row->Nome,
                    'slug'         => $slug,
                    'sigla'        => $this->normalizar($row->sigla, 20),
                    'tipo'         => 'Municipal',
                    'descricao'    => $descricao,
                    'email'        => $this->normalizar($row->Email, 255),
                    'telefone'     => $this->normalizar($row->Telefone ?? null, 50),
                    'endereco'     => $this->normalizar($row->Endereco ?? null, 255),
                    'ativo'        => (bool) $row->Ativo,
                    'logo_url'     => $logoUrl,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
                $this->conselhoMap[$row->id] = $newId;
            }
            $count++;

            $this->line("  [{$row->id}] {$row->Nome} → slug:{$slug}");
        }

        $this->line("  Total: {$count} conselhos importados.");
    }

    // ── Composição ────────────────────────────────────────────────────────────

    private function importarComposicao(): void
    {
        $this->info('→ Composição (membros)...');

        $rows = DB::connection('legacy')
            ->table('composicao')
            ->orderBy('id_conselho')
            ->orderBy('id_membro')
            ->get();

        $count   = 0;
        $skipped = 0;

        // Valores válidos do enum no novo schema
        $tiposValidos = ['PRESIDENTE', 'VICE_PRESIDENTE', 'SECRETARIO', 'MEMBRO', 'SUPLENTE'];

        foreach ($rows as $row) {
            $conselhoId = $this->conselhoMap[$row->id_conselho] ?? null;
            if (! $conselhoId) {
                $this->rejeicoes[] = ['tabela' => 'composicao', 'legacy_id' => $row->id_conselho ?? '?', 'motivo' => 'conselho não mapeado'];
                continue;
            }

            $conselheirosId = isset($row->id_conselheiro)
                ? ($this->conselheirosMap[$row->id_conselheiro] ?? null)
                : null;

            // Tipo vem diretamente do campo Tipo do legado
            $tipoRaw = strtoupper(trim($row->Tipo ?? 'MEMBRO'));
            $tipo = in_array($tipoRaw, $tiposValidos) ? $tipoRaw : 'MEMBRO';

            // Verifica duplicata (unique: conselho+conselheiro+tipo)
            if ($conselheirosId) {
                $dup = DB::table('composicao')
                    ->where('conselho_id', $conselhoId)
                    ->where('conselheiro_id', $conselheirosId)
                    ->where('tipo', $tipo)
                    ->whereNull('deleted_at')
                    ->exists();

                if ($dup) {
                    $skipped++;
                    continue;
                }
            }

            $nomeExibicao = $this->normalizar($row->Nome, 255)
                ?: ($conselheirosId ? DB::table('conselheiros')->where('id', $conselheirosId)->value('nome') : null);

            if (! $this->dryRun) {
                DB::table('composicao')->insert([
                    'conselho_id'     => $conselhoId,
                    'conselheiro_id'  => $conselheirosId,
                    'tipo'            => $tipo,
                    'nome_exibicao'   => $nomeExibicao,
                    'email_exibicao'  => $this->normalizar($row->Email ?? null, 255),
                    'telefone_contato'=> $this->normalizar($row->Telefone ?? null, 20),
                    'data_nomeacao'   => $this->parseDate($row->Data_nomeacao ?? null),
                    'decreto_nomeacao'=> $this->normalizar($row->Decreto_nomecao ?? null, 255),
                    'observacoes'     => $row->Obs ?: null,
                    'ativo'           => true,
                    'legacy_id'       => $row->id_membro,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }
            $count++;
        }

        $this->line("  {$count} membros importados, {$skipped} duplicatas ignoradas.");
    }

    // ── URL base do sistema legado ────────────────────────────────────────────

    private const LEGACY_BASE = 'https://conselhos.perui.be';

    private function urlArquivo(string $arquivo): string
    {
        return self::LEGACY_BASE . '/arquivos_conselhos/' . rawurlencode($arquivo);
    }

    private function urlLei(string $arquivo): string
    {
        return self::LEGACY_BASE . '/leis_conselhos/' . rawurlencode($arquivo);
    }

    private function urlLogo(string $arquivo): string
    {
        return self::LEGACY_BASE . '/fotos/' . rawurlencode($arquivo);
    }

    // ── Reuniões (convocações) ────────────────────────────────────────────────

    private function importarReunioesConvocacoes(): void
    {
        $this->info('→ Reuniões (convocações)...');

        $rows = DB::connection('legacy')->table('convocacoes')->get();
        $count = 0;

        foreach ($rows as $row) {
            $conselhoId = $this->conselhoMap[$row->id_conselho] ?? null;
            if (! $conselhoId) {
                continue;
            }

            // Evita duplicatas por legacy_id
            if (DB::table('reunioes')->where('legacy_id', $row->id)->exists()) {
                continue;
            }

            $dataHora = $this->parseDateTime($row->Data);

            // Status: se a data já passou → realizada; caso contrário → agendada
            $status = $dataHora && $dataHora < now() ? 'realizada' : 'agendada';

            $tipoId = $this->tipoReuniaoMap[$row->Tipo ?? null] ?? null;

            // Pauta: strip HTML do legado
            $pauta = $row->Pauta ? trim(strip_tags($row->Pauta)) : null;

            if (! $this->dryRun) {
                DB::table('reunioes')->insert([
                    'conselho_id' => $conselhoId,
                    'tipo_id'     => $tipoId,
                    'data_hora'   => $dataHora,
                    'local'       => $this->normalizar($row->Local ?? null, 255),
                    'pauta'       => $pauta,
                    'status'      => $status,
                    'legacy_id'   => $row->id,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
            $count++;
        }

        $this->line("  {$count} reuniões importadas.");
    }

    // ── Links de reunião (videoconferência) ──────────────────────────────────

    private function importarReuniaoLinks(): void
    {
        $this->info('→ Links de reunião...');

        /** Mapeamento: legacy id_convocacao → novo reuniao_id */
        $reuniaoByLegacy = DB::table('reunioes')
            ->whereIn('conselho_id', array_values($this->conselhoMap))
            ->whereNotNull('legacy_id')
            ->pluck('id', 'legacy_id');

        $rows = DB::connection('legacy')->table('reuniao_links')->get();
        $count = 0;

        foreach ($rows as $row) {
            $reuniaoId = $reuniaoByLegacy[$row->id_convocacao] ?? null;
            if (! $reuniaoId) {
                continue;
            }

            if (DB::table('reuniao_links')->where('reuniao_id', $reuniaoId)->where('url', $row->url)->exists()) {
                continue;
            }

            // Detecta plataforma pelo domínio da URL
            $plataforma = match (true) {
                str_contains($row->url, 'meet.jit.si')   => 'Jitsi',
                str_contains($row->url, 'meet.google')   => 'Google Meet',
                str_contains($row->url, 'zoom.us')       => 'Zoom',
                str_contains($row->url, 'teams.microsoft') => 'Teams',
                default                                   => null,
            };

            if (! $this->dryRun) {
                DB::table('reuniao_links')->insert([
                    'reuniao_id'  => $reuniaoId,
                    'url'         => $row->url,
                    'plataforma'  => $plataforma,
                    'created_at'  => $row->criado_em ?? now(),
                    'updated_at'  => $row->criado_em ?? now(),
                ]);
            }
            $count++;
        }

        // Convocações com Anexos → link direto para o arquivo
        $convocacoes = DB::connection('legacy')
            ->table('convocacoes')
            ->whereNotNull('Anexos')
            ->where('Anexos', '!=', '')
            ->get();

        foreach ($convocacoes as $row) {
            $reuniaoId = $reuniaoByLegacy[$row->id] ?? null;
            if (! $reuniaoId) {
                continue;
            }

            $arquivos = array_filter(array_map('trim', explode(',', $row->Anexos)));
            foreach ($arquivos as $arquivo) {
                $url = $this->urlArquivo($arquivo);
                if (DB::table('reuniao_links')->where('reuniao_id', $reuniaoId)->where('url', $url)->exists()) {
                    continue;
                }
                if (! $this->dryRun) {
                    DB::table('reuniao_links')->insert([
                        'reuniao_id'  => $reuniaoId,
                        'url'         => $url,
                        'plataforma'  => 'Anexo',
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                }
                $count++;
            }
        }

        $this->line("  {$count} links de reunião importados.");
    }

    // ── Documentos (arquivos) ────────────────────────────────────────────────

    private function importarDocumentosArquivos(): void
    {
        $this->info('→ Documentos (arquivos)...');

        $rows = DB::connection('legacy')->table('arquivos')->get();
        $count = 0;

        foreach ($rows as $row) {
            $conselhoId = $this->conselhoMap[$row->id_conselho] ?? null;
            if (! $conselhoId) {
                continue;
            }

            if (DB::table('documentos')->where('legacy_id', $row->id)->exists()) {
                continue;
            }

            $tipoDocId = $this->tipoDocMap[$row->Tipo] ?? null;
            // O portal legado (PHP Maker) exibia TODOS os arquivos publicamente,
            // independente do campo `Publico` (que era DEFAULT 0 e não controlava acesso).
            // Portanto, todos os documentos legados são importados como públicos.
            // Documentos criados manualmente no novo sistema seguem a regra padrão (privado).
            $publico = true;

            $arquivoUrl = ($row->Arquivo && trim($row->Arquivo) !== '')
                ? $this->urlArquivo(trim($row->Arquivo))
                : null;

            if (! $this->dryRun) {
                DB::table('documentos')->insert([
                    'conselho_id'       => $conselhoId,
                    'tipo_documento_id' => $tipoDocId,
                    'titulo'            => $this->normalizar($row->Titulo, 255),
                    'descricao'         => $this->normalizar($row->Observacoes ?? null, 1000),
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

        $this->line("  {$count} documentos importados.");
    }

    // ── Legislação ────────────────────────────────────────────────────────────

    private function importarLegislacao(): void
    {
        $this->info('→ Legislação...');

        $rows = DB::connection('legacy')->table('legislacao')->get();
        $count = 0;

        // Mapa: legacy tp_legislacao id → nome do tipo
        $tiposLeg = DB::connection('legacy')->table('tp_legislacao')
            ->pluck('Tipo', 'id');

        foreach ($rows as $row) {
            $conselhoId = $this->conselhoMap[$row->Conselho] ?? null;
            if (! $conselhoId) {
                continue;
            }

            // Verifica duplicata por título+conselho
            if (DB::table('legislacao')
                ->where('conselho_id', $conselhoId)
                ->where('titulo', $row->Titulo)
                ->exists()
            ) {
                continue;
            }

            // Quando Tipo é NULL no legado, não atribuímos um tipo por suposição.
            // O fallback anterior era 'Lei', o que classificava incorretamente
            // documentos como "DOCUMENTAÇÃO CMAS 2024/2025" como legislação.
            $tipoNome = isset($row->Tipo) ? ($tiposLeg[$row->Tipo] ?? null) : null;

            // Busca tipo_legislacao apenas quando o tipo é conhecido
            $tipoLegId = $tipoNome
                ? DB::table('tipos_legislacao')
                    ->where('municipio_id', $this->municipioId)
                    ->where('nome', $tipoNome)
                    ->value('id')
                : null;

            $arquivoUrl = ($row->Arquivo && trim($row->Arquivo) !== '')
                ? $this->urlLei(trim($row->Arquivo))
                : null;

            // Se tem link externo, usa ele; se não, usa o arquivo local
            $linkFinal = $this->normalizar($row->Link ?? null, 500)
                ?: $arquivoUrl;

            if (! $this->dryRun) {
                DB::table('legislacao')->insert([
                    'conselho_id'       => $conselhoId,
                    'tipo_legislacao_id' => $tipoLegId,
                    'titulo'            => $this->normalizar($row->Titulo, 255),
                    'numero'            => $this->normalizar($row->Numero ?? null, 50),
                    'link'              => $linkFinal,
                    'arquivo_url'       => $arquivoUrl,
                    'publico'           => true, // Portal legado exibia toda legislação publicamente
                    'legacy_id'         => $row->id,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
            }
            $count++;
        }

        $this->line("  {$count} legislações importadas.");
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function normalizar(?string $value, int $maxLen): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }
        return mb_substr(trim($value), 0, $maxLen);
    }

    private function parseDate(?string $value): ?string
    {
        if (! $value || $value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
            return null;
        }
        try {
            return \Carbon\Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function parseDateTime(?string $value): ?\Carbon\Carbon
    {
        if (! $value || $value === '0000-00-00 00:00:00') {
            return null;
        }
        try {
            return \Carbon\Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }
}
