<?php

namespace Modules\LGPD\Filament\Resources;

use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\LGPD\Filament\Resources\SolicitacaoLgpdResource\Pages;
use Modules\LGPD\Models\SolicitacaoLgpd;

class SolicitacaoLgpdResource extends Resource
{
    protected static ?string $model = SolicitacaoLgpd::class;
    protected static bool $isScopedToTenant = false;
    protected static ?string $navigationIcon   = 'heroicon-o-shield-exclamation';
    protected static ?string $navigationGroup  = 'LGPD';
    protected static ?string $navigationLabel  = 'Solicitações';
    protected static ?string $modelLabel       = 'Solicitação';
    protected static ?string $pluralModelLabel = 'Solicitações';
    protected static ?int    $navigationSort   = 1;

    public static function canAccess(): bool
    {
        $user = Filament::auth()->user();
        return $user && $user->hasAnyRole(['encarregado_dados', 'admin_municipal', 'super_admin']);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('municipio_id', Filament::getTenant()->id);
    }

    public static function getNavigationBadge(): ?string
    {
        $tenant = Filament::getTenant();
        $count = static::getEloquentQuery()
            ->whereIn('status', ['pendente', 'em_atendimento'])
            ->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identificação do titular')->schema([
                Forms\Components\TextInput::make('protocolo')
                    ->label('Protocolo')
                    ->disabled()
                    ->dehydrated(false)
                    ->placeholder('Gerado automaticamente')
                    ->visibleOn('edit'),

                Forms\Components\TextInput::make('nome_titular')
                    ->label('Nome do titular')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('email_titular')
                    ->label('E-mail')
                    ->email()
                    ->maxLength(255),

                Forms\Components\TextInput::make('cpf_titular')
                    ->label('CPF')
                    ->mask('999.999.999-99')
                    ->maxLength(14),
            ])->columns(2),

            Forms\Components\Section::make('Solicitação')->schema([
                Forms\Components\Select::make('tipo')
                    ->label('Tipo de solicitação')
                    ->required()
                    ->options(collect([
                        'acesso', 'correcao', 'exclusao', 'portabilidade',
                        'oposicao', 'revogacao_consentimento', 'informacao', 'outro',
                    ])->mapWithKeys(fn ($v) => [$v => SolicitacaoLgpd::tipoLabel($v)])),

                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->required()
                    ->options(collect([
                        'pendente', 'em_atendimento', 'atendida', 'negada', 'arquivada',
                    ])->mapWithKeys(fn ($v) => [$v => SolicitacaoLgpd::statusLabel($v)]))
                    ->default('pendente'),

                Forms\Components\Textarea::make('descricao')
                    ->label('Descrição / Solicitação do titular')
                    ->required()
                    ->rows(4)
                    ->columnSpanFull(),
            ])->columns(2),

            Forms\Components\Section::make('Resposta')
                ->description('Preencha ao atender ou negar a solicitação.')
                ->schema([
                    Forms\Components\Textarea::make('resposta')
                        ->label('Resposta ao titular')
                        ->rows(5)
                        ->columnSpanFull(),

                    Forms\Components\DateTimePicker::make('atendida_em')
                        ->label('Data/hora de atendimento')
                        ->native(false)
                        ->displayFormat('d/m/Y H:i'),
                ])->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('protocolo')
                    ->label('Protocolo')
                    ->searchable()
                    ->copyable()
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('nome_titular')
                    ->label('Titular')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn ($state) => SolicitacaoLgpd::tipoLabel($state))
                    ->color('info'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => SolicitacaoLgpd::statusLabel($state))
                    ->color(fn (string $state) => match ($state) {
                        'pendente'       => 'danger',
                        'em_atendimento' => 'warning',
                        'atendida'       => 'success',
                        'negada'         => 'gray',
                        'arquivada'      => 'gray',
                        default          => 'gray',
                    }),

                Tables\Columns\TextColumn::make('prazo_legal')
                    ->label('Prazo legal')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn (SolicitacaoLgpd $record) => $record->isAtrasada() ? 'danger' : null)
                    ->weight(fn (SolicitacaoLgpd $record) => $record->isAtrasada() ? 'bold' : null),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Recebida em')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(collect([
                        'pendente', 'em_atendimento', 'atendida', 'negada', 'arquivada',
                    ])->mapWithKeys(fn ($v) => [$v => SolicitacaoLgpd::statusLabel($v)]))
                    ->default('pendente'),

                Tables\Filters\SelectFilter::make('tipo')
                    ->label('Tipo')
                    ->options(collect([
                        'acesso', 'correcao', 'exclusao', 'portabilidade',
                        'oposicao', 'revogacao_consentimento', 'informacao', 'outro',
                    ])->mapWithKeys(fn ($v) => [$v => SolicitacaoLgpd::tipoLabel($v)])),

                Tables\Filters\Filter::make('atrasadas')
                    ->label('Somente atrasadas')
                    ->query(fn (Builder $q) => $q
                        ->whereDate('prazo_legal', '<', now())
                        ->whereNotIn('status', ['atendida', 'negada', 'arquivada'])
                    ),
            ])
            ->actions([
                Tables\Actions\Action::make('atender')
                    ->label('Atender')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (SolicitacaoLgpd $r) => in_array($r->status, ['pendente', 'em_atendimento']))
                    ->form([
                        Forms\Components\Select::make('status')
                            ->label('Encerrar como')
                            ->required()
                            ->options([
                                'atendida' => 'Atendida',
                                'negada'   => 'Negada',
                            ]),
                        Forms\Components\Textarea::make('resposta')
                            ->label('Resposta ao titular')
                            ->required()
                            ->rows(5),
                    ])
                    ->action(function (SolicitacaoLgpd $record, array $data) {
                        $record->update([
                            'status'      => $data['status'],
                            'resposta'    => $data['resposta'],
                            'atendida_em' => now(),
                            'atendida_por' => Filament::auth()->id(),
                        ]);
                        Notification::make()->title('Solicitação encerrada.')->success()->send();
                    }),

                Tables\Actions\Action::make('em_atendimento')
                    ->label('Iniciar atendimento')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->color('warning')
                    ->visible(fn (SolicitacaoLgpd $r) => $r->status === 'pendente')
                    ->action(function (SolicitacaoLgpd $record) {
                        $record->update(['status' => 'em_atendimento']);
                        Notification::make()->title('Solicitação em atendimento.')->warning()->send();
                    }),

                Tables\Actions\EditAction::make(),
            ])
            // A04: exclusão em massa de solicitações LGPD desabilitada —
            // cada atendimento deve ser individual, fundamentado e auditado.
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSolicitacoes::route('/'),
            'create' => Pages\CreateSolicitacao::route('/create'),
            'edit'   => Pages\EditSolicitacao::route('/{record}/edit'),
        ];
    }
}
