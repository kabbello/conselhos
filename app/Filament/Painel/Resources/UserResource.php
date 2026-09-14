<?php

namespace App\Filament\Painel\Resources;

use App\Filament\Painel\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use STS\FilamentImpersonate\Tables\Actions\Impersonate;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static bool $isScopedToTenant = false;
    protected static ?string $navigationIcon    = 'heroicon-o-users';
    protected static ?string $navigationGroup   = 'Administração';
    protected static ?string $navigationLabel   = 'Usuários';
    protected static ?string $modelLabel        = 'Usuário';
    protected static ?string $pluralModelLabel  = 'Usuários';
    protected static ?int    $navigationSort    = 10;

    // Visível apenas para admin_municipal e super_admin
    public static function canAccess(): bool
    {
        $user = Filament::auth()->user();
        return $user && ($user->hasRole('admin_municipal') || $user->hasRole('super_admin'));
    }

    public static function getEloquentQuery(): Builder
    {
        $tenant = Filament::getTenant();

        return parent::getEloquentQuery()
            ->where('municipio_id', $tenant->id)
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'super_admin'));
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Dados do usuário')->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nome completo')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('email')
                    ->label('E-mail')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(User::class, 'email', ignoreRecord: true),

                Forms\Components\TextInput::make('password')
                    ->label('Senha')
                    ->password()
                    ->revealable()
                    ->minLength(8)
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $operation) => $operation === 'create')
                    ->helperText(fn (string $operation) => $operation === 'edit' ? 'Deixe em branco para manter a senha atual.' : null),
            ])->columns(2),

            Forms\Components\Section::make('Perfil de acesso')->schema([
                Forms\Components\Select::make('roles')
                    ->label('Função')
                    ->relationship('roles', 'name')
                    ->options(
                        \Spatie\Permission\Models\Role::whereNotIn('name', ['super_admin'])
                            ->pluck('name', 'id')
                            ->map(fn ($name) => match ($name) {
                                'admin_municipal'  => 'Administrador Municipal',
                                'gestor_conselho'  => 'Gestor de Conselho',
                                'operador'         => 'Operador',
                                'conselheiro'      => 'Conselheiro',
                                'encarregado_dados' => 'Encarregado de Dados (LGPD)',
                                'auditor'          => 'Auditor',
                                default            => $name,
                            })
                    )
                    ->multiple()
                    ->preload()
                    ->required(),

                Forms\Components\Toggle::make('must_reset_password')
                    ->label('Forçar redefinição de senha no próximo login')
                    ->default(false)
                    ->inline(false),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Função')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'admin_municipal'  => 'Administrador',
                        'gestor_conselho'  => 'Gestor',
                        'operador'         => 'Operador',
                        'conselheiro'      => 'Conselheiro',
                        'encarregado_dados' => 'Enc. Dados',
                        'auditor'          => 'Auditor',
                        default            => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'admin_municipal' => 'danger',
                        'gestor_conselho' => 'warning',
                        'operador'        => 'info',
                        'conselheiro'     => 'success',
                        default           => 'gray',
                    }),

                Tables\Columns\IconColumn::make('must_reset_password')
                    ->label('Redefinir senha')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-circle')
                    ->trueColor('warning')
                    ->falseIcon('heroicon-o-check-circle')
                    ->falseColor('success')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->label('Função')
                    ->relationship('roles', 'name')
                    ->options([
                        'admin_municipal'   => 'Administrador Municipal',
                        'gestor_conselho'   => 'Gestor de Conselho',
                        'operador'          => 'Operador',
                        'conselheiro'       => 'Conselheiro',
                        'encarregado_dados' => 'Encarregado de Dados',
                        'auditor'           => 'Auditor',
                    ]),
            ])
            ->actions([
                // A09: impersonação exige motivo obrigatório registrado em log antes da ação.
                Impersonate::make()
                    ->label('Impersonar')
                    ->icon('heroicon-o-identification')
                    ->requiresConfirmation()
                    ->modalHeading('Confirmar impersonation')
                    ->modalDescription(null)
                    ->form([
                        Forms\Components\Textarea::make('motivo')
                            ->label('Motivo (obrigatório)')
                            ->required()
                            ->minLength(10)
                            ->rows(3)
                            ->placeholder('Descreva o motivo técnico ou operacional para acessar esta conta.')
                            ->helperText('O motivo é registrado no log de auditoria junto com seu nome e horário.'),
                    ])
                    ->before(function (User $record, array $data) {
                        activity('impersonation')
                            ->causedBy(auth()->user())
                            ->performedOn($record)
                            ->withProperties([
                                'motivo'             => $data['motivo'],
                                'impersonator_email' => auth()->user()->email,
                                'impersonated_email' => $record->email,
                            ])
                            ->event('impersonation_autorizado')
                            ->log('Motivo registrado antes de impersonar ' . $record->email);
                    })
                    ->redirectTo(fn () => Filament::getPanel('painel')->getUrl(Filament::getTenant())),

                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('reset_password')
                    ->label('Redefinir senha')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Redefinir senha')
                    ->modalDescription('O usuário será obrigado a criar uma nova senha no próximo login.')
                    ->action(fn (User $record) => $record->update(['must_reset_password' => true])),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
