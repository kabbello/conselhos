<?php

namespace Modules\Conselhos\Filament\Resources\ConselhoResource\Pages;

use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Modules\Conselhos\Filament\Resources\ConselhoResource;

class CreateConselho extends CreateRecord
{
    protected static string $resource = ConselhoResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['municipio_id'] = Filament::getTenant()->id;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
