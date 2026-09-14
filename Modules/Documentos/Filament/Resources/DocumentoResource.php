<?php

namespace Modules\Documentos\Filament\Resources;

use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Documentos\Filament\Resources\DocumentoResource\Pages;
use Modules\Documentos\Models\Documento;

class DocumentoResource extends Resource
{
    protected static ?string $model = Documento::class;
    protected static bool $isScopedToTenant = false;

    protected static ?string $navigationIcon = 'heroicon-o-paper-clip';

    protected static ?string $navigationGroup = 'Documentos';

    protected static ?string $navigationLabel = 'Documentos';

    protected static ?string $modelLabel = 'Documento';

    protected static ?string $pluralModelLabel = 'Documentos';

    protected static ?string $slug = 'documentos';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('conselho', fn (Builder $q) => $q->where('municipio_id', Filament::getTenant()->id));
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('conselho_id')
                ->label('Conselho')
                ->options(fn () => \Modules\Conselhos\Models\Conselho::where('municipio_id', Filament::getTenant()->id)
                    ->where('ativo', true)->orderBy('nome')->pluck('nome', 'id')->all())
                ->required()->searchable(),

            Select::make('tipo_documento_id')
                ->label('Tipo de Documento')
                ->options(fn () => \Modules\Documentos\Models\TipoDocumento::orderBy('nome')->pluck('nome', 'id')->all())
                ->searchable()
                ->nullable(),

            TextInput::make('titulo')
                ->required()
                ->maxLength(255)
                ->columnSpanFull()
                ->label('Título'),

            TextInput::make('numero_documento')
                ->maxLength(100)
                ->label('Número do Documento'),

            DatePicker::make('data_documento')
                ->label('Data do Documento'),

            DatePicker::make('data_publicacao')
                ->label('Data de Publicação (LAI)'),

            Toggle::make('publico')
                ->default(false)
                ->label('Público (visível no portal)'),

            Textarea::make('descricao')
                ->rows(3)
                ->columnSpanFull()
                ->label('Descrição'),

            FileUpload::make('arquivo_url')
                ->disk('local')
                ->directory('documentos')
                ->acceptedFileTypes(['application/pdf', 'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ])
                ->maxSize(20480) // 20 MB
                ->columnSpanFull()
                ->label('Arquivo')
                ->helperText('PDF ou Office, máx. 20 MB. SHA-256 calculado automaticamente.'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('conselho.sigla')
                    ->label('Conselho')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('tipoDocumento.nome')
                    ->label('Tipo')
                    ->badge()
                    ->color('info'),

                TextColumn::make('titulo')
                    ->label('Título')
                    ->searchable()
                    ->limit(60),

                TextColumn::make('numero_documento')
                    ->label('Número')
                    ->searchable(),

                TextColumn::make('data_documento')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),

                IconColumn::make('publico')
                    ->label('Público')
                    ->boolean(),

                TextColumn::make('data_publicacao')
                    ->label('Publicado em')
                    ->date('d/m/Y')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('conselho_id')
                    ->label('Conselho')
                    ->options(fn () => \Modules\Conselhos\Models\Conselho::where('municipio_id', Filament::getTenant()->id)
                        ->orderBy('nome')
                        ->pluck('nome', 'id')
                        ->all()
                    )
                    ->searchable(),

                TernaryFilter::make('publico')
                    ->label('Público'),
            ])
            ->defaultSort('data_documento', 'desc')
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListDocumentos::route('/'),
            'create' => Pages\CreateDocumento::route('/create'),
            'edit'   => Pages\EditDocumento::route('/{record}/edit'),
        ];
    }
}
