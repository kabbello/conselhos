<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Corrige dados incorretos herdados da importação do portal legado de Peruíbe.
 *
 * PROBLEMAS CORRIGIDOS
 * --------------------
 * 1. Telefones truncados a 20 chars (coluna foi ampliada para 50 na migration).
 *    Valores corretos extraídos do dump peruibe_conselhos.sql.
 *
 * 2. E-mails inválidos de formato: "habitacao.conselhos.perui.be" (sem @).
 *    Ação: limpa para NULL.
 *
 * 3. E-mail com typo de TLD: "cmpdcndeperuibe@gmail.cpm".
 *    Ação: limpa para NULL.
 *
 * 4. E-mails no domínio @*.perui.be (plataforma legada — domínio inexistente).
 *    Ação: limpa para NULL.
 *
 * 4. Legislação "DOCUMENTAÇÃO CMAS 2024/2025" classificada incorretamente
 *    como "Lei" por causa do fallback ao tipo NULL na importação.
 *    Ação: remove tipo_legislacao_id (deixa sem tipo).
 *
 * IDEMPOTÊNCIA
 * ------------
 * Cada correção verifica o estado atual antes de aplicar.
 * Executar múltiplas vezes é seguro.
 *
 * USO
 * ---
 *   php artisan dados:corrigir-legado-peruibe --dry-run
 *   php artisan dados:corrigir-legado-peruibe
 */
class CorrigirDadosLegadoPeruibe extends Command
{
    protected $signature = 'dados:corrigir-legado-peruibe
                            {--dry-run : Exibe o que seria alterado sem gravar}';

    protected $description = 'Corrige dados herdados da importação do legado de Peruíbe (telefones, e-mails, tipos)';

    /**
     * Valores corretos extraídos do dump peruibe_conselhos.sql.
     * Chave: legacy_id do conselho → ['telefone' => valor_correto]
     */
    private const TELEFONES_CORRETOS = [
        6  => ['sigla' => 'CMDCA',  'telefone' => '(13) 3451-1000, Ramal 5257'],
        22 => ['sigla' => 'CONDEF', 'telefone' => '133451-1000 ramal 5258'],
        26 => ['sigla' => 'CMAS',   'telefone' => '1343511000 ramal 52527'],
    ];

    /**
     * E-mails inválidos que devem ser limpos.
     * Chave: legacy_id → descrição do problema.
     * O e-mail correto deve ser cadastrado manualmente pelo admin.
     */
    private const EMAILS_INVALIDOS = [
        9  => ['sigla' => 'CMCNPIRP', 'email_atual' => 'cmpdcndeperuibe@gmail.cpm',      'problema' => 'TLD incorreto (.cpm → .com?)'],
        10 => ['sigla' => 'CMH',      'email_atual' => 'habitacao.conselhos.perui.be',    'problema' => 'Sem @ — não é um endereço de e-mail'],
    ];

    /**
     * Legislação com tipo_legislacao_id incorreto por fallback 'Lei'.
     * Formato: [municipio_slug, conselho_legacy_id, titulo_legislacao]
     */
    private const LEGISLACAO_TIPO_ERRADO = [
        ['municipio' => 'peruibe', 'conselho_legacy_id' => 26, 'titulo' => 'DOCUMENTAÇÃO CMAS 2024/2025'],
    ];

    private bool $dryRun;

    public function handle(): int
    {
        $this->dryRun = (bool) $this->option('dry-run');

        if ($this->dryRun) {
            $this->warn('MODO DRY-RUN — nenhuma alteração será gravada.');
            $this->newLine();
        }

        $municipio = DB::table('municipios')->where('slug', 'peruibe')->first();
        if (! $municipio) {
            $this->error('Município "peruibe" não encontrado. Execute import:peruibe-legacy primeiro.');
            return Command::FAILURE;
        }

        $this->corrigirTelefones($municipio->id);
        $this->corrigirEmailsInvalidos($municipio->id);
        $this->corrigirTipoLegislacao($municipio->id);

        $this->newLine();
        $this->info('Concluído' . ($this->dryRun ? ' (dry-run — nada gravado)' : '.'));

        return Command::SUCCESS;
    }

    // ── Telefones truncados ───────────────────────────────────────────────────

