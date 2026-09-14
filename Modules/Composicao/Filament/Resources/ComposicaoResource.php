<?php

namespace Modules\Composicao\Filament\Resources;

use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Composicao\Filament\Resources\ComposicaoResource\Pages;
use Modules\Composicao\Models\Composicao;
use Modules\Composicao\Models\Conselheiro;
use Modules\Composicao\Services\ComposicaoService;
use Modules\Conselhos\Models\Conselho;

class ComposicaoResource extends Resource
{
    protected static ?string $model = Composicao::class;
    protected static bool $isScopedToTenant = false;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Conselho';
    protected static ?string $navigationLabel = 'Composição';
    protected static ?string $modelLabel = 'Membro';
    protected static ?string $pluralModelLabel = 'Composição';
    protected static ?string $slug = 'composicao';
    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['conselheiro', 'conselho'])
            ->whereHas('conselho', fn (Builder $q) =>
                $q->where('municipio_id', Filament::getTenant()->id)
            );
    }

    public static function form(Form $form): Form
    {
        $municipioId = Filament::getTenant()->id;

        return $form->schema([
            Forms\Components\Section::make('Vínculo')->schema([
                Forms\Components\Select::make('conselho_id')
                    ->label('Conselho')
                    ->options(fn () => \Modules\Conselhos\Models\Conselho::where('municipio_id', Filament::getTenant()->id)
                        ->where('ativo', true)->orderBy('nome')->pluck('nome', 'id')->all())
                    ->required()
                    ->searchable(),

                Forms\Components\Select::make('conselheiro_id')
                    ->label('Conselheiro')
                    ->options(fn () => \Modules\Composicao\Models\Conselheiro::where('municipio_id', Filament::getTenant()->id)
                        ->where('ativo', true)->orderBy('nome')->pluck('nome', 'id')->all())
                    ->searchable()
                    ->nullable()
                    ->helperText('Digite o nome para buscar ou cadastrar um novo conselheiro')
                    ->createOptionForm([
                        Forms\Components\TextInput::make('nome')
                            ->label('Nome completo')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('cpf')
                            ->label('CPF')
                            ->mask('999.999.999-99')
                            ->maxLength(14),
                        Forms\Components\TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('telefone')
                            ->label('Telefone')
                            ->tel()
                            ->maxLength(20),
                    ])
                    ->createOptionUsing(fn (array $data) => Conselheiro::create([
                        ...$data,
                        'municipio_id' => Filament::getTenant()->id,
                        'ativo'        => true,
                    ])->getKey()),

                Forms\Components\Select::make('tipo')
                    ->label('Tipo / Cargo na mesa')
                    ->options([
                        'PRESIDENTE'      => 'Presidente',
                        'VICE_PRESIDENTE' => 'Vice-presidente',
                        'SECRETARIO'      => 'Secretário',
                        'MEMBRO'          => 'Membro',
                        'SUPLENTE'        => 'Suplente',
                    ])
                    ->required()
                    ->default('MEMBRO'),
            ])->columns(3),

            Forms\Components\Section::make('Dados para exibição pública')
                ->description('Preenchidos automaticamente do cadastro do conselheiro.')
                ->schema([
                    Forms\Components\TextInput::make('nome_exibicao')
                        ->label('Nome para exibição')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('email_exibicao')
                        ->label('E-mail para exibição')
                        ->email()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('telefone_contato')
                        ->label('Telefone de contato')
                        ->tel()
                        ->maxLength(20),
                ])->columns(3),

            Forms\Components\Section::make('Mandato')->schema([
                Forms\Components\DatePicker::make('data_nomeacao')
                    ->label('Data da nomeação')
                    ->displayFormat('d/m/Y'),

                Forms\Components\DatePicker::make('data_fim')
                    ->label('Fim do mandato')
                    ->displayFormat('d/m/Y')
                    ->helperText('Deixe em branco para mandato em curso'),

                Forms\Components\TextInput::make('decreto_nomeacao')
                    ->label('Decreto de nomeação')
                    ->placeholder('Ex.: Decreto nº 6.731, de 16/12/2025')
                    ->maxLength(255)
                    ->columnSpan(2),

                Forms\Components\Toggle::make('ativo')
                    ->label('Mandato ativo')
                    ->default(true)
                    ->inline(false),
            ])->columns(3),

            Forms\Components\Section::make('Observações')->schema([
                Forms\Components\Textarea::make('observacoes')
                    ->label('Observações internas')
                    ->rows(3)
                    ->maxLength(2000)
                    ->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('conselho.nome')
                    ->label('Conselho')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('nome_exibicao')
                    ->label('Membro')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->description(fn (Composicao $r) => $r->email_exibicao),

                Tables\Columns\TextColumn::make('tipo')
                    ->label('Cargo')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'PRESIDENTE'      => 'danger',
                        'VICE_PRESIDENTE' => 'warning',
                        'SECRETARIO'      => 'info',
                        'MEMBRO'          => 'success',
                        'SUPLENTE'        => 'gray',
                        default           => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'PRESIDENTE'      => 'Presidente',
                        'VICE_PRESIDENTE' => 'Vice-presidente',
                        'SECRETARIO'      => 'Secretário',
                        'MEMBRO'          => 'Membro',
                        'SUPLENTE'        => 'Suplente',
                        default           => $state,
                    }),

                Tables\Columns\TextColumn::make('data_nomeacao')
                    ->label('Nomeação')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('data_fim')
                    ->label('Fim mandato')
                    ->date('d/m/Y')
                    ->placeholder('Em curso')
                    ->sortable(),

                Tables\Columns\IconColumn::make('ativo')
                    ->label('Ativo')
                    ->boolean()
                    ->alignCenter(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('conselho_id')
                    ->label('Conselho')
                    ->options(fn () => \Modules\Conselhos\Models\Conselho::where('municipio_id', Filament::getTenant()->id)
                        ->orderBy('nome')
                        ->pluck('nome', 'id')
                        ->all()
                    )
                    ->searchable(),

                Tables\Filters\SelectFilter::make('tipo')
                    ->label('Cargo')
                    ->options([
                        'PRESIDENTE'      => 'Presidente',
                        'VICE_PRESIDENTE' => 'Vice-presidente',
                        'SECRETARIO'      => 'Secretário',
                        'MEMBRO'          => 'Membro',
                        'SUPLENTE'        => 'Suplente',
                    ]),

                Tables\Filters\TernaryFilter::make('ativo')
                    ->label('Situação')
                    ->placeholder('Todos')
                    ->trueLabel('Ativos')
                    ->falseLabel('Encerrados')
                    ->default(true),
            ])
            ->actions([
                Tables\Actions\Action::make('promover')
                    ->label('Promover')
                    ->icon('heroicon-o-arrow-up-circle')
                    ->color('warning')
                    ->visible(fn (Composicao $r) => $r->ativo && ! in_array($r->tipo, ['PRESIDENTE', 'SECRETARIO']))
                    ->form([
                        Forms\Components\Select::make('novo_tipo')
                            ->label('Novo cargo')
                            ->options([
                                'PRESIDENTE' => 'Presidente',
                                'SECRETARIO' => 'Secretário',
                            ])
                            ->required(),
                    ])
                    ->action(function (Composicao $record, array $data, ComposicaoService $service) {
                        try {
                            $service->promover($record, $data['novo_tipo']);
                            Notification::make()->title('Membro promovido')->success()->send();
                        } catch (\Illuminate\Validation\ValidationException $e) {
                            Notification::make()
                                ->title('Erro ao promover')
                                ->body(collect($e->errors())->flatten()->first())
                                ->danger()->send();
                        }
                    }),

                Tables\Actions\Action::make('encerrar')
                    ->label('Encerrar mandato')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Composicao $r) => $r->ativo)
                    ->requiresConfirmation()
                    ->modalHeading('Encerrar mandato')
                    ->modalDescription('O mandato será encerrado. Esta ação pode ser desfeita pelo administrador.')
                    ->form([
                        Forms\Components\DatePicker::make('data_fim')
                            ->label('Data de encerramento')
                            ->default(now()->toDateString())
                            ->required()
                            ->displayFormat('d/m/Y'),

                        Forms\Components\Textarea::make('motivo')
                            ->label('Motivo (opcional)')
                            ->rows(2)
                            ->maxLength(500),
                    ])
                    ->action(function (Composicao $record, array $data, ComposicaoService $service) {
                        try {
                            $service->encerrar($record, $data['data_fim'], $data['motivo'] ?? null);
                            Notification::make()->title('Mandato encerrado')->success()->send();
                        } catch (\Illuminate\Validation\ValidationException $e) {
                            Notification::make()
                                ->title('Erro ao encerrar')
                                ->body(collect($e->errors())->flatten()->first())
                                ->danger()->send();
                        }
                    }),

                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('conselho_id');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListComposicao::route('/'),
            'create' => Pages\CreateComposicao::route('/create'),
            'edit'   => Pages\EditComposicao::route('/{record}/edit'),
        ];
    }
}
