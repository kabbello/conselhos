<?php

namespace Modules\Comissoes\Filament\Resources\ComissaoResource\RelationManagers;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DocumentosRelationManager extends RelationManager
{
    protected static string $relationship = 'documentos';

    protected static ?string $title = 'Documentos';

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('titulo')
                ->required()
                ->maxLength(255)
                ->columnSpanFull()
                ->label('Título'),

            Select::make('tipo')
                ->options([
                    'ATA'         => 'Ata',
                    'RELATORIO'   => 'Relatório',
                    'PARECER'     => 'Parecer',
                    'ESTUDO'      => 'Estudo',
                    'PROPOSTA'    => 'Proposta',
                    'NOTA_TECNICA' => 'Nota Técnica',
                    'OUTRO'       => 'Outro',
                ])
                ->required()
                ->label('Tipo'),

            DatePicker::make('data_documento')
                ->label('Data do Documento'),

            Toggle::make('publicado')
                ->default(false)
                ->label('Publicado'),

            DatePicker::make('data_publicacao')
                ->label('Data de Publicação'),

            Textarea::make('descricao')
                ->rows(3)
                ->columnSpanFull()
                ->label('Descrição'),

            FileUpload::make('arquivo_path')
                ->directory('comissao-documentos')
                ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                ->maxSize(10240)
                ->columnSpanFull()
                ->label('Arquivo'),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('titulo')
                    ->label('Título')
                    ->searchable()
                    ->limit(50),

                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'ATA'          => 'Ata',
                        'RELATORIO'    => 'Relatório',
                        'PARECER'      => 'Parecer',
                        'ESTUDO'       => 'Estudo',
                        'PROPOSTA'     => 'Proposta',
                        'NOTA_TECNICA' => 'Nota Técnica',
                        'OUTRO'        => 'Outro',
                        default        => $state,
                    }),

                TextColumn::make('data_documento')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),

                IconColumn::make('publicado')
                    ->label('Pub.')
                    ->boolean(),
            ])
            ->defaultSort('data_documento', 'desc')
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
