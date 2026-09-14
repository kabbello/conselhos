<?php

namespace Modules\Resolucoes\Filament\Resources\ProcessoResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Resolucoes\Filament\Resources\ProcessoResource;

class ListProcessos extends ListRecords
{
    protected static string $resource = ProcessoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
