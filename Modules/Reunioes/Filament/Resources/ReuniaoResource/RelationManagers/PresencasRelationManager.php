<?php

namespace Modules\Reunioes\Filament\Resources\ReuniaoResource\RelationManagers;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Controle de presença nas reuniões do conselho.
 *
 * O quórum exigido varia por conselho, tipo de matéria e regimento interno.
 * Nenhuma regra de quórum é aplicada aqui — a validação deve ser feita
 * com base no perfil normativo configurado para cada conselho.
 */
class PresencasRelationManager extends RelationManager
{
    protected static string $relationship = 'presencas';

    protected static ?string $title = 'Presença';

    public function form(Form $form): Form
    {
        return $form->schema([
            Select::make('composicao_id')
                ->label('Conselheiro')
                ->options(function (\Livewire\Component $livewire) {
                    $conselhoId = $livewire->getOwnerRecord()->conselho_id;
                    return \Modules\Composicao\Models\Composicao::where('conselho_id', $conselhoId)
                        ->where('ativo', true)
                        ->orderBy('nome_exibicao')
                        ->pluck('nome_exibicao', 'id')
                        ->all();
                })
                ->required()
                ->searchable(),

            Toggle::make('presente')
                ->default(false)
                ->label('Presente'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('composicao.nome_exibicao')
                    ->label('Conselheiro')
                    ->searchable(),

                TextColumn::make('composicao.tipo')
                    ->label('Função')
                    ->badge()
                    ->color('gray'),

                IconColumn::make('presente')
                    ->label('Presente')
                    ->boolean(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
            ]);
    }
}
