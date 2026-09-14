<?php

namespace Modules\Resolucoes\Filament\Resources;

use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Resolucoes\Filament\Resources\AtoNormativoResource\Pages;
use Modules\Resolucoes\Models\AtoNormativo;
use Modules\Resolucoes\Services\AtoNormativoService;

class AtoNormativoResource extends Resource
{
    protected static ?string $model = AtoNormativo::class;
    protected static bool $isScopedToTenant = false;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';

    protected static ?string $navigationGroup = 'Resoluções';

    protected static ?string $navigationLabel = 'Atos Normativos';

    protected static ?string $modelLabel = 'Ato Normativo';

    protected static ?string $pluralModelLabel = 'Atos Normativos';

    protected static ?string $slug = 'atos-normativos';

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
                ->required()
                ->searchable()
                ->live(),

            Select::make('tipo')
                ->options([
                    'RESOLUCAO'    => 'Resolução',
                    'DELIBERACAO'  => 'Deliberação',
                    'RECOMENDACAO' => 'Recomendação',
                    'PARECER'      => 'Parecer',
                    'MOCAO'        => 'Moção',
                    'PORTARIA'     => 'Portaria',
                ])
                ->required()
                ->live()
                ->label('Tipo'),

            TextInput::make('numero')
                ->numeric()
                ->required()
                ->label('Número'),

            TextInput::make('ano')
                ->numeric()
                ->required()
                ->default(now()->year)
                ->label('Ano'),

            Select::make('processo_id')
                ->label('Processo de Origem')
                ->options(fn (Get $get) => \Modules\Resolucoes\Models\Processo::when(
                    $get('conselho_id'),
                    fn ($q, $id) => $q->where('conselho_id', $id),
                    fn ($q) => $q->whereHas('conselho', fn ($q2) => $q2->where('municipio_id', Filament::getTenant()->id))
                )->orderBy('titulo')->pluck('titulo', 'id')->all())
                ->searchable()
                ->nullable()
                ->helperText('Apenas processos do conselho selecionado são listados.'),

            Select::make('comissao_id')
                ->label('Comissão')
                ->options(fn (Get $get) => \Modules\Comissoes\Models\Comissao::when(
                    $get('conselho_id'),
                    fn ($q, $id) => $q->where('conselho_id', $id),
                    fn ($q) => $q->whereHas('conselho', fn ($q2) => $q2->where('municipio_id', Filament::getTenant()->id))
                )->orderBy('nome')->pluck('nome', 'id')->all())
                ->searchable()
                ->nullable()
                ->helperText('Apenas comissões do conselho selecionado são listadas.'),

            TextInput::make('titulo')
                ->required()
                ->maxLength(255)
                ->columnSpanFull()
                ->label('Título'),

            Textarea::make('ementa')
                ->required()
                ->rows(3)
                ->columnSpanFull()
                ->label('Ementa'),

            Select::make('status')
                ->options([
                    'RASCUNHO' => 'Rascunho',
                    'APROVADO' => 'Aprovado',
                    'VIGENTE'  => 'Vigente',
                    'REVOGADO' => 'Revogado',
                    'SUSPENSO' => 'Suspenso',
                ])
                ->required()
                ->default('RASCUNHO')
                ->label('Status'),

            DatePicker::make('data_aprovacao')
                ->label('Data de Aprovação'),

            DatePicker::make('data_publicacao')
                ->label('Data de Publicação'),

            Toggle::make('publicado')
                ->default(false)
                ->label('Publicado'),

            TextInput::make('diario_oficial_referencia')
                ->maxLength(255)
                ->label('Referência - Diário Oficial'),

            Select::make('revoga_id')
                ->relationship('revoga', 'numero_completo',
                    fn (Builder $q) => $q->whereHas('conselho', fn (Builder $q2) =>
                        $q2->where('municipio_id', Filament::getTenant()->id)
                    )->where('status', 'VIGENTE')
                )
                ->searchable()
                ->nullable()
                ->label('Revoga Ato'),

