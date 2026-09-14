<?php

namespace Modules\LGPD\Filament\Resources\SolicitacaoLgpdResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\LGPD\Filament\Resources\SolicitacaoLgpdResource;

class ListSolicitacoes extends ListRecords
{
    protected static string $resource = SolicitacaoLgpdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Nova solicitação'),
        ];
    }
}
