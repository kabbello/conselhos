<?php

namespace App\Http\Controllers\Reunioes;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Composicao\Models\Composicao;
use Modules\Reunioes\Models\Reuniao;

class ListaPresencaPdfController extends Controller
{
    public function __invoke(Reuniao $reuniao)
    {
        $this->authorize('view', $reuniao);

        // Carrega relações necessárias para o PDF
        $reuniao->load([
            'conselho.municipio',
            'tipoReuniao',
            'presencas.composicao.conselheiro',
        ]);

        // Todos os membros ativos da composição do conselho (independente de já ter presença)
        $composicoes = Composicao::where('conselho_id', $reuniao->conselho_id)
            ->where('ativo', true)
            ->with('conselheiro')
            ->orderByRaw("FIELD(tipo, 'PRESIDENTE', 'VICE_PRESIDENTE', 'SECRETARIO', 'MEMBRO', 'SUPLENTE')")
            ->orderBy('nome_exibicao')
            ->get();

        $pdf = Pdf::loadView('reunioes.lista-presenca-pdf', compact('reuniao', 'composicoes'))
            ->setPaper('a4', 'portrait');

        $nomeArquivo = sprintf(
            'lista-presenca-%s-%s.pdf',
            $reuniao->conselho?->sigla ?? 'conselho',
            $reuniao->data_hora?->format('Y-m-d') ?? now()->format('Y-m-d')
        );

        return $pdf->stream($nomeArquivo);
    }
}
