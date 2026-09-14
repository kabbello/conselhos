<?php

namespace Modules\Resolucoes\Filament\Resources\ProcessoResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Resolucoes\Filament\Resources\ProcessoResource;

class EditProcesso extends EditRecord
{
    protected static string $resource = ProcessoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
