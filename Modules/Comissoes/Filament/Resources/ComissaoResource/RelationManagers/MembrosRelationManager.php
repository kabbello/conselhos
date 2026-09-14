<?php

namespace Modules\Comissoes\Filament\Resources\ComissaoResource\RelationManagers;

use Filament\Forms\Components\DatePicker;
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

class MembrosRelationManager extends RelationManager
{
    protected static string $relationship = 'membros';

    protected static ?string $title = 'Membros';

    public function form(Form $form): Form
    {
        return $form->schema([
            Select::make('composicao_id')
                ->label('Conselheiro vinculado')
                ->options(function (\Livewire\Component $livewire) {
                    $conselhoId = $livewire->getOwnerRecord()->conselho_id;
                    return \Modules\Composicao\Models\Composicao::where('conselho_id', $conselhoId)
                        ->where('ativo', true)->orderBy('nome_exibicao')->pluck('nome_exibicao', 'id')->all();
                })
                ->searchable()
                ->nullable(),

            TextInput::make('nome_exibicao')
                ->maxLength(255)
                ->label('Nome para exibição')
                ->helperText('Preencher se diferente do conselheiro vinculado'),

            Select::make('papel')
                ->options([
                    'COORDENADOR'       => 'Coordenador',
                    'VICE_COORDENADOR'  => 'Vice-Coordenador',
                    'RELATOR'           => 'Relator',
                    'MEMBRO'            => 'Membro',
                ])
                ->required()
                ->default('MEMBRO')
                ->label('Papel'),

            DatePicker::make('data_entrada')
                ->label('Data de Entrada'),

            DatePicker::make('data_saida')
                ->label('Data de Saída'),

            Toggle::make('ativo')
                ->default(true)
                ->label('Ativo'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable(['nome_exibicao']),

                TextColumn::make('papel')
                    ->label('Papel')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'COORDENADOR'      => 'success',
                        'VICE_COORDENADOR' => 'info',
                        'RELATOR'          => 'warning',
                        default            => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'COORDENADOR'      => 'Coordenador',
                        'VICE_COORDENADOR' => 'Vice-Coordenador',
                        'RELATOR'          => 'Relator',
                        'MEMBRO'           => 'Membro',
                        default            => $state,
                    }),

                TextColumn::make('data_entrada')
                    ->label('Entrada')
                    ->date('d/m/Y'),

                TextColumn::make('data_saida')
                    ->label('Saída')
                    ->date('d/m/Y'),

                IconColumn::make('ativo')
                    ->label('Ativo')
                    ->boolean(),
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
