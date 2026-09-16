<?php

namespace Modules\Reunioes\Observers;

use App\Jobs\EnviarConvocacaoJob;
use Modules\Composicao\Models\Composicao;
use Modules\Reunioes\Models\NotificacaoEnviada;
use Modules\Reunioes\Models\Reuniao;
use Modules\Reunioes\Notifications\ConselheiroNotification;

class ReuniaoObserver
{
    /**
     * Campos relevantes que, quando alterados, disparam notificação de atualização.
     * Mudanças de status (realizar/cancelar) são ações manuais e não geram aviso automático.
     */
    private const CAMPOS_OBSERVADOS = ['data_hora', 'local', 'pauta', 'tipo_id', 'observacoes'];

    // ── Criação ──────────────────────────────────────────────────────────────

    public function created(Reuniao $reuniao): void
    {
        // Pequeno delay para garantir que a transação foi confirmada e as
        // relações (conselho, tipoReuniao) já estão acessíveis no worker.
        EnviarConvocacaoJob::dispatch($reuniao->id, 'convocacao_criada')
            ->delay(now()->addSeconds(15));
    }

    // ── Atualização ──────────────────────────────────────────────────────────

    public function updated(Reuniao $reuniao): void
    {
        $changes = [];

        foreach (self::CAMPOS_OBSERVADOS as $campo) {
            if (! $reuniao->wasChanged($campo)) {
                continue;
            }

            $changes[$campo] = [
                'old' => $this->formatarValor($campo, $reuniao->getOriginal($campo)),
                'new' => $this->formatarValor($campo, $reuniao->getAttribute($campo)),
            ];
        }

        if (empty($changes)) {
            return; // só campos irrelevantes mudaram
        }

        // Chave de idempotência única por conjunto de mudanças
        $hash      = substr(md5(json_encode($changes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)), 0, 10);
        $tipoNotif = 'upd_' . $hash;

        EnviarConvocacaoJob::dispatch($reuniao->id, $tipoNotif, true, $changes)
            ->delay(now()->addSeconds(10));
    }

    // ── Envio manual (action "Notificar") ────────────────────────────────────

    /**
     * Método estático mantido para a action manual "Notificar" no painel.
     * Envia e-mail simples (sem o template de convocação) para membros com e-mail.
     */
    public static function enviarParaComposicao(Reuniao $reuniao, string $assunto, string $corpo): array
    {
        $membros = Composicao::where('conselho_id', $reuniao->conselho_id)
            ->where('ativo', true)
            ->with('conselheiro')
            ->get();

        $resultados = ['enviados' => 0, 'sem_email' => 0, 'erros' => 0];

        foreach ($membros as $membro) {
            $conselheiro = $membro->conselheiro;

            if (! $conselheiro || ! $conselheiro->email) {
                $resultados['sem_email']++;
                continue;
            }

            $sucesso  = true;
            $resposta = null;

            try {
                $conselheiro->notify(new ConselheiroNotification($assunto, $corpo, $reuniao));
                $resultados['enviados']++;
            } catch (\Throwable $e) {
                $sucesso  = false;
                $resposta = $e->getMessage();
                $resultados['erros']++;
            }

            NotificacaoEnviada::create([
                'reuniao_id' => $reuniao->id,
                'tipo'       => 'email',
                'canal'      => 'email',
                'destino'    => $conselheiro->email,
                'sucesso'    => $sucesso,
                'resposta'   => $resposta,
                'enviado_em' => now(),
            ]);
        }

        return $resultados;
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function formatarValor(string $campo, mixed $valor): string
    {
        if ($valor === null) {
            return '';
        }

        if ($campo === 'data_hora') {
            try {
                return \Carbon\Carbon::parse($valor)->format('d/m/Y H:i');
            } catch (\Throwable) {
                // fall through
            }
        }

        return (string) $valor;
    }
}
