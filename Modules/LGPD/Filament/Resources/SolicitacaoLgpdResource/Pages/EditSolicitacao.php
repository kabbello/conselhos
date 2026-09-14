<?php

namespace Modules\LGPD\Filament\Resources\SolicitacaoLgpdResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\LGPD\Filament\Resources\SolicitacaoLgpdResource;

class EditSolicitacao extends EditRecord
{
    protected static string $resource = SolicitacaoLgpdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
