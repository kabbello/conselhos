<?php

namespace Modules\Auditoria\Filament\Resources;

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Auditoria\Filament\Resources\AtividadeResource\Pages;
use App\Models\Activity;

class AtividadeResource extends Resource
{
    protected static ?string $model = Activity::class;
    protected static bool $isScopedToTenant = false;
    protected static ?string $navigationIcon   = 'heroicon-o-magnifying-glass-circle';
    protected static ?string $navigationGroup  = 'Administração';
    protected static ?string $navigationLabel  = 'Auditoria';
    protected static ?string $modelLabel       = 'Registro de atividade';
    protected static ?string $pluralModelLabel = 'Registros de atividade';
    protected static ?int    $navigationSort   = 20;

    public static function canAccess(): bool
    {
        $user = Filament::auth()->user();
        return $user && $user->hasAnyRole(['auditor', 'encarregado_dados', 'admin_municipal', 'super_admin']);
    }

    public static function canCreate(): bool   { return false; }
    public static function canEdit($r): bool   { return false; }
    public static function canDelete($r): bool { return false; }

    /** Filtra apenas atividades causadas por usuários do município atual */
    public static function getEloquentQuery(): Builder
    {
        $tenant  = Filament::getTenant();
        $userIds = User::where('municipio_id', $tenant->id)->pluck('id');

        return parent::getEloquentQuery()
            ->where('causer_type', User::class)
            ->whereIn('causer_id', $userIds)
            ->latest();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data/hora')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),

                Tables\Columns\TextColumn::make('causer.name')
                    ->label('Usuário')
                    ->searchable()
                    ->default('—'),

                Tables\Columns\TextColumn::make('event')
                    ->label('Evento')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'created' => 'Criação',
                        'updated' => 'Atualização',
                        'deleted' => 'Exclusão',
                        'restored' => 'Restauração',
                        default    => $state ?? '—',
                    })
                    ->color(fn ($state) => match ($state) {
                        'created'  => 'success',
                        'updated'  => 'info',
                        'deleted'  => 'danger',
                        'restored' => 'warning',
                        default    => 'gray',
                    }),

                Tables\Columns\TextColumn::make('subject_type')
                    ->label('Entidade')
                    ->formatStateUsing(fn ($state) => static::resolveSubjectLabel($state))
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('subject_id')
                    ->label('ID')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Descrição')
                    ->limit(60)
                    ->tooltip(fn (Activity $r) => $r->description),

                Tables\Columns\TextColumn::make('properties')
                    ->label('Campos alterados')
                    ->formatStateUsing(function (Activity $record) {
                        $old = $record->properties->get('old', []);
                        $new = $record->properties->get('attributes', []);
                        if (empty($old) && empty($new)) {
                            return collect($record->properties->get('attributes', []))->keys()->implode(', ') ?: '—';
                        }
                        return collect(array_keys($old + $new))->implode(', ') ?: '—';
                    })
                    ->limit(50),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('causer_id')
                    ->label('Usuário')
                    ->options(function () {
                        $tenant  = Filament::getTenant();
                        return User::where('municipio_id', $tenant->id)
                            ->orderBy('name')
                            ->pluck('name', 'id');
                    })
                    ->searchable(),

                Tables\Filters\SelectFilter::make('event')
                    ->label('Evento')
                    ->options([
                        'created'  => 'Criação',
                        'updated'  => 'Atualização',
                        'deleted'  => 'Exclusão',
                        'restored' => 'Restauração',
                    ]),

                Tables\Filters\SelectFilter::make('subject_type')
                    ->label('Entidade')
                    ->options(static::subjectTypeOptions()),

                Tables\Filters\Filter::make('periodo')
                    ->label('Período')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('de')
                            ->label('De')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        \Filament\Forms\Components\DatePicker::make('ate')
                            ->label('Até')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                    ])
                    ->query(function (Builder $q, array $data) {
                        if ($data['de'])  $q->whereDate('created_at', '>=', $data['de']);
                        if ($data['ate']) $q->whereDate('created_at', '<=', $data['ate']);
                    })
                    ->columns(2),
            ])
            ->actions([
                Tables\Actions\Action::make('detalhes')
                    ->label('Detalhes')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading('Detalhes da atividade')
                    ->modalContent(fn (Activity $record) => view(
                        'auditoria::detalhe-atividade',
                        ['activity' => $record]
                    ))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fechar'),
            ])
            ->bulkActions([])
            ->paginated([25, 50, 100]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAtividades::route('/'),
        ];
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private static function resolveSubjectLabel(?string $fqcn): string
    {
        if (!$fqcn) return '—';

        return match ($fqcn) {
            'App\Models\User'                             => 'Usuário',
            'Modules\Conselhos\Models\Conselho'           => 'Conselho',
            'Modules\Composicao\Models\Composicao'        => 'Composição',
            'Modules\Composicao\Models\Conselheiro'       => 'Conselheiro',
            'Modules\Reunioes\Models\Reuniao'             => 'Reunião',
            'Modules\Documentos\Models\Documento'         => 'Documento',
            'Modules\Documentos\Models\Legislacao'        => 'Legislação',
            'Modules\Resolucoes\Models\AtoNormativo'      => 'Ato Normativo',
            'Modules\Comissoes\Models\Comissao'           => 'Comissão',
            'Modules\LGPD\Models\SolicitacaoLgpd'         => 'Solicitação LGPD',
            'Modules\LGPD\Models\RegistroTratamento'      => 'Reg. Tratamento',
            default => class_basename($fqcn),
        };
    }

    private static function subjectTypeOptions(): array
    {
        return [
            'App\Models\User'                             => 'Usuário',
            'Modules\Conselhos\Models\Conselho'           => 'Conselho',
            'Modules\Composicao\Models\Composicao'        => 'Composição',
            'Modules\Composicao\Models\Conselheiro'       => 'Conselheiro',
            'Modules\Reunioes\Models\Reuniao'             => 'Reunião',
            'Modules\Documentos\Models\Documento'         => 'Documento',
            'Modules\Documentos\Models\Legislacao'        => 'Legislação',
            'Modules\Resolucoes\Models\AtoNormativo'      => 'Ato Normativo',
            'Modules\Comissoes\Models\Comissao'           => 'Comissão',
            'Modules\LGPD\Models\SolicitacaoLgpd'         => 'Solicitação LGPD',
            'Modules\LGPD\Models\RegistroTratamento'      => 'Reg. Tratamento',
        ];
    }
}
