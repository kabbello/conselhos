<?php

namespace Modules\Comissoes\Filament\Resources\ComissaoResource\RelationManagers;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReunioesRelationManager extends RelationManager
{
    protected static string $relationship = 'reunioes';

    protected static ?string $title = 'Reuniões';

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('numero')
                ->numeric()
                ->label('Número da Reunião'),

            Select::make('tipo')
                ->options([
                    'ORDINARIA'     => 'Ordinária',
                    'EXTRAORDINARIA' => 'Extraordinária',
                ])
                ->required()
                ->default('ORDINARIA')
                ->label('Tipo'),

            DateTimePicker::make('data_hora')
                ->required()
                ->label('Data e Hora'),

            TextInput::make('local')
                ->maxLength(255)
                ->label('Local'),

            Select::make('status')
                ->options([
                    'AGENDADA'  => 'Agendada',
                    'REALIZADA' => 'Realizada',
                    'CANCELADA' => 'Cancelada',
                ])
                ->required()
                ->default('AGENDADA')
                ->label('Status'),

            Toggle::make('ata_aprovada')
                ->default(false)
                ->label('Ata Aprovada'),

            DatePicker::make('data_aprovacao_ata')
                ->label('Data de Aprovação da Ata'),

            DatePicker::make('data_publicacao_ata')
                ->label('Data de Publicação da Ata (LAI)'),

            RichEditor::make('pauta')
                ->columnSpanFull()
                ->label('Pauta'),

            RichEditor::make('ata_texto')
                ->columnSpanFull()
                ->label('Texto da Ata'),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero')
                    ->label('Nº')
                    ->sortable(),

                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state) => $state === 'ORDINARIA' ? 'info' : 'warning')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'ORDINARIA'      => 'Ordinária',
                        'EXTRAORDINARIA' => 'Extraordinária',
                        default          => $state,
                    }),

                TextColumn::make('data_hora')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('local')
                    ->label('Local')
                    ->limit(30),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'AGENDADA'  => 'warning',
                        'REALIZADA' => 'success',
                        'CANCELADA' => 'danger',
                        default     => 'gray',
                    }),

                IconColumn::make('ata_aprovada')
                    ->label('Ata')
                    ->boolean(),

                TextColumn::make('data_publicacao_ata')
                    ->label('Pub. Ata')
                    ->date('d/m/Y'),
            ])
            ->defaultSort('data_hora', 'desc')
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
