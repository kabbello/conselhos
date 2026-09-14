<?php

namespace Modules\Auditoria\Filament\Resources\AtividadeResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Auditoria\Filament\Resources\AtividadeResource;

class ListAtividades extends ListRecords
{
    protected static string $resource = AtividadeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('exportar')
                ->label('Exportar CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                // A08: somente usuários com permissão explícita podem exportar o log
                ->authorize(fn () => auth()->user()->hasPermissionTo('export.auditoria'))
                ->action(function () {
                    $records = AtividadeResource::getEloquentQuery()
                        ->with('causer')
                        ->get();

                    $linhas = ["Data/Hora,Usuário,Evento,Entidade,ID,Descrição\n"];

                    foreach ($records as $r) {
                        $linhas[] = implode(',', [
                            '"' . $r->created_at->format('d/m/Y H:i:s') . '"',
                            '"' . self::sanitizarCelula($r->causer?->name ?? '—') . '"',
                            '"' . self::sanitizarCelula($r->event ?? '—') . '"',
                            '"' . self::sanitizarCelula(class_basename($r->subject_type ?? '')) . '"',
                            (int) ($r->subject_id ?? 0),
                            '"' . self::sanitizarCelula($r->description ?? '') . '"',
                        ]) . "\n";
                    }

                    $csv = implode('', $linhas);

                    return response()->streamDownload(
                        fn () => print($csv),
                        'auditoria-' . now()->format('Y-m-d') . '.csv',
                        ['Content-Type' => 'text/csv; charset=UTF-8']
                    );
                }),
        ];
    }

    /**
     * A08: previne CSV formula injection (CWE-1236).
     *
     * Planilhas como Excel e LibreOffice interpretam células que começam com
     * '=', '+', '-', '@', '\t' ou '\r' como fórmulas, permitindo execução
     * de macros ou exfiltração de dados quando o arquivo é aberto.
     *
     * Solução: prefixar o valor com uma aspa simples quando ele inicia com
     * qualquer um desses caracteres. A aspa simples é o marcador padrão de
     * "texto literal" em planilhas e não aparece na célula após a importação.
     * Aspas duplas existentes são dobradas para escapar o delimitador CSV.
     */
    private static function sanitizarCelula(mixed $value): string
    {
        $str = str_replace('"', '""', (string) $value);

        if ($str !== '' && in_array($str[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            $str = "'" . $str;
        }

        return $str;
    }
}
