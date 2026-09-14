<?php

namespace Modules\Reunioes\Observers;

use Modules\Reunioes\Models\Reuniao;
use Modules\Reunioes\Models\NotificacaoEnviada;
use Modules\Reunioes\Notifications\ConselheiroNotification;
use Modules\Composicao\Models\Composicao;

class ReuniaoObserver
{
    // P0.4 — Envio automático suspendido: não há fluxo formal de convocação, fila
    // transacional, idempotência nem autorização granular. Use a action "Notificar"
    // no painel para envios manuais e autorizados. Reimplemente via evento
    // ReuniaoConvocada + Job em fila quando o fluxo de convocação estiver completo.
    //
    // public function created(Reuniao $reuniao): void { ... }

    public static function enviarParaComposicao(Reuniao $reuniao, string $assunto, string $corpo): array
    {
        $membros = Composicao::where('conselho_id', $reuniao->conselho_id)
            ->where('ativo', true)
            ->with('conselheiro')
            ->get();

        $resultados = ['enviados' => 0, 'sem_email' => 0, 'erros' => 0];

        foreach ($membros as $membro) {
            $conselheiro = $membro->conselheiro;

            if (!$conselheiro || !$conselheiro->email) {
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
}
