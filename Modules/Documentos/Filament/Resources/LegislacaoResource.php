<?php

namespace Modules\Documentos\Filament\Resources;

use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Documentos\Filament\Resources\LegislacaoResource\Pages;
use Modules\Documentos\Models\Legislacao;

/**
 * Base legal dos conselhos.
 *
 * Registra o arcabouço legal que regula cada conselho:
 * leis federais, estaduais, municipais, decretos de criação, regimentos internos.
 */
class LegislacaoResource extends Resource
{
    protected static ?string $model = Legislacao::class;
    protected static bool $isScopedToTenant = false;

    protected static ?string $navigationIcon = 'heroicon-o-scale';

    protected static ?string $navigationGroup = 'Documentos';

    protected static ?string $navigationLabel = 'Base Legal';

    protected static ?string $modelLabel = 'Legislação';

    protected static ?string $pluralModelLabel = 'Legislações';

    protected static ?string $slug = 'legislacao';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('conselho', fn (Builder $q) => $q->where('municipio_id', Filament::getTenant()->id));
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('conselho_id')
                ->label('Conselho')
                ->options(fn () => \Modules\Conselhos\Models\Conselho::where('municipio_id', Filament::getTenant()->id)
                    ->where('ativo', true)->orderBy('nome')->pluck('nome', 'id')->all())
                ->required()->searchable(),

            Select::make('tipo_legislacao_id')
                ->label('Tipo')
                ->options(fn () => \Modules\Documentos\Models\TipoLegislacao::orderBy('nome')->pluck('nome', 'id')->all())
                ->searchable()
                ->nullable(),

            TextInput::make('titulo')
                ->required()
                ->maxLength(255)
                ->columnSpanFull()
                ->label('Título'),

            TextInput::make('numero')
                ->maxLength(100)
                ->label('Número'),

            DatePicker::make('data')
                ->label('Data'),

            Textarea::make('descricao')
                ->rows(4)
                ->columnSpanFull()
                ->label('Descrição / Ementa'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('conselho.sigla')
                    ->label('Conselho')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('tipoLegislacao.nome')
                    ->label('Tipo')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('titulo')
                    ->label('Título')
                    ->searchable()
                    ->limit(70),

                TextColumn::make('numero')
                    ->label('Número')
                    ->searchable(),

                TextColumn::make('data')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('conselho_id')
                    ->label('Conselho')
                    ->options(fn () => \Modules\Conselhos\Models\Conselho::where('municipio_id', Filament::getTenant()->id)
                        ->orderBy('nome')
                        ->pluck('nome', 'id')
                        ->all()
                    )
                    ->searchable(),

                SelectFilter::make('tipo_legislacao_id')
                    ->relationship('tipoLegislacao', 'nome')
                    ->label('Tipo'),
            ])
            ->defaultSort('data', 'desc')
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListLegislacoes::route('/'),
            'create' => Pages\CreateLegislacao::route('/create'),
            'edit'   => Pages\EditLegislacao::route('/{record}/edit'),
        ];
    }
}
