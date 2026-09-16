<?php

namespace App\Jobs;

use App\Mail\ConvocacaoMail;
use App\Services\EvolutionApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\Composicao\Models\Composicao;
use Modules\Reunioes\Models\Reuniao;

/**
 * Envia notificações (e-mail e/ou WhatsApp) para os membros ativos da
 * composição quando uma reunião é criada ou alterada.
 *
 * Idempotência: antes de enviar cada mensagem, tenta inserir um registro
 * em notificacoes_enviadas. Se a inserção falhar (chave única duplicada),
 * o envio é pulado — garantindo que o mesmo evento não gere duas notificações.
 *
 * @property  int     $reuniaoId
 * @property  string  $tipoNotif   'convocacao_criada' | 'upd_{hash}'
 * @property  bool    $isUpdate
 * @property  array   $changes     ['campo' => ['old' => ..., 'new' => ...]]
 */
class EnviarConvocacaoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        public readonly int $reuniaoId,
        public readonly string $tipoNotif,
        public readonly bool $isUpdate = false,
        public readonly array $changes = [],
    ) {}

    public function handle(EvolutionApiService $evolution): void
    {
        $reuniao = Reuniao::with([
            'conselho.municipio',
            'tipoReuniao',
            'anexos' => fn ($q) => $q->where('publicado', true),
        ])->find($this->reuniaoId);

        if (! $reuniao) {
            return;
        }

        $conselho    = $reuniao->conselho;
        $emailAtivo  = (bool) $conselho->notif_email_ativo;
        $wappAtivo   = (bool) $conselho->notif_whatsapp_ativo;

        if (! $emailAtivo && ! $wappAtivo) {
            return;
        }

        $membros = Composicao::where('conselho_id', $reuniao->conselho_id)
            ->where('ativo', true)
            ->whereNull('deleted_at')
            ->with('conselheiro')
            ->get();

        // ── E-mail ───────────────────────────────────────────────────────────
        if ($emailAtivo) {
            foreach ($membros as $membro) {
                $email = $membro->conselheiro?->email ?: ($membro->email_exibicao ?? null);
                if (! $email) {
                    continue;
                }

                if (! $this->registrar($reuniao->id, 'email', $email)) {
                    continue; // já enviado
                }

                $sucesso  = true;
                $resposta = null;

                try {
                    Mail::to($email, $membro->nome_exibicao)
                        ->send(new ConvocacaoMail(
                            reuniao: $reuniao,
                            nomeDestinatario: $membro->nome_exibicao,
                            isUpdate: $this->isUpdate,
                            changes: $this->changes,
                        ));
                } catch (\Throwable $e) {
                    $sucesso  = false;
                    $resposta = mb_substr($e->getMessage(), 0, 500);
                    Log::warning('EnviarConvocacaoJob: falha no e-mail', [
                        'reuniao_id' => $this->reuniaoId,
                        'email'      => $email,
                        'error'      => $e->getMessage(),
                    ]);
                }

                $this->atualizar($reuniao->id, 'email', $email, $sucesso, $resposta);
            }
        }

        // ── WhatsApp ─────────────────────────────────────────────────────────
        if ($wappAtivo && $evolution->enabled()) {
            $sigla  = $conselho->sigla ?? '';
            $dataPt = $reuniao->data_hora?->format('d/m/Y') ?? '';
            $horaPt = $reuniao->data_hora?->format('H:i') ?? '';
            $tipo   = $reuniao->tipoReuniao->nome ?? 'Reunião';
            $local  = trim(preg_replace('/\s+/', ' ', str_replace(["\r", "\n"], ' ', $reuniao->local ?? '')));

            foreach ($membros as $membro) {
                $fone = $membro->conselheiro?->telefone ?: ($membro->telefone_contato ?? null);
                if (! $fone) {
                    continue;
                }

                if (! $this->registrar($reuniao->id, 'whatsapp', $fone)) {
                    continue; // já enviado
                }

                $primeiro = $this->primeiroNome($membro->nome_exibicao);
                $msg      = $this->montarMensagemWpp($sigla, $tipo, $dataPt, $horaPt, $local, $primeiro);

                $sucesso  = $evolution->sendText($fone, $msg);
                $resposta = $sucesso ? null : 'Falha na API';

                $this->atualizar($reuniao->id, 'whatsapp', $fone, $sucesso, $resposta);

                // Pequena pausa para não sobrecarregar a API
                usleep(300_000); // 300 ms
            }
        }
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Tenta inserir o registro de idempotência.
     * Retorna true se foi inserido (deve enviar), false se já existia.
     */
    private function registrar(int $reuniaoId, string $canal, string $destino): bool
    {
        $inseridos = DB::table('notificacoes_enviadas')->insertOrIgnore([
            'reuniao_id' => $reuniaoId,
            'tipo'       => $this->tipoNotif,
            'canal'      => $canal,
            'destino'    => $destino,
            'sucesso'    => false,
            'resposta'   => null,
            'enviado_em' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $inseridos > 0;
    }

    /** Atualiza o registro com o resultado do envio. */
    private function atualizar(int $reuniaoId, string $canal, string $destino, bool $sucesso, ?string $resposta): void
    {
        DB::table('notificacoes_enviadas')
            ->where('reuniao_id', $reuniaoId)
            ->where('tipo', $this->tipoNotif)
            ->where('canal', $canal)
            ->where('destino', $destino)
            ->update([
                'sucesso'    => $sucesso,
                'resposta'   => $resposta,
                'enviado_em' => now(),
                'updated_at' => now(),
            ]);
    }

    private function montarMensagemWpp(
        string $sigla,
        string $tipo,
        string $dataPt,
        string $horaPt,
        string $local,
        string $primeiro,
    ): string {
        if ($this->isUpdate) {
            $linhas = [];
            $labelCampo = [
                'data_hora'   => 'Data/Hora',
                'local'       => 'Local',
                'pauta'       => 'Pauta',
                'tipo_id'     => 'Tipo',
                'observacoes' => 'Observações',
            ];
            foreach ($this->changes as $campo => $vals) {
                $label = $labelCampo[$campo] ?? $campo;
                $old   = $campo === 'pauta'
                    ? mb_strimwidth(strip_tags($vals['old'] ?? ''), 0, 60, '…')
                    : ($vals['old'] ?? '—');
                $new   = $campo === 'pauta'
                    ? mb_strimwidth(strip_tags($vals['new'] ?? ''), 0, 60, '…')
                    : ($vals['new'] ?? '—');
                $linhas[] = "- {$label}: {$old} → {$new}";
            }

            return "⚠️ Atualização de convocação ({$sigla})\n"
                . "Olá {$primeiro}!\n"
                . "🗓 {$dataPt}" . ($horaPt ? " às {$horaPt}" : '') . "\n"
                . ($local ? "📍 {$local}\n" : '')
                . "\nAlterações:\n" . implode("\n", $linhas) . "\n\n"
                . 'Detalhes no sistema de conselhos.';
        }

        return "📣 Convocação de reunião {$tipo} ({$sigla})\n"
            . "Olá {$primeiro}!\n"
            . "🗓 {$dataPt}" . ($horaPt ? " às {$horaPt}" : '') . "\n"
            . ($local ? "📍 {$local}\n" : '')
            . "\nA pauta e demais informações estão disponíveis no sistema de conselhos.";
    }

    private function primeiroNome(string $nome): string
    {
        return explode(' ', trim($nome))[0] ?? $nome;
    }
}
