<?php

namespace App\Filament\Admin\Resources\MunicipioResource\Pages;

use App\Filament\Admin\Resources\MunicipioResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMunicipio extends CreateRecord
{
    protected static string $resource = MunicipioResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
