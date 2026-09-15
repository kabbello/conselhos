<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MunicipioResource\Pages;
use App\Models\Municipio;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
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
                    ->disk('public')
                    ->directory('municipios/logos')
                    ->imagePreviewHeight('120'),

                Forms\Components\FileUpload::make('brasao_path')
                    ->label('Brasão / Símbolo')
                    ->disk('public')
                    ->directory('municipios/brasoes')
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
                    ->maxLength(20)
                    ->placeholder('(13) 3456-7890'),

                Forms\Components\TextInput::make('site')
                    ->label('Site oficial')
                    ->url()
                    ->maxLength(255)
                    ->placeholder('https://www.peruibe.sp.gov.br'),
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
                    ->disk('public')
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
            'index'  => Pages\ListMunicipios::route('/'),
            'create' => Pages\CreateMunicipio::route('/create'),
            'edit'   => Pages\EditMunicipio::route('/{record}/edit'),
        ];
    }
}
