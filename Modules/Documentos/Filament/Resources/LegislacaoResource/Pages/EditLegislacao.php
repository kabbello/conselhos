<?php

namespace Modules\Documentos\Filament\Resources\LegislacaoResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Documentos\Filament\Resources\LegislacaoResource;

class EditLegislacao extends EditRecord
{
    protected static string $resource = LegislacaoResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
