<?php

namespace Modules\Reunioes\Filament\Resources\ReuniaoResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NotificacoesRelationManager extends RelationManager
{
    protected static string $relationship = 'notificacoes';

    protected static ?string $title = 'Notificações Enviadas';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('enviado_em')
                    ->label('Enviado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('destino')
                    ->label('Destinatário')
                    ->searchable(),

                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->color('info'),

                IconColumn::make('sucesso')
                    ->label('Sucesso')
                    ->boolean(),
            ])
            ->headerActions([])
            ->actions([])
            ->paginated([10, 25]);
    }
}
