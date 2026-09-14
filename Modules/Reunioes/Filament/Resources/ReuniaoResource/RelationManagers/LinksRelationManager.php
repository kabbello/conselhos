<?php

namespace Modules\Reunioes\Filament\Resources\ReuniaoResource\RelationManagers;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Tables\Columns\IconColumn;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LinksRelationManager extends RelationManager
{
    protected static string $relationship = 'links';

    protected static ?string $title = 'Links de Acesso Remoto';

    public function form(Form $form): Form
    {
        return $form->schema([
            Select::make('plataforma')
                ->options([
                    'Zoom'   => 'Zoom',
                    'Meet'   => 'Google Meet',
                    'Jitsi'  => 'Jitsi Meet',
                    'Teams'  => 'Microsoft Teams',
                    'Outro'  => 'Outro',
                ])
                ->nullable()
                ->label('Plataforma'),

            TextInput::make('url')
                ->url()
                ->required()
                ->maxLength(1000)
                ->columnSpanFull()
                ->label('Link'),

            Toggle::make('audiencia_publica')
                ->default(false)
                ->label('Exibir no portal público')
                ->helperText('Marque apenas para sessões abertas ao público em geral.'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('plataforma')
                    ->label('Plataforma')
                    ->badge()
                    ->color('info'),

                TextColumn::make('url')
                    ->label('Link')
                    ->limit(60)
                    ->url(fn ($record) => $record->url),

                IconColumn::make('audiencia_publica')
                    ->label('Portal')
                    ->boolean()
                    ->trueIcon('heroicon-o-globe-alt')
                    ->falseIcon('heroicon-o-lock-closed')
                    ->trueColor('success')
                    ->falseColor('gray'),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