            RichEditor::make('texto_completo')
                ->columnSpanFull()
                ->label('Texto Completo'),

            Textarea::make('observacoes')
                ->rows(3)
                ->columnSpanFull()
                ->label('Observações'),
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

                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'RESOLUCAO'    => 'success',
                        'DELIBERACAO'  => 'info',
                        'RECOMENDACAO' => 'warning',
                        'PARECER'      => 'purple',
                        'MOCAO'        => 'gray',
                        'PORTARIA'     => 'danger',
                        default        => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'RESOLUCAO'    => 'Resolução',
                        'DELIBERACAO'  => 'Deliberação',
                        'RECOMENDACAO' => 'Recomendação',
                        'PARECER'      => 'Parecer',
                        'MOCAO'        => 'Moção',
                        'PORTARIA'     => 'Portaria',
                        default        => $state,
                    }),

                TextColumn::make('numero_completo')
                    ->label('Número')
                    ->searchable(['numero', 'ano'])
                    ->sortable(['numero', 'ano']),

                TextColumn::make('titulo')
                    ->label('Título')
                    ->limit(50)
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'RASCUNHO' => 'gray',
                        'APROVADO' => 'warning',
                        'VIGENTE'  => 'success',
                        'REVOGADO' => 'danger',
                        'SUSPENSO' => 'warning',
                        default    => 'gray',
                    }),

                IconColumn::make('publicado')
                    ->label('Pub.')
                    ->boolean(),

                TextColumn::make('data_aprovacao')
                    ->label('Aprovação')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('data_publicacao')
                    ->label('Publicação')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('tipo')
                    ->options([
                        'RESOLUCAO'    => 'Resolução',
                        'DELIBERACAO'  => 'Deliberação',
                        'RECOMENDACAO' => 'Recomendação',
                        'PARECER'      => 'Parecer',
                        'MOCAO'        => 'Moção',
                        'PORTARIA'     => 'Portaria',
                    ])
                    ->label('Tipo'),

                SelectFilter::make('status')
                    ->options([
                        'RASCUNHO' => 'Rascunho',
                        'APROVADO' => 'Aprovado',
                        'VIGENTE'  => 'Vigente',
                        'REVOGADO' => 'Revogado',
                        'SUSPENSO' => 'Suspenso',
                    ])
                    ->label('Status'),

                TernaryFilter::make('publicado')
                    ->label('Publicado'),
            ])
            ->defaultSort('ano', 'desc')
            ->actions([
                EditAction::make(),
                Action::make('publicar')
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    ->label('Publicar')
                    ->requiresConfirmation()
                    ->modalDescription('O ato será marcado como publicado e seu status mudará para VIGENTE. Esta ação não pode ser desfeita.')
                    ->form([
                        TextInput::make('diario_oficial_referencia')
                            ->label('Referência no Diário Oficial')
                            ->maxLength(255)
                            ->helperText('Opcional. Preencha se houver publicação em Diário Oficial.')
                            ->placeholder('Ex.: D.O.M. de 01/01/2025, p. 5'),
                    ])
                    ->authorize(fn (AtoNormativo $record) => auth()->user()->can('publicar', $record))
                    ->visible(fn (AtoNormativo $record) => ! $record->publicado && in_array($record->status, ['APROVADO', 'VIGENTE']))
                    ->action(function (AtoNormativo $record, array $data) {
                        try {
                            app(AtoNormativoService::class)->publicar(
                                $record,
                                $data['diario_oficial_referencia'] ?? null,
                            );

                            Notification::make()
                                ->title('Ato publicado com sucesso.')
                                ->success()
                                ->send();
                        } catch (\RuntimeException $e) {
                            Notification::make()
                                ->title('Não foi possível publicar')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAtosNormativos::route('/'),
            'create' => Pages\CreateAtoNormativo::route('/create'),
            'edit'   => Pages\EditAtoNormativo::route('/{record}/edit'),
        ];
    }
}
