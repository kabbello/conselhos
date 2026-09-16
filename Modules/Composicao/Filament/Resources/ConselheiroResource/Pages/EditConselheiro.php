<?php

namespace Modules\Composicao\Filament\Resources\ConselheiroResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Composicao\Filament\Resources\ConselheiroResource;

class EditConselheiro extends EditRecord
{
    protected static string $resource = ConselheiroResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
