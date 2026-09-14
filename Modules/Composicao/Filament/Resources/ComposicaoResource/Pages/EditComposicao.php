<?php

namespace Modules\Composicao\Filament\Resources\ComposicaoResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Composicao\Filament\Resources\ComposicaoResource;

class EditComposicao extends EditRecord
{
    protected static string $resource = ComposicaoResource::class;

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

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Membro atualizado';
    }
}
