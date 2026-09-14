<?php

namespace Modules\LGPD\Filament\Resources\RegistroTratamentoResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\LGPD\Filament\Resources\RegistroTratamentoResource;

class ListRegistroTratamentos extends ListRecords
{
    protected static string $resource = RegistroTratamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Novo registro'),
        ];
    }
}
