<?php

namespace Modules\Composicao\Filament\Resources\ComposicaoResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Composicao\Filament\Resources\ComposicaoResource;
use Modules\Composicao\Services\ComposicaoService;
use Modules\Conselhos\Models\Conselho;

class CreateComposicao extends CreateRecord
{
    protected static string $resource = ComposicaoResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $conselho = Conselho::findOrFail($data['conselho_id']);

        return app(ComposicaoService::class)->adicionar($conselho, $data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Membro adicionado à composição';
    }
}
