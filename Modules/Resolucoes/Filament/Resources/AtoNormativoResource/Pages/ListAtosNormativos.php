<?php

namespace Modules\Resolucoes\Filament\Resources\AtoNormativoResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Resolucoes\Filament\Resources\AtoNormativoResource;

class ListAtosNormativos extends ListRecords
{
    protected static string $resource = AtoNormativoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
