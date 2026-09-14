<?php

namespace Modules\Documentos\Filament\Resources\DocumentoResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Documentos\Filament\Resources\DocumentoResource;

class ListDocumentos extends ListRecords
{
    protected static string $resource = DocumentoResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