    private function corrigirTelefones(int $municipioId): void
    {
        $this->info('1. Telefones truncados:');

        foreach (self::TELEFONES_CORRETOS as $legacyId => $dados) {
            $conselho = DB::table('conselhos')
                ->where('municipio_id', $municipioId)
                ->where('legacy_id', $legacyId)
                ->first();

            if (! $conselho) {
                $this->warn("   [{$dados['sigla']}] Conselho não encontrado (legacy_id={$legacyId})");
                continue;
            }

            $atual   = $conselho->telefone;
            $correto = $dados['telefone'];

            if ($atual === $correto) {
                $this->line("   [{$dados['sigla']}] OK — já está correto: {$correto}");
                continue;
            }

            $this->line("   [{$dados['sigla']}] Corrigindo:");
            $this->line("     Atual:   {$atual}");
            $this->line("     Correto: {$correto}");

            if (! $this->dryRun) {
                DB::table('conselhos')
                    ->where('id', $conselho->id)
                    ->update(['telefone' => $correto, 'updated_at' => now()]);
                $this->line("     → Gravado.");
            }
        }
    }

    // ── E-mails inválidos ─────────────────────────────────────────────────────

    private function corrigirEmailsInvalidos(int $municipioId): void
    {
        $this->newLine();
        $this->info('2. E-mails inválidos:');

        foreach (self::EMAILS_INVALIDOS as $legacyId => $dados) {
            $conselho = DB::table('conselhos')
                ->where('municipio_id', $municipioId)
                ->where('legacy_id', $legacyId)
                ->first();

            if (! $conselho) {
                $this->warn("   [{$dados['sigla']}] Conselho não encontrado");
                continue;
            }

            if ($conselho->email === null) {
                $this->line("   [{$dados['sigla']}] E-mail já está NULL — pendente de cadastro correto.");
                continue;
            }

            $this->line("   [{$dados['sigla']}] E-mail inválido: \"{$conselho->email}\"");
            $this->line("     Problema: {$dados['problema']}");
            $this->line("     Ação: definir para NULL (necessita cadastro manual do endereço correto)");

            if (! $this->dryRun) {
                DB::table('conselhos')
                    ->where('id', $conselho->id)
                    ->update(['email' => null, 'updated_at' => now()]);
                $this->line("     → Gravado (e-mail removido).");
            }
        }

        // E-mails no domínio @conselhos.perui.be (plataforma antiga — domínio inexistente)
        $this->newLine();
        $legacyDomain = DB::table('conselhos as c')
            ->join('municipios as m', 'm.id', 'c.municipio_id')
            ->where('m.slug', 'peruibe')
            ->where('c.email', 'like', '%@%.perui.be')
            ->whereNotNull('c.email')
            ->select('c.id', 'c.sigla', 'c.legacy_id', 'c.email')
            ->get();

        if ($legacyDomain->isEmpty()) {
            $this->line('   E-mails @*.perui.be: nenhum encontrado — já limpos.');
        } else {
            $this->line('   E-mails no domínio @*.perui.be (domínio inexistente — removendo):');
            foreach ($legacyDomain as $row) {
                $this->line("     [{$row->sigla}] {$row->email}");
                if (! $this->dryRun) {
                    DB::table('conselhos')
                        ->where('id', $row->id)
                        ->update(['email' => null, 'updated_at' => now()]);
                    $this->line('       → Removido.');
                }
            }
        }
    }

    // ── Tipo de legislação incorreto ──────────────────────────────────────────

    private function corrigirTipoLegislacao(int $municipioId): void
    {
        $this->newLine();
        $this->info('3. Legislação com tipo classificado incorretamente:');

        foreach (self::LEGISLACAO_TIPO_ERRADO as $item) {
            // Localiza conselho
            $conselho = DB::table('conselhos')
                ->where('municipio_id', $municipioId)
                ->where('legacy_id', $item['conselho_legacy_id'])
                ->first();

            if (! $conselho) {
                $this->warn("   Conselho legacy_id={$item['conselho_legacy_id']} não encontrado.");
                continue;
            }

            // Localiza registro de legislação
            $leg = DB::table('legislacao')
                ->where('conselho_id', $conselho->id)
                ->where('titulo', $item['titulo'])
                ->first();

            if (! $leg) {
                $this->line("   \"{$item['titulo']}\": não encontrado no DB.");
                continue;
            }

            if ($leg->tipo_legislacao_id === null) {
                $this->line("   \"{$item['titulo']}\": tipo_legislacao_id já é NULL — OK.");
                continue;
            }

            // Busca nome do tipo atual para informar
            $tipoAtual = DB::table('tipos_legislacao')->where('id', $leg->tipo_legislacao_id)->value('nome');

            $this->line("   \"{$item['titulo']}\":");
            $this->line("     Tipo atual: {$tipoAtual} (id={$leg->tipo_legislacao_id}) — classificação incorreta por fallback");
            $this->line("     Ação: definir tipo_legislacao_id = NULL (sem tipo — aguarda revisão)");

            if (! $this->dryRun) {
                DB::table('legislacao')
                    ->where('id', $leg->id)
                    ->update(['tipo_legislacao_id' => null, 'updated_at' => now()]);
                $this->line("     → Gravado.");
            }
        }
    }
}
