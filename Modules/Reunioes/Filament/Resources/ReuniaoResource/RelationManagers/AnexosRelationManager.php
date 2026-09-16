<?php

namespace Modules\Reunioes\Filament\Resources\ReuniaoResource\RelationManagers;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AnexosRelationManager extends RelationManager
{
    protected static string $relationship = 'anexos';

    protected static ?string $title = 'Anexos';

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('titulo')
                ->label('Título')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),

            Select::make('tipo')
                ->label('Tipo')
                ->options([
                    'CONVOCACAO'  => 'Convocação',
                    'PAUTA'       => 'Pauta',
                    'ATA_APROVADA' => 'Ata Aprovada',
                    'DOCUMENTO'   => 'Documento',
                    'OUTRO'       => 'Outro',
                ])
                ->required()
                ->default('DOCUMENTO'),

            DatePicker::make('data_documento')
                ->label('Data do Documento')
                ->displayFormat('d/m/Y'),

            Toggle::make('publicado')
                ->label('Publicado no Portal')
                ->inline(false)
                ->default(false),

            FileUpload::make('arquivo_path')
                ->label('Arquivo')
                ->disk('r2')
                ->directory('reunioes/anexos')
                ->acceptedFileTypes([
                    'application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'image/png',
                    'image/jpeg',
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ])
                ->maxSize(20480)
                ->columnSpanFull()
                ->helperText('Formatos aceitos: PDF, DOC, DOCX, PNG, JPG, XLS, XLSX. Máximo: 20 MB.'),

            Textarea::make('descricao')
                ->label('Descrição')
                ->rows(2)
                ->columnSpanFull(),
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
                    ->color(fn (string $state) => match ($state) {
                        'CONVOCACAO'   => 'info',
                        'PAUTA'        => 'warning',
                        'ATA_APROVADA' => 'success',
                        'DOCUMENTO'    => 'gray',
                        'OUTRO'        => 'gray',
                        default        => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'CONVOCACAO'   => 'Convocação',
                        'PAUTA'        => 'Pauta',
                        'ATA_APROVADA' => 'Ata Aprovada',
                        'DOCUMENTO'    => 'Documento',
                        'OUTRO'        => 'Outro',
                        default        => $state,
                    }),

                TextColumn::make('data_documento')
                    ->label('Data')
                    ->date('d/m/Y'),

                IconColumn::make('publicado')
                    ->label('Público')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('gray'),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                CreateAction::make()->label('Adicionar Anexo'),
            ])
            ->actions([
                Action::make('download')
                    ->label('Baixar')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(fn ($record) => $record->arquivo_url)
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => (bool) $record->arquivo_path),

                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
