<?php

namespace Modules\Comissoes\Filament\Resources\ComissaoResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Comissoes\Filament\Resources\ComissaoResource;

class EditComissao extends EditRecord
{
    protected static string $resource = ComissaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
