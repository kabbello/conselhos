<?php

namespace Modules\Comissoes\Filament\Resources\ComissaoResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Comissoes\Filament\Resources\ComissaoResource;

class ListComissoes extends ListRecords
{
    protected static string $resource = ComissaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
