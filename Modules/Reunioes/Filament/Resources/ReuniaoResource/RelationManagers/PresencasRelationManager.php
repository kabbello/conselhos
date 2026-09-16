<?php

namespace Modules\Reunioes\Filament\Resources\ReuniaoResource\RelationManagers;

use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\HeaderAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Composicao\Models\Composicao;
use Modules\Reunioes\Models\ReuniaoPresenca;

/**
 * Controle de presença nas reuniões do conselho.
 *
 * O quórum exigido varia por conselho, tipo de matéria e regimento interno.
 * Nenhuma regra de quórum é aplicada aqui — a validação deve ser feita
 * com base no perfil normativo configurado para cada conselho.
 */
class PresencasRelationManager extends RelationManager
{
    protected static string $relationship = 'presencas';

    protected static ?string $title = 'Presença';

    public function form(Form $form): Form
    {
        return $form->schema([
            Toggle::make('presente')
                ->default(false)
                ->label('Presente')
                ->inline(false),
        ]);
    }

    public function table(Table $table): Table
    {
        $reuniao = $this->getOwnerRecord();

        $total    = $reuniao->presencas()->count();
        $presentes = $reuniao->presencas()->where('presente', true)->count();

        return $table
            ->description($total > 0 ? "{$presentes} presentes de {$total} membros" : null)
            ->columns([
                TextColumn::make('composicao.nome_exibicao')
                    ->label('Conselheiro')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('composicao.tipo')
                    ->label('Função')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'PRESIDENTE'      => 'success',
                        'VICE_PRESIDENTE' => 'success',
                        'SECRETARIO'      => 'info',
                        'MEMBRO'          => 'gray',
                        'SUPLENTE'        => 'warning',
                        default           => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'PRESIDENTE'      => 'Presidente',
                        'VICE_PRESIDENTE' => 'Vice-Presidente',
                        'SECRETARIO'      => 'Secretário(a)',
                        'MEMBRO'          => 'Membro',
                        'SUPLENTE'        => 'Suplente',
                        default           => $state ?? '—',
                    }),

                TextColumn::make('composicao.conselheiro.nome')
                    ->label('Nome')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('composicao.entidade.nome')
                    ->label('Entidade')
                    ->limit(30)
                    ->toggleable(),

                IconColumn::make('presente')
                    ->label('Presente')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->headerActions([
                \Filament\Tables\Actions\Action::make('popular_composicao')
                    ->label('Popular da Composição')
                    ->icon('heroicon-o-user-group')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalDescription('Isso criará registros de presença para todos os membros ativos da composição do conselho que ainda não possuem registro nesta reunião.')
                    ->action(function () {
                        $reuniao = $this->getOwnerRecord();
                        $conselhoId = $reuniao->conselho_id;

                        $composicoes = Composicao::where('conselho_id', $conselhoId)
                            ->where('ativo', true)
                            ->pluck('id');

                        $jaExistentes = ReuniaoPresenca::where('reuniao_id', $reuniao->id)
                            ->pluck('composicao_id')
                            ->toArray();

                        $novos = 0;
                        foreach ($composicoes as $composicaoId) {
                            if (! in_array($composicaoId, $jaExistentes)) {
                                ReuniaoPresenca::create([
                                    'reuniao_id'    => $reuniao->id,
                                    'composicao_id' => $composicaoId,
                                    'presente'      => false,
                                ]);
                                $novos++;
                            }
                        }

                        Notification::make()
                            ->title($novos > 0 ? "{$novos} membro(s) adicionado(s)" : 'Nenhum novo membro para adicionar')
                            ->success()
                            ->send();
                    }),

                CreateAction::make()
                    ->label('Adicionar Manualmente')
                    ->form([
                        \Filament\Forms\Components\Select::make('composicao_id')
                            ->label('Conselheiro')
                            ->options(function () {
                                $conselhoId = $this->getOwnerRecord()->conselho_id;
                                return Composicao::where('conselho_id', $conselhoId)
                                    ->where('ativo', true)
                                    ->orderBy('nome_exibicao')
                                    ->pluck('nome_exibicao', 'id')
                                    ->all();
                            })
                            ->required()
                            ->searchable(),

                        Toggle::make('presente')
                            ->default(false)
                            ->label('Presente'),
                    ]),
            ])
            ->actions([
                EditAction::make()
                    ->label('Presença')
                    ->icon('heroicon-o-check-circle'),
                DeleteAction::make()
                    ->label('Remover'),
            ]);
    }
}
