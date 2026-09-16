<?php

namespace Modules\Composicao\Filament\Resources;

use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Composicao\Filament\Resources\ConselheiroResource\Pages;
use Modules\Composicao\Models\Conselheiro;

class ConselheiroResource extends Resource
{
    protected static ?string $model = Conselheiro::class;
    protected static bool $isScopedToTenant = false;

    protected static ?string $navigationIcon  = 'heroicon-o-user-circle';
    protected static ?string $navigationGroup = 'Conselho';
    protected static ?string $navigationLabel = 'Conselheiros';
    protected static ?string $modelLabel      = 'Conselheiro';
    protected static ?string $pluralModelLabel = 'Conselheiros';
    protected static ?string $slug            = 'conselheiros';
    protected static ?int    $navigationSort  = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('municipio_id', Filament::getTenant()->id);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Foto')->schema([
                Forms\Components\FileUpload::make('foto_path')
                    ->label('Foto do conselheiro')
                    ->image()
                    ->disk('r2')
                    ->directory('conselheiros/fotos')
                    ->visibility('public')
                    ->maxSize(4096)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->helperText('JPG, PNG ou WEBP. Máx. 4 MB. Recomendado: quadrada, mínimo 200×200 px.')
                    ->imagePreviewHeight('160')
                    ->imageEditor()
                    ->imageEditorAspectRatios(['1:1'])
                    ->columnSpanFull(),
            ]),

            Forms\Components\Section::make('Dados pessoais')->schema([
                Forms\Components\TextInput::make('nome')
                    ->label('Nome completo')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(2),

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

                Forms\Components\Toggle::make('ativo')
                    ->label('Ativo')
                    ->default(true)
                    ->inline(false),
            ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('foto_path')
                    ->label('Foto')
                    ->disk('r2')
                    ->height(48)
                    ->width(48)
                    ->extraImgAttributes(['class' => 'rounded-full object-cover'])
                    ->defaultImageUrl(fn () => null),

                Tables\Columns\TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('telefone')
                    ->label('Telefone')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('composicoesAtivas_count')
                    ->label('Mandatos ativos')
                    ->counts('composicoesAtivas')
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('ativo')
                    ->label('Ativo')
                    ->boolean()
                    ->alignCenter(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('ativo')
                    ->label('Situação')
                    ->placeholder('Todos')
                    ->trueLabel('Ativos')
                    ->falseLabel('Inativos'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index'  => Pages\ListConselheiros::route('/'),
            'create' => Pages\CreateConselheiro::route('/create'),
            'edit'   => Pages\EditConselheiro::route('/{record}/edit'),
        ];
    }
}
