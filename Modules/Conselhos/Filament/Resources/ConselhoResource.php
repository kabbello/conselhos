<?php

namespace Modules\Conselhos\Filament\Resources;

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Composicao\Filament\Resources\ComposicaoResource;
use Modules\Conselhos\Filament\Resources\ConselhoResource\Pages;
use Modules\Conselhos\Models\Conselho;

class ConselhoResource extends Resource
{
    protected static ?string $model = Conselho::class;
    protected static bool $isScopedToTenant = false;
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationGroup = 'Conselho';
    protected static ?string $navigationLabel = 'Conselhos';
    protected static ?string $modelLabel = 'Conselho';
    protected static ?string $pluralModelLabel = 'Conselhos';
    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('municipio_id', Filament::getTenant()->id);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identificação')->schema([
                Forms\Components\TextInput::make('nome')
                    ->label('Nome do conselho')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, Forms\Set $set) =>
                        $set('slug', Str::slug($state))
                    )
                    ->columnSpan(2),

                Forms\Components\TextInput::make('sigla')
                    ->label('Sigla')
                    ->maxLength(20)
                    ->placeholder('Ex.: CMAS'),

                Forms\Components\TextInput::make('slug')
                    ->label('Slug (URL)')
                    ->required()
                    ->maxLength(255)
                    ->unique(Conselho::class, 'slug', ignoreRecord: true)
                    ->helperText('Identificador único na URL'),
            ])->columns(3),

            Forms\Components\Section::make('Tipo e situação')->schema([
                Forms\Components\Select::make('tipo')
                    ->label('Tipo')
                    ->options([
                        'DELIBERATIVO' => 'Deliberativo',
                        'CONSULTIVO'   => 'Consultivo',
                        'FISCALIZADOR' => 'Fiscalizador',
                        'NORMATIVO'    => 'Normativo',
                    ])
                    ->required(),

                Forms\Components\Toggle::make('ativo')
                    ->label('Ativo')
                    ->default(true)
                    ->inline(false),
            ])->columns(2),

            Forms\Components\Section::make('Contato')->schema([
                Forms\Components\TextInput::make('email')
                    ->label('E-mail')
                    ->email()
                    ->maxLength(255),

                Forms\Components\TextInput::make('telefone')
                    ->label('Telefone')
                    ->tel()
                    ->maxLength(20),

                Forms\Components\Textarea::make('endereco')
                    ->label('Endereço')
                    ->maxLength(500)
                    ->columnSpanFull(),
            ])->columns(2),

            Forms\Components\Section::make('Descrição')->schema([
                Forms\Components\RichEditor::make('descricao')
                    ->label('Descrição')
                    ->toolbarButtons(['bold', 'italic', 'link', 'bulletList', 'orderedList'])
                    ->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome')
                    ->label('Conselho')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('sigla')
                    ->label('Sigla')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'DELIBERATIVO' => 'success',
                        'CONSULTIVO'   => 'info',
                        'FISCALIZADOR' => 'warning',
                        'NORMATIVO'    => 'primary',
                        default        => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'DELIBERATIVO' => 'Deliberativo',
                        'CONSULTIVO'   => 'Consultivo',
                        'FISCALIZADOR' => 'Fiscalizador',
                        'NORMATIVO'    => 'Normativo',
                        default        => $state,
                    }),

                Tables\Columns\TextColumn::make('membrosAtivos_count')
                    ->label('Membros ativos')
                    ->counts('membrosAtivos')
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('ativo')
                    ->label('Ativo')
                    ->boolean()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atualizado')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('ativo')
                    ->label('Situação')
                    ->placeholder('Todos')
                    ->trueLabel('Ativos')
                    ->falseLabel('Inativos')
                    ->default(true),

                Tables\Filters\SelectFilter::make('tipo')
                    ->label('Tipo')
                    ->options([
                        'DELIBERATIVO' => 'Deliberativo',
                        'CONSULTIVO'   => 'Consultivo',
                        'FISCALIZADOR' => 'Fiscalizador',
                        'NORMATIVO'    => 'Normativo',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('composicao')
                    ->label('Composição')
                    ->icon('heroicon-o-users')
                    ->url(fn (Conselho $record): string =>
                        ComposicaoResource::getUrl('index', ['conselho' => $record->id])
                    ),

                Tables\Actions\Action::make('gestores')
                    ->label('Gestores')
                    ->icon('heroicon-o-shield-check')
                    ->color('warning')
                    ->authorize(fn () => auth()->user()->hasAnyRole(['admin_municipal', 'super_admin'])
                        || auth()->user()->hasRole('gestor_conselho'))
                    ->modalHeading(fn (Conselho $record) => "Gestores — {$record->sigla}")
                    ->modalDescription('Usuários com papel gestor_conselho atribuído a este conselho. Não é necessário ser membro da composição.')
                    ->form(fn (Conselho $record) => [
                        Forms\Components\Select::make('user_id')
                            ->label('Atribuir gestor')
                            ->placeholder('Selecione um usuário do município...')
                            ->options(
                                User::where('municipio_id', Filament::getTenant()->id)
                                    ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'super_admin'))
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->nullable()
                            ->helperText('Deixe em branco para apenas visualizar os gestores atuais.'),

                        Forms\Components\Placeholder::make('gestores_atuais')
                            ->label('Gestores ativos')
                            ->content(function () use ($record) {
                                $lista = $record->gestoresUsuarios()->pluck('name')->join(', ');
                                return $lista ?: '— nenhum gestor atribuído';
                            }),
                    ])
                    ->action(function (Conselho $record, array $data) {
                        if (empty($data['user_id'])) {
                            return;
                        }

                        $userId = (int) $data['user_id'];

                        DB::table('user_conselho_gestores')->upsert(
                            [
                                'user_id'       => $userId,
                                'conselho_id'   => $record->id,
                                'atribuido_por' => auth()->id(),
                                'atribuido_em'  => now(),
                                'revogado_em'   => null,
                            ],
                            ['user_id', 'conselho_id'],
                            ['atribuido_por', 'atribuido_em', 'revogado_em'],
                        );

                        // Garante que o usuário tem o papel gestor_conselho
                        $user = User::find($userId);
                        if ($user && ! $user->hasRole('gestor_conselho')) {
                            $user->assignRole('gestor_conselho');
                        }

                        activity('gestores-conselho')
                            ->causedBy(auth()->user())
                            ->performedOn($record)
                            ->withProperties(['user_id' => $userId, 'user_name' => $user?->name])
                            ->event('gestor_atribuido')
                            ->log("Gestor {$user?->name} atribuído ao conselho {$record->sigla}");

                        Notification::make()
                            ->title('Gestor atribuído com sucesso.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('revogar_gestor')
                    ->label('Revogar gestor')
                    ->icon('heroicon-o-shield-exclamation')
                    ->color('danger')
                    ->authorize(fn () => auth()->user()->hasAnyRole(['admin_municipal', 'super_admin']))
                    ->form(fn (Conselho $record) => [
                        Forms\Components\Select::make('user_id')
                            ->label('Gestor a revogar')
                            ->options(
                                $record->gestoresUsuarios()->pluck('users.name', 'users.id')
                            )
                            ->required()
                            ->searchable(),
                    ])
                    ->action(function (Conselho $record, array $data) {
                        DB::table('user_conselho_gestores')
                            ->where('user_id', $data['user_id'])
                            ->where('conselho_id', $record->id)
                            ->whereNull('revogado_em')
                            ->update(['revogado_em' => now()]);

                        $user = User::find($data['user_id']);

                        // Remove o papel se o usuário não gerencia mais nenhum conselho
                        if ($user && $user->conselhosSobGestao()->doesntExist()) {
                            $user->removeRole('gestor_conselho');
                        }

                        activity('gestores-conselho')
                            ->causedBy(auth()->user())
                            ->performedOn($record)
                            ->withProperties(['user_id' => $data['user_id'], 'user_name' => $user?->name])
                            ->event('gestor_revogado')
                            ->log("Gestor {$user?->name} revogado do conselho {$record->sigla}");

                        Notification::make()
                            ->title('Gestor revogado com sucesso.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('nome');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListConselhos::route('/'),
            'create' => Pages\CreateConselho::route('/create'),
            'edit'   => Pages\EditConselho::route('/{record}/edit'),
        ];
    }
}
