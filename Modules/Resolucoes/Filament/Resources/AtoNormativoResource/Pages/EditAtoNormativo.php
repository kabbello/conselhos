<?php

namespace Modules\Resolucoes\Filament\Resources\AtoNormativoResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Resolucoes\Filament\Resources\AtoNormativoResource;

class EditAtoNormativo extends EditRecord
{
    protected static string $resource = AtoNormativoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
