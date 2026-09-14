<?php

namespace Modules\LGPD\Filament\Resources\RegistroTratamentoResource\Pages;

use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Modules\LGPD\Filament\Resources\RegistroTratamentoResource;

class CreateRegistroTratamento extends CreateRecord
{
    protected static string $resource = RegistroTratamentoResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['municipio_id'] = Filament::getTenant()->id;
        return $data;
    }
}
