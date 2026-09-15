<?php

namespace App\Filament\Admin\Resources\MunicipioResource\Pages;

use App\Filament\Admin\Resources\MunicipioResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMunicipios extends ListRecords
{
    protected static string $resource = MunicipioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
