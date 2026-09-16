<?php

namespace Modules\Composicao\Filament\Resources\ConselheiroResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Composicao\Filament\Resources\ConselheiroResource;

class ListConselheiros extends ListRecords
{
    protected static string $resource = ConselheiroResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
