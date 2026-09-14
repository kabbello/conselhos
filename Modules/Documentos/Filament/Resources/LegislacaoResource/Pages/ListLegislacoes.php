<?php

namespace Modules\Documentos\Filament\Resources\LegislacaoResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Documentos\Filament\Resources\LegislacaoResource;

class ListLegislacoes extends ListRecords
{
    protected static string $resource = LegislacaoResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
