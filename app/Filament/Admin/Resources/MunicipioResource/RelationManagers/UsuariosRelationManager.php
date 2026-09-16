<?php

namespace App\Filament\Admin\Resources\MunicipioResource\RelationManagers;

use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Spatie\Activitylog\Models\Activity;

class UsuariosRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    protected static ?string $title = 'Usuários';

    protected static ?string $modelLabel = 'Usuário';

    protected static ?string $pluralModelLabel = 'Usuários';

    public function form(Form $form): Form
    {
        return $form->schema([
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
                ->helperText(fn (string $operation) => $operation === 'edit'
                    ? 'Deixe em branco para manter a senha atual.' : null),

            Forms\Components\Select::make('roles')
                ->label('Função')
                ->relationship('roles', 'name')
                ->options(
                    \Spatie\Permission\Models\Role::whereNotIn('name', ['super_admin'])
                        ->pluck('name', 'id')
                        ->map(fn ($name) => match ($name) {
                            'admin_municipal'   => 'Administrador Municipal',
                            'gestor_conselho'   => 'Gestor de Conselho',
                            'operador'          => 'Operador',
                            'conselheiro'       => 'Conselheiro',
                            'encarregado_dados' => 'Encarregado de Dados (LGPD)',
                            'auditor'           => 'Auditor',
                            default             => $name,
                        })
                )
                ->multiple()
                ->preload(),

            Forms\Components\Toggle::make('must_reset_password')
                ->label('Forçar redefinição de senha no próximo login')
                ->default(true)
                ->inline(false),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
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
                        'admin_municipal'   => 'Administrador',
                        'gestor_conselho'   => 'Gestor',
                        'operador'          => 'Operador',
                        'conselheiro'       => 'Conselheiro',
                        'encarregado_dados' => 'Enc. Dados',
                        'auditor'           => 'Auditor',
                        default             => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'admin_municipal' => 'danger',
                        'gestor_conselho' => 'warning',
                        'operador'        => 'info',
                        'conselheiro'     => 'success',
                        default           => 'gray',
                    }),

                Tables\Columns\IconColumn::make('must_reset_password')
                    ->label('Reset senha')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-circle')
                    ->trueColor('warning')
                    ->falseIcon('heroicon-o-check-circle')
                    ->falseColor('success')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Novo usuário')
                    ->mutateFormDataUsing(function (array $data): array {
                        // municipio_id já é injetado pelo RelationManager via relationship
                        $data['must_reset_password'] = $data['must_reset_password'] ?? true;
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('impersonar')
                    ->label('Impersonar')
                    ->icon('heroicon-o-identification')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading(fn (User $record) => "Impersonar {$record->name}")
                    ->modalDescription(null)
                    ->form([
                        Forms\Components\Textarea::make('motivo')
                            ->label('Motivo (obrigatório)')
                            ->required()
                            ->minLength(10)
                            ->rows(3)
                            ->placeholder('Descreva o motivo técnico ou operacional para acessar esta conta.')
                            ->helperText('Registrado no log de auditoria.'),
                    ])
                    ->visible(fn (User $record) => $record->canBeImpersonated())
                    ->action(function (User $record, array $data) {
                        activity('impersonation')
                            ->causedBy(auth()->user())
                            ->performedOn($record)
                            ->withProperties([
                                'motivo'             => $data['motivo'],
                                'impersonator_email' => auth()->user()->email,
                                'impersonated_email' => $record->email,
                            ])
                            ->event('impersonation_autorizado')
                            ->log('Impersonação via painel admin: ' . $record->email);

                        session()->put([
                            'impersonate.back_to' => url('/admin'),
                            'impersonate.guard'   => 'web',
                        ]);

                        app(\Lab404\Impersonate\Services\ImpersonateManager::class)
                            ->take(auth()->user(), $record, 'web');

                        $municipio = $this->getOwnerRecord();
                        return redirect('/painel/municipio/' . $municipio->slug);
                    }),

                Tables\Actions\Action::make('historico')
                    ->label('Histórico')
                    ->icon('heroicon-o-clock')
                    ->color('gray')
                    ->modalHeading(fn (User $record) => "Histórico de atividades — {$record->name}")
                    ->modalContent(function (User $record) {
                        $logs = Activity::where(function ($q) use ($record) {
                                // atividades causadas pelo usuário
                                $q->where('causer_type', User::class)
                                  ->where('causer_id', $record->id);
                            })
                            ->orWhere(function ($q) use ($record) {
                                // impersonações sobre o usuário
                                $q->where('subject_type', User::class)
                                  ->where('subject_id', $record->id)
                                  ->where('log_name', 'impersonation');
                            })
                            ->latest()
                            ->limit(50)
                            ->get();

                        return view('filament.admin.historico-usuario', compact('logs', 'record'));
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fechar'),

                Tables\Actions\Action::make('reset_password')
                    ->label('Forçar reset')
                    ->icon('heroicon-o-key')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalDescription('O usuário será obrigado a criar uma nova senha no próximo login.')
                    ->action(fn (User $record) => $record->update(['must_reset_password' => true]))
                    ->after(fn () => Notification::make()->title('Reset de senha ativado')->success()->send()),

                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('name');
    }
}
