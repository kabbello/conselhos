<?php

namespace Modules\Composicao\Filament\Resources\ConselheiroResource\Pages;

use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Modules\Composicao\Filament\Resources\ConselheiroResource;

class CreateConselheiro extends CreateRecord
{
    protected static string $resource = ConselheiroResource::class;

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
