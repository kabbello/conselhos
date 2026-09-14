<?php

namespace Modules\Resolucoes\Filament\Resources;

use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Resolucoes\Filament\Resources\ProcessoResource\Pages;
use Modules\Resolucoes\Models\Processo;

class ProcessoResource extends Resource
{
    protected static ?string $model = Processo::class;
    protected static bool $isScopedToTenant = false;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Resoluções';

    protected static ?string $navigationLabel = 'Processos';

    protected static ?string $modelLabel = 'Processo';

    protected static ?string $pluralModelLabel = 'Processos';

    protected static ?string $slug = 'processos';

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

            Select::make('comissao_id')
                ->label('Comissão Responsável')
                ->options(fn (Get $get) => \Modules\Comissoes\Models\Comissao::when(
                    $get('conselho_id'),
                    fn ($q, $id) => $q->where('conselho_id', $id),
                    fn ($q) => $q->whereHas('conselho', fn ($q2) => $q2->where('municipio_id', Filament::getTenant()->id))
                )->orderBy('nome')->pluck('nome', 'id')->all())
                ->searchable()
                ->nullable()
                ->helperText('Apenas comissões do conselho selecionado são listadas.'),

            TextInput::make('numero')
                ->numeric()
                ->label('Número'),

            TextInput::make('ano')
                ->numeric()
                ->required()
                ->default(now()->year)
                ->label('Ano'),

            TextInput::make('titulo')
                ->required()
                ->maxLength(255)
                ->columnSpanFull()
                ->label('Título'),

            Select::make('tipo')
                ->options([
                    'NORMATIVO'   => 'Normativo',
                    'DELIBERATIVO' => 'Deliberativo',
                    'CONSULTIVO'  => 'Consultivo',
                    'FISCALIZACAO' => 'Fiscalização',
                    'OUTROS'      => 'Outros',
                ])
                ->required()
                ->default('DELIBERATIVO')
                ->label('Tipo'),

            Select::make('status')
                ->options([
                    'ABERTO'                   => 'Aberto',
                    'EM_ANALISE'               => 'Em Análise',
                    'AGUARDANDO_COMPLEMENTACAO' => 'Aguardando Complementação',
                    'ENCAMINHADO_COMISSAO'      => 'Encaminhado à Comissão',
                    'VOTADO'                   => 'Votado',
                    'APROVADO'                 => 'Aprovado',
                    'REJEITADO'                => 'Rejeitado',
                    'ARQUIVADO'                => 'Arquivado',
                ])
                ->required()
                ->default('ABERTO')
                ->label('Status'),

            Select::make('origem')
                ->options([
                    'GOVERNO'          => 'Governo (órgão gestor)',
                    'SOCIEDADE_CIVIL'  => 'Sociedade Civil',
                    'MEMBRO_CONSELHO'  => 'Membro do Conselho',
                    'EXTERNO'          => 'Externo (MP, TCE, etc.)',
                ])
                ->nullable()
                ->label('Origem'),

            TextInput::make('requerente')
                ->maxLength(255)
                ->label('Requerente'),

            DatePicker::make('data_abertura')
                ->label('Data de Abertura'),

            DatePicker::make('data_encerramento')
                ->label('Data de Encerramento'),

            Textarea::make('descricao')
                ->rows(4)
                ->columnSpanFull()
                ->label('Descrição'),

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

                TextColumn::make('numero_completo')
                    ->label('Nº')
                    ->searchable(query: fn ($query, $search) => $query->where('numero', $search)),

                TextColumn::make('titulo')
                    ->label('Título')
                    ->limit(60)
                    ->searchable(),

                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'ABERTO'                    => 'info',
                        'EM_ANALISE'                => 'warning',
                        'AGUARDANDO_COMPLEMENTACAO' => 'danger',
                        'ENCAMINHADO_COMISSAO'      => 'warning',
                        'VOTADO'                    => 'purple',
                        'APROVADO'                  => 'success',
                        'REJEITADO'                 => 'danger',
                        'ARQUIVADO'                 => 'gray',
                        default                     => 'gray',
                    }),

                TextColumn::make('comissao.nome')
                    ->label('Comissão')
                    ->limit(30)
                    ->toggleable(),

                TextColumn::make('data_abertura')
                    ->label('Abertura')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'ABERTO'                    => 'Aberto',
                        'EM_ANALISE'                => 'Em Análise',
                        'AGUARDANDO_COMPLEMENTACAO' => 'Aguardando Complementação',
                        'ENCAMINHADO_COMISSAO'      => 'Encaminhado à Comissão',
                        'VOTADO'                    => 'Votado',
                        'APROVADO'                  => 'Aprovado',
                        'REJEITADO'                 => 'Rejeitado',
                        'ARQUIVADO'                 => 'Arquivado',
                    ])
                    ->label('Status'),

                SelectFilter::make('tipo')
                    ->options([
                        'NORMATIVO'    => 'Normativo',
                        'DELIBERATIVO' => 'Deliberativo',
                        'CONSULTIVO'   => 'Consultivo',
                        'FISCALIZACAO' => 'Fiscalização',
                        'OUTROS'       => 'Outros',
                    ])
                    ->label('Tipo'),
            ])
            ->defaultSort('data_abertura', 'desc')
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProcessos::route('/'),
            'create' => Pages\CreateProcesso::route('/create'),
            'edit'   => Pages\EditProcesso::route('/{record}/edit'),
        ];
    }
}
