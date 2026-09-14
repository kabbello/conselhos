<?php

namespace Modules\Conselhos\Filament\Resources\ConselhoResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Conselhos\Filament\Resources\ConselhoResource;

class ListConselhos extends ListRecords
{
    protected static string $resource = ConselhoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
