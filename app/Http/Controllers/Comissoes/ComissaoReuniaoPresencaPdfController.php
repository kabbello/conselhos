<?php

namespace App\Http\Controllers\Comissoes;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Comissoes\Models\ComissaoReuniao;

class ComissaoReuniaoPresencaPdfController extends Controller
{
    public function __invoke(ComissaoReuniao $reuniao)
    {
        // Carrega relações necessárias para o PDF
        $reuniao->load([
            'comissao.conselho.municipio',
            'presencas.membro',
        ]);

        // Todos os membros ativos da comissão
        $membros = $reuniao->comissao
            ->membrosAtivos()
            ->with('composicao.conselheiro')
            ->orderBy('papel')
            ->orderBy('nome_exibicao')
            ->get();

        $pdf = Pdf::loadView('comissoes.lista-presenca-pdf', compact('reuniao', 'membros'))
            ->setPaper('a4', 'portrait');

        $nomeArquivo = sprintf(
            'lista-presenca-comissao-%s-%s.pdf',
            str($reuniao->comissao?->nome ?? 'comissao')->slug()->toString(),
            $reuniao->data_hora?->format('Y-m-d') ?? now()->format('Y-m-d')
        );

        return $pdf->stream($nomeArquivo);
    }
}
