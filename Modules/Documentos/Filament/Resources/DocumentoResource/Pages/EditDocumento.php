<?php

namespace Modules\Documentos\Filament\Resources\DocumentoResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Documentos\Filament\Resources\DocumentoResource;

class EditDocumento extends EditRecord
{
    protected static string $resource = DocumentoResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
