<?php

namespace Modules\LGPD\Filament\Resources\RegistroTratamentoResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\LGPD\Filament\Resources\RegistroTratamentoResource;

class EditRegistroTratamento extends EditRecord
{
    protected static string $resource = RegistroTratamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
