<?php

namespace Modules\Comissoes\Filament\Resources;

use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Comissoes\Filament\Resources\ComissaoResource\Pages;
use Modules\Comissoes\Models\Comissao;

class ComissaoResource extends Resource
{
    protected static ?string $model = Comissao::class;
    protected static bool $isScopedToTenant = false;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Comissões';

    protected static ?string $navigationLabel = 'Comissões';

    protected static ?string $modelLabel = 'Comissão';

    protected static ?string $pluralModelLabel = 'Comissões';

    protected static ?string $slug = 'comissoes';

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

            TextInput::make('nome')
                ->required()
                ->maxLength(255)
                ->label('Nome da Comissão'),

            Select::make('tipo')
                ->options([
                    'PERMANENTE'      => 'Permanente',
                    'TEMPORARIA'      => 'Temporária',
                    'ESPECIAL'        => 'Especial',
                    'GRUPO_TRABALHO'  => 'Grupo de Trabalho',
                    'CAMARA_TECNICA'  => 'Câmara Técnica',
                ])
                ->required()
                ->label('Tipo'),

            Select::make('status')
                ->options([
                    'ATIVA'     => 'Ativa',
                    'SUSPENSA'  => 'Suspensa',
                    'ENCERRADA' => 'Encerrada',
                ])
                ->required()
                ->default('ATIVA')
                ->label('Status'),

            TextInput::make('ato_criacao')
                ->maxLength(255)
                ->label('Ato de Criação'),

            DatePicker::make('data_criacao')
                ->label('Data de Criação'),

            DatePicker::make('data_encerramento')
                ->label('Data de Encerramento'),

            Textarea::make('finalidade')
                ->rows(3)
                ->columnSpanFull()
                ->label('Finalidade'),

            Textarea::make('observacoes')
                ->rows(3)
                ->columnSpanFull()
                ->label('Observações'),

            Toggle::make('ativo')
                ->default(true)
                ->label('Ativa'),
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

                TextColumn::make('nome')
                    ->label('Nome')
                    ->sortable()
                    ->searchable()
                    ->limit(50),

                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'PERMANENTE'     => 'Permanente',
                        'TEMPORARIA'     => 'Temporária',
                        'ESPECIAL'       => 'Especial',
                        'GRUPO_TRABALHO' => 'Grupo de Trabalho',
                        'CAMARA_TECNICA' => 'Câmara Técnica',
                        default          => $state,
                    })
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'PERMANENTE'     => 'success',
                        'TEMPORARIA'     => 'warning',
                        'ESPECIAL'       => 'info',
                        'GRUPO_TRABALHO' => 'gray',
                        'CAMARA_TECNICA' => 'purple',
                        default          => 'gray',
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'ATIVA'     => 'Ativa',
                        'SUSPENSA'  => 'Suspensa',
                        'ENCERRADA' => 'Encerrada',
                        default     => $state,
                    })
                    ->color(fn (string $state) => match ($state) {
                        'ATIVA'     => 'success',
                        'SUSPENSA'  => 'warning',
                        'ENCERRADA' => 'danger',
                        default     => 'gray',
                    }),

                IconColumn::make('ativo')
                    ->label('Ativa')
                    ->boolean(),

                TextColumn::make('data_criacao')
                    ->label('Criação')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('membrosAtivos_count')
                    ->label('Membros')
                    ->counts('membrosAtivos')
                    ->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('tipo')
                    ->options([
                        'PERMANENTE'     => 'Permanente',
                        'TEMPORARIA'     => 'Temporária',
                        'ESPECIAL'       => 'Especial',
                        'GRUPO_TRABALHO' => 'Grupo de Trabalho',
                        'CAMARA_TECNICA' => 'Câmara Técnica',
                    ])
                    ->label('Tipo'),

                SelectFilter::make('status')
                    ->options([
                        'ATIVA'     => 'Ativa',
                        'SUSPENSA'  => 'Suspensa',
                        'ENCERRADA' => 'Encerrada',
                    ])
                    ->label('Status'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getRelationManagers(): array
    {
        return [
            ComissaoResource\RelationManagers\MembrosRelationManager::class,
            ComissaoResource\RelationManagers\ReunioesRelationManager::class,
            ComissaoResource\RelationManagers\DocumentosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListComissoes::route('/'),
            'create' => Pages\CreateComissao::route('/create'),
            'edit'   => Pages\EditComissao::route('/{record}/edit'),
        ];
    }
}
