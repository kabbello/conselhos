<?php

namespace Modules\Reunioes\Filament\Resources\ReuniaoResource\Pages;

use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Resources\Pages\ListRecords;
use Modules\Reunioes\Filament\Resources\ReuniaoResource;

class ListReunioes extends ListRecords
{
    protected static string $resource = ReuniaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('mensagem_avulsa')
                ->label('Mensagem avulsa')
                ->icon('heroicon-o-envelope')
                ->color('gray')
                ->form([
                    Forms\Components\Select::make('conselho_id')
                        ->label('Conselho (deixe vazio para todos)')
                        ->options(fn () => \Modules\Conselhos\Models\Conselho::where('municipio_id', Filament::getTenant()->id)
                            ->orderBy('nome')->pluck('nome', 'id')->all())
                        ->nullable()
                        ->searchable(),
                    Forms\Components\TextInput::make('assunto')
                        ->label('Assunto')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Textarea::make('corpo')
                        ->label('Mensagem')
                        ->required()
                        ->rows(5),
                ])
                ->action(function (array $data) {
                    $municipioId = Filament::getTenant()->id;

                    $query = \Modules\Composicao\Models\Composicao::query()
                        ->where('ativo', true)
                        ->with('conselheiro')
                        ->whereHas('conselho', fn ($q) => $q->where('municipio_id', $municipioId));

                    if ($data['conselho_id']) {
                        $query->where('conselho_id', $data['conselho_id']);
                    }

                    $membros = $query->get();
                    $enviados = 0;
                    $semEmail = 0;

                    foreach ($membros as $membro) {
                        $conselheiro = $membro->conselheiro;
                        if (!$conselheiro || !$conselheiro->email) { $semEmail++; continue; }

                        try {
                            $conselheiro->notify(new \Modules\Reunioes\Notifications\ConselheiroNotification(
                                $data['assunto'], $data['corpo']
                            ));
                            $enviados++;
                        } catch (\Throwable) {}
                    }

                    $msg = "Enviados: {$enviados} | Sem e-mail: {$semEmail}";
                    \Filament\Notifications\Notification::make()->title('Mensagem enviada')->body($msg)->success()->send();
                }),
        ];
    }
}
