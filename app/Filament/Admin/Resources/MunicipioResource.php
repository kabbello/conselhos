<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MunicipioResource\Pages;
use App\Models\Municipio;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MunicipioResource extends Resource
{
    protected static ?string $model = Municipio::class;
    protected static ?string $navigationIcon  = 'heroicon-o-map-pin';
    protected static ?string $navigationLabel = 'Municípios';
    protected static ?string $modelLabel      = 'Município';
    protected static ?string $pluralModelLabel = 'Municípios';
    protected static ?int    $navigationSort  = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Identidade')->schema([
                Forms\Components\TextInput::make('nome')
                    ->label('Nome do município')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, Forms\Set $set) =>
                        $set('slug', Str::slug($state))
                    )
                    ->columnSpan(2),

                Forms\Components\TextInput::make('slug')
                    ->label('Slug (URL)')
                    ->required()
                    ->maxLength(255)
                    ->unique(Municipio::class, 'slug', ignoreRecord: true)
                    ->helperText('Usado na URL do portal: /portal/{slug}'),

                Forms\Components\TextInput::make('sigla')
                    ->label('Sigla')
                    ->maxLength(10)
                    ->placeholder('Ex.: PRB'),

                Forms\Components\Select::make('uf')
                    ->label('Estado (UF)')
                    ->required()
                    ->searchable()
                    ->options([
                        'AC' => 'Acre', 'AL' => 'Alagoas', 'AP' => 'Amapá',
                        'AM' => 'Amazonas', 'BA' => 'Bahia', 'CE' => 'Ceará',
                        'DF' => 'Distrito Federal', 'ES' => 'Espírito Santo',
                        'GO' => 'Goiás', 'MA' => 'Maranhão', 'MT' => 'Mato Grosso',
                        'MS' => 'Mato Grosso do Sul', 'MG' => 'Minas Gerais',
                        'PA' => 'Pará', 'PB' => 'Paraíba', 'PR' => 'Paraná',
                        'PE' => 'Pernambuco', 'PI' => 'Piauí', 'RJ' => 'Rio de Janeiro',
                        'RN' => 'Rio Grande do Norte', 'RS' => 'Rio Grande do Sul',
                        'RO' => 'Rondônia', 'RR' => 'Roraima', 'SC' => 'Santa Catarina',
                        'SP' => 'São Paulo', 'SE' => 'Sergipe', 'TO' => 'Tocantins',
                    ]),

                Forms\Components\TextInput::make('codigo_ibge')
                    ->label('Código IBGE')
                    ->maxLength(10)
                    ->placeholder('Ex.: 3537305'),

                Forms\Components\Toggle::make('ativo')
                    ->label('Ativo')
                    ->default(true)
                    ->inline(false),
            ])->columns(3),

            Forms\Components\Section::make('Identidade Visual')->schema([
                Forms\Components\FileUpload::make('logo_path')
                    ->label('Logotipo')
                    ->image()
                    ->disk('r2')
                    ->directory('municipios/logos')
                    ->visibility('public')
                    ->maxSize(4096)
                    ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])
                    ->helperText('PNG, JPG ou WEBP. Máx. 4MB.')
                    ->imagePreviewHeight('120'),

                Forms\Components\FileUpload::make('brasao_path')
                    ->label('Brasão / Símbolo')
                    ->image()
                    ->disk('r2')
                    ->directory('municipios/brasoes')
                    ->visibility('public')
                    ->maxSize(4096)
                    ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])
                    ->helperText('PNG, JPG ou WEBP. Máx. 4MB.')
                    ->imagePreviewHeight('120'),

                Forms\Components\ColorPicker::make('cor_primaria')
                    ->label('Cor primária do portal')
                    ->helperText('Cor usada no cabeçalho e destaques do portal público.')
                    ->default('#1e40af'),
            ])->columns(3),

            Forms\Components\Section::make('Contato')->schema([
                Forms\Components\TextInput::make('email')
                    ->label('E-mail institucional')
                    ->email()
                    ->maxLength(255),

                Forms\Components\TextInput::make('telefone')
                    ->label('Telefone')
                    ->tel()
                    ->maxLength(50)
                    ->placeholder('(13) 3456-7890'),

                Forms\Components\TextInput::make('site')
                    ->label('Site oficial da Prefeitura')
                    ->url()
                    ->maxLength(255)
                    ->placeholder('https://www.peruibe.sp.gov.br'),

                Forms\Components\TextInput::make('link_transparencia')
                    ->label('URL — Portal de Transparência')
                    ->url()
                    ->maxLength(500)
                    ->placeholder('https://peruibe.sp.gov.br/transparencia')
                    ->helperText('Exibido no rodapé do portal como "Portal de Transparência Municipal".'),

                Forms\Components\TextInput::make('link_ouvidoria')
                    ->label('URL — Ouvidoria Municipal')
                    ->url()
                    ->maxLength(500)
                    ->placeholder('https://peruibe.sp.gov.br/ouvidoria')
                    ->helperText('Exibido no rodapé quando preenchido.'),
            ])->columns(3),

            Forms\Components\Section::make('Endereço')->schema([
                Forms\Components\TextInput::make('endereco')
                    ->label('Endereço da prefeitura')
                    ->maxLength(500)
                    ->columnSpan(2),

                Forms\Components\TextInput::make('cep')
                    ->label('CEP')
                    ->maxLength(9)
                    ->placeholder('11750-000'),
            ])->columns(3),

            Forms\Components\Section::make('Dados Institucionais')->schema([
                Forms\Components\TextInput::make('prefeito')
                    ->label('Nome do(a) prefeito(a)')
                    ->maxLength(255)
                    ->columnSpan(2),

                Forms\Components\TextInput::make('populacao')
                    ->label('População estimada')
                    ->numeric()
                    ->minValue(0)
                    ->suffix('hab.'),

                Forms\Components\TextInput::make('area_km2')
                    ->label('Área territorial')
                    ->numeric()
                    ->minValue(0)
                    ->suffix('km²'),

                Forms\Components\Textarea::make('descricao')
                    ->label('Descrição curta')
                    ->maxLength(1000)
                    ->rows(3)
                    ->columnSpanFull()
                    ->helperText('Exibida no cabeçalho do portal público.'),
            ])->columns(3),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo_path')
                    ->label('Logo')
                    ->disk('r2')
                    ->height(36)
                    ->defaultImageUrl(fn () => null)
                    ->extraImgAttributes(['class' => 'rounded']),

                Tables\Columns\TextColumn::make('nome')
                    ->label('Município')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->description(fn (Municipio $r) => $r->sigla ? "{$r->sigla} — {$r->uf}" : $r->uf),

                Tables\Columns\TextColumn::make('codigo_ibge')
                    ->label('IBGE')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('prefeito')
                    ->label('Prefeito(a)')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('E-mail')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('conselhos_count')
                    ->label('Conselhos')
                    ->counts('conselhos')
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
                    ->falseLabel('Inativos'),

                Tables\Filters\SelectFilter::make('uf')
                    ->label('Estado')
                    ->options(fn () => Municipio::distinct()->pluck('uf', 'uf')->sort()->toArray()),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('criar_admin')
                    ->label('Criar Admin')
                    ->icon('heroicon-o-user-plus')
                    ->color('success')
                    ->modalHeading(fn (Municipio $record) => "Criar administrador — {$record->nome}")
                    ->modalDescription('Cria o primeiro usuário administrador municipal. Uma senha temporária será definida e o usuário será obrigado a redefini-la no primeiro acesso.')
                    ->form([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome completo')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(User::class, 'email'),

                        Forms\Components\TextInput::make('password')
                            ->label('Senha temporária')
                            ->password()
                            ->revealable()
                            ->required()
                            ->minLength(8)
                            ->helperText('O usuário deverá redefinir esta senha no primeiro acesso.'),
                    ])
                    ->action(function (Municipio $record, array $data) {
                        $user = User::create([
                            'municipio_id'        => $record->id,
                            'name'                => $data['name'],
                            'email'               => $data['email'],
                            'password'            => Hash::make($data['password']),
                            'must_reset_password' => true,
                        ]);
                        $user->assignRole('admin_municipal');

                        Notification::make()
                            ->title('Administrador criado com sucesso')
                            ->body("Usuário {$data['email']} criado para {$record->nome}.")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('impersonar')
                    ->label('Impersonar')
                    ->icon('heroicon-o-identification')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading(fn (Municipio $record) => "Impersonar admin — {$record->nome}")
                    ->modalDescription(null)
                    ->form([
                        Forms\Components\Textarea::make('motivo')
                            ->label('Motivo (obrigatório)')
                            ->required()
                            ->minLength(10)
                            ->rows(3)
                            ->placeholder('Descreva o motivo técnico ou operacional para acessar esta conta.')
                            ->helperText('O motivo é registrado no log de auditoria.'),
                    ])
                    ->action(function (Municipio $record, array $data) {
                        $admin = User::where('municipio_id', $record->id)
                            ->whereHas('roles', fn ($q) => $q->where('name', 'admin_municipal'))
                            ->first();

                        if (! $admin) {
                            Notification::make()
                                ->title('Nenhum administrador encontrado')
                                ->body("Crie primeiro um administrador para {$record->nome}.")
                                ->warning()
                                ->send();
                            return;
                        }

                        activity('impersonation')
                            ->causedBy(auth()->user())
                            ->performedOn($admin)
                            ->withProperties([
                                'motivo'             => $data['motivo'],
                                'impersonator_email' => auth()->user()->email,
                                'impersonated_email' => $admin->email,
                                'municipio'          => $record->nome,
                            ])
                            ->event('impersonation_autorizado')
                            ->log('Impersonação via MunicipioResource: ' . $admin->email);

                        // Grava as chaves de sessão que o stechstudio/filament-impersonate
                        // espera ao encerrar a sessão (leave). Sem isso, back_to seria null
                        // e redirect(null) causaria TypeError 500.
                        session()->put([
                            'impersonate.back_to' => url('/admin'),
                            'impersonate.guard'   => 'web',
                        ]);

                        app(\Lab404\Impersonate\Services\ImpersonateManager::class)
                            ->take(auth()->user(), $admin, 'web');

                        return redirect('/painel/municipio/' . $record->slug);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('nome');
    }

    public static function getRelationManagers(): array
    {
        return [
            MunicipioResource\RelationManagers\UsuariosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListMunicipios::route('/'),
            'create' => Pages\CreateMunicipio::route('/create'),
            'edit'   => Pages\EditMunicipio::route('/{record}/edit'),
        ];
    }
}
