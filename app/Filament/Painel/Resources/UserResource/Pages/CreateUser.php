<?php

namespace App\Filament\Painel\Resources\UserResource\Pages;

use App\Filament\Painel\Resources\UserResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['municipio_id'] = Filament::getTenant()->id;
        return $data;
    }
}
