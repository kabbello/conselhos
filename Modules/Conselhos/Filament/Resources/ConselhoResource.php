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
use Illuminate\Support\Facades\Hash;
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
                Forms\Components\FileUpload::make('logo_url')
                    ->label('Logotipo')
                    ->image()
                    ->disk('r2')
                    ->directory('conselhos/logos')
                    ->visibility('public')
                    ->maxSize(2048)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'])
                    ->helperText('PNG, JPG, WEBP ou SVG. Máx. 2 MB. Recomendado: fundo transparente.')
                    ->imagePreviewHeight('80')
                    ->columnSpanFull(),

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

            Forms\Components\Section::make('Notificações automáticas')
                ->description('Define se o sistema envia avisos automáticos à composição ao criar ou alterar uma reunião.')
                ->schema([
                    Forms\Components\Toggle::make('notif_email_ativo')
                        ->label('Enviar e-mail')
                        ->helperText('Notifica por e-mail ao criar ou alterar data/local/pauta de uma reunião.')
                        ->default(true)
                        ->inline(false),

                    Forms\Components\Toggle::make('notif_whatsapp_ativo')
                        ->label('Enviar WhatsApp')
                        ->helperText('Requer que a Evolution API esteja configurada no servidor.')
                        ->default(false)
                        ->inline(false),
                ])
                ->columns(2)
                ->collapsible(),
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
                    ->authorize(function (Conselho $record) {
                        $user = auth()->user();
                        if ($user->hasAnyRole(['admin_municipal', 'super_admin'])) {
                            return true;
                        }
                        // Presidente do conselho é gestor natural — pode atribuir outros gestores
                        return $record->composicao()
                            ->whereHas('conselheiro', fn ($q) => $q->where('user_id', $user->id))
                            ->where('tipo', 'PRESIDENTE')
                            ->where('ativo', true)
                            ->whereNull('deleted_at')
                            ->exists();
                    })
                    ->modalHeading(fn (Conselho $record) => "Gestores — {$record->sigla}")
                    ->modalDescription('Gerencie quem pode administrar este conselho. Gestores não precisam ser membros da composição.')
                    ->modalWidth('2xl')
                    ->form(fn (Conselho $record) => [
                        // Painel de gestores atuais
                        Forms\Components\Placeholder::make('gestores_atuais')
                            ->label('Gestores ativos')
                            ->content(function () use ($record) {
                                $gestores = $record->gestoresUsuarios()->get();

                                // Gestor natural: presidente com user_id vinculado
                                $presidente = $record->composicao()
                                    ->with('conselheiro.user')
                                    ->where('tipo', 'PRESIDENTE')
                                    ->where('ativo', true)
                                    ->whereNull('deleted_at')
                                    ->first();

                                $linhas = [];

                                if ($presidente?->conselheiro?->user) {
                                    $linhas[] = "⭐ {$presidente->conselheiro->user->name} (Presidente — gestor natural)";
                                }

                                foreach ($gestores as $g) {
                                    $linhas[] = "✓ {$g->name}";
                                }

                                return $linhas ? implode("\n", $linhas) : '— nenhum gestor atribuído';
                            }),

                        Forms\Components\Radio::make('modo')
                            ->label('Adicionar gestor')
                            ->options([
                                'existente' => 'Usuário já cadastrado no município',
                                'externo'   => 'Novo usuário externo (criar conta)',
                            ])
                            ->default('existente')
                            ->live()
                            ->inline(),

                        // --- Usuário existente ---
                        Forms\Components\Select::make('user_id')
                            ->label('Selecionar usuário')
                            ->placeholder('Digite para buscar...')
                            ->options(
                                User::where('municipio_id', Filament::getTenant()->id)
                                    ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'super_admin'))
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                            )
                            ->preload()
                            ->searchable()
                            ->nullable()
                            ->visible(fn (Forms\Get $get) => $get('modo') === 'existente')
                            ->helperText('Deixe em branco para apenas visualizar os gestores atuais.'),

                        // --- Usuário externo ---
                        Forms\Components\TextInput::make('ext_nome')
                            ->label('Nome completo')
                            ->maxLength(255)
                            ->visible(fn (Forms\Get $get) => $get('modo') === 'externo'),

                        Forms\Components\TextInput::make('ext_email')
                            ->label('E-mail')
                            ->email()
                            ->maxLength(255)
                            ->unique(User::class, 'email')
                            ->visible(fn (Forms\Get $get) => $get('modo') === 'externo'),

                        Forms\Components\TextInput::make('ext_senha')
                            ->label('Senha inicial')
                            ->password()
                            ->revealable()
                            ->minLength(8)
                            ->visible(fn (Forms\Get $get) => $get('modo') === 'externo')
                            ->helperText('O usuário deverá redefinir esta senha no primeiro acesso.'),
                    ])
                    ->action(function (Conselho $record, array $data) {
                        $municipioId = Filament::getTenant()->id;

                        if ($data['modo'] === 'externo') {
                            // Validação mínima
                            if (empty($data['ext_nome']) || empty($data['ext_email']) || empty($data['ext_senha'])) {
                                Notification::make()->title('Preencha nome, e-mail e senha para criar um usuário externo.')->warning()->send();
                                return;
                            }

                            $user = User::create([
                                'municipio_id'        => $municipioId,
                                'name'                => $data['ext_nome'],
                                'email'               => $data['ext_email'],
                                'password'            => Hash::make($data['ext_senha']),
                                'must_reset_password' => true,
                            ]);
                        } else {
                            if (empty($data['user_id'])) {
                                return; // Só visualização
                            }
                            $user = User::find((int) $data['user_id']);
                        }

                        if (! $user) return;

                        DB::table('user_conselho_gestores')->upsert(
                            [
                                'user_id'       => $user->id,
                                'conselho_id'   => $record->id,
                                'atribuido_por' => auth()->id(),
                                'atribuido_em'  => now(),
                                'revogado_em'   => null,
                            ],
                            ['user_id', 'conselho_id'],
                            ['atribuido_por', 'atribuido_em', 'revogado_em'],
                        );

                        if (! $user->hasRole('gestor_conselho')) {
                            $user->assignRole('gestor_conselho');
                        }

                        activity('gestores-conselho')
                            ->causedBy(auth()->user())
                            ->performedOn($record)
                            ->withProperties(['user_id' => $user->id, 'user_name' => $user->name])
                            ->event('gestor_atribuido')
                            ->log("Gestor {$user->name} atribuído ao conselho {$record->sigla}");

                        Notification::make()
                            ->title('Gestor atribuído com sucesso.')
                            ->body($user->name)
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('revogar_gestor')
                    ->label('Revogar gestor')
                    ->icon('heroicon-o-shield-exclamation')
                    ->color('danger')
                    ->authorize(function (Conselho $record) {
                        $user = auth()->user();
                        if ($user->hasAnyRole(['admin_municipal', 'super_admin'])) return true;
                        return $record->composicao()
                            ->whereHas('conselheiro', fn ($q) => $q->where('user_id', $user->id))
                            ->where('tipo', 'PRESIDENTE')
                            ->where('ativo', true)
                            ->whereNull('deleted_at')
                            ->exists();
                    })
                    ->form(fn (Conselho $record) => [
                        Forms\Components\Select::make('user_id')
                            ->label('Gestor a revogar')
                            ->options(
                                $record->gestoresUsuarios()->pluck('users.name', 'users.id')
                            )
                            ->preload()
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

                        if ($user && $user->conselhosSobGestao()->doesntExist()) {
                            $user->removeRole('gestor_conselho');
                        }

                        activity('gestores-conselho')
                            ->causedBy(auth()->user())
                            ->performedOn($record)
                            ->withProperties(['user_id' => $data['user_id'], 'user_name' => $user?->name])
                            ->event('gestor_revogado')
                            ->log("Gestor {$user?->name} revogado do conselho {$record->sigla}");

                        Notification::make()->title('Gestor revogado.')->success()->send();
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
