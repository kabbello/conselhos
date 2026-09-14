<?php

namespace Modules\Reunioes\Filament\Resources;

use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Reunioes\Filament\Resources\ReuniaoResource\Pages;
use Modules\Reunioes\Models\Reuniao;

class ReuniaoResource extends Resource
{
    protected static ?string $model = Reuniao::class;
    protected static bool $isScopedToTenant = false;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Reuniões';

    protected static ?string $navigationLabel = 'Reuniões do Conselho';

    protected static ?string $modelLabel = 'Reunião';

    protected static ?string $pluralModelLabel = 'Reuniões';

    protected static ?string $slug = 'reunioes';

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

            Select::make('tipo_id')
                ->label('Tipo de Reunião')
                ->options(fn () => \Modules\Reunioes\Models\TipoReuniao::orderBy('nome')->pluck('nome', 'id')->all())
                ->searchable()->nullable(),

            DateTimePicker::make('data_hora')
                ->required()
                ->seconds(false)
                ->label('Data e Hora'),

            TextInput::make('local')
                ->maxLength(255)
                ->label('Local'),

            Select::make('status')
                ->options([
                    'agendada'  => 'Agendada',
                    'realizada' => 'Realizada',
                    'cancelada' => 'Cancelada',
                ])
                ->required()
                ->default('agendada')
                ->label('Status'),

            Textarea::make('observacoes')
                ->rows(2)
                ->columnSpanFull()
                ->label('Observações'),

            RichEditor::make('pauta')
                ->columnSpanFull()
                ->label('Pauta / Ordem do Dia')
                ->helperText('Registre os pontos de pauta antes da reunião'),
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

                TextColumn::make('tipoReuniao.nome')
                    ->label('Tipo')
                    ->badge()
                    ->color('info'),

                TextColumn::make('data_hora')
                    ->label('Data/Hora')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('local')
                    ->label('Local')
                    ->limit(40),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'agendada'  => 'warning',
                        'realizada' => 'success',
                        'cancelada' => 'danger',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'agendada'  => 'Agendada',
                        'realizada' => 'Realizada',
                        'cancelada' => 'Cancelada',
                        default     => $state,
                    }),

                TextColumn::make('presencas_count')
                    ->label('Presentes')
                    ->counts('presencas')
                    ->alignCenter()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'agendada'  => 'Agendada',
                        'realizada' => 'Realizada',
                        'cancelada' => 'Cancelada',
                    ])
                    ->label('Status'),

                SelectFilter::make('conselho_id')
                    ->label('Conselho')
                    ->options(fn () => \Modules\Conselhos\Models\Conselho::where('municipio_id', Filament::getTenant()->id)
                        ->orderBy('nome')
                        ->pluck('nome', 'id')
                        ->all()
                    )
                    ->searchable(),
            ])
            ->defaultSort('data_hora', 'desc')
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
                Tables\Actions\Action::make('notificar')
                    ->label('Notificar')
                    ->icon('heroicon-o-bell')
                    ->color('info')
                    ->authorize(fn (Reuniao $record) => auth()->user()->can('notificar', $record))
                    ->form([
                        Forms\Components\TextInput::make('assunto')
                            ->label('Assunto')
                            ->required()
                            ->default(fn (Reuniao $r) => 'Reunião agendada: ' . ($r->conselho->nome ?? ''))
                            ->maxLength(255),
                        Forms\Components\Textarea::make('corpo')
                            ->label('Mensagem')
                            ->required()
                            ->rows(4)
                            ->default('Lembramos que há uma reunião agendada. Por favor, confirme sua presença.'),
                    ])
                    ->action(function (Reuniao $record, array $data) {
                        $resultado = \Modules\Reunioes\Observers\ReuniaoObserver::enviarParaComposicao(
                            $record, $data['assunto'], $data['corpo']
                        );
                        $msg = "Enviados: {$resultado['enviados']} | Sem e-mail: {$resultado['sem_email']} | Erros: {$resultado['erros']}";
                        Notification::make()->title('Notificação enviada')->body($msg)->success()->send();
                    }),
            ]);
    }

    public static function getRelationManagers(): array
    {
        return [
            ReuniaoResource\RelationManagers\PresencasRelationManager::class,
            ReuniaoResource\RelationManagers\LinksRelationManager::class,
            ReuniaoResource\RelationManagers\NotificacoesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListReunioes::route('/'),
            'create' => Pages\CreateReuniao::route('/create'),
            'edit'   => Pages\EditReuniao::route('/{record}/edit'),
        ];
    }
}
