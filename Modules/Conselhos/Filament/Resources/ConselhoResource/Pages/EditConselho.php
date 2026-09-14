<?php

namespace Modules\Conselhos\Filament\Resources\ConselhoResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Conselhos\Filament\Resources\ConselhoResource;

class EditConselho extends EditRecord
{
    protected static string $resource = ConselhoResource::class;

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
