<?php

namespace Modules\Reunioes\Filament\Resources\ReuniaoResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Reunioes\Filament\Resources\ReuniaoResource;

class EditReuniao extends EditRecord
{
    protected static string $resource = ReuniaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
