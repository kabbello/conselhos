<?php

namespace Modules\Composicao\Filament\Resources\ComposicaoResource\Pages;

use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Modules\Composicao\Filament\Resources\ComposicaoResource;
use Modules\Composicao\Services\ComposicaoService;
use Modules\Conselhos\Models\Conselho;

class ListComposicao extends ListRecords
{
    protected static string $resource = ComposicaoResource::class;

    public ?int $conselhoId = null;

    public function mount(): void
    {
        parent::mount();

        if (request()->has('conselho')) {
            $this->conselhoId = (int) request('conselho');
        }
    }

    protected function getTableQuery(): ?Builder
    {
        $query = parent::getTableQuery();

        if ($this->conselhoId) {
            $query?->where('conselho_id', $this->conselhoId);
        }

        return $query;
    }

    protected function getHeaderActions(): array
    {
        $municipioId = Filament::getTenant()->id;

        return [
            Actions\Action::make('adicionar_membro')
                ->label('Adicionar membro')
                ->icon('heroicon-o-user-plus')
                ->form([
                    Forms\Components\Select::make('conselho_id')
                        ->label('Conselho')
                        ->options(fn () => \Modules\Conselhos\Models\Conselho::where('municipio_id', Filament::getTenant()->id)
                            ->where('ativo', true)->orderBy('nome')->pluck('nome', 'id')->all())
                        ->default($this->conselhoId)
                        ->required()
                        ->searchable(),

                    Forms\Components\Select::make('conselheiro_id')
                        ->label('Conselheiro')
                        ->options(fn () => \Modules\Composicao\Models\Conselheiro::where('municipio_id', Filament::getTenant()->id)
                            ->where('ativo', true)->orderBy('nome')->pluck('nome', 'id')->all())
                        ->nullable()
                        ->searchable()
                        ->helperText('Deixe em branco para membros sem login no sistema'),

                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\Select::make('tipo')
                            ->label('Cargo')
                            ->options([
                                'PRESIDENTE'      => 'Presidente',
                                'VICE_PRESIDENTE' => 'Vice-presidente',
                                'SECRETARIO'      => 'Secretário',
                                'MEMBRO'          => 'Membro',
                                'SUPLENTE'        => 'Suplente',
                            ])
                            ->default('MEMBRO')
                            ->required(),

                        Forms\Components\TextInput::make('nome_exibicao')
                            ->label('Nome para exibição')
                            ->helperText('Auto-preenchido se conselheiro selecionado'),
                    ]),

                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\DatePicker::make('data_nomeacao')
                            ->label('Data da nomeação')
                            ->displayFormat('d/m/Y'),

                        Forms\Components\TextInput::make('decreto_nomeacao')
                            ->label('Decreto de nomeação')
                            ->placeholder('Ex.: Decreto nº 6.731, de 16/12/2025'),
                    ]),
                ])
                ->action(function (array $data, ComposicaoService $service) {
                    try {
                        $conselho = Conselho::findOrFail($data['conselho_id']);
                        $service->adicionar($conselho, $data);
                        Notification::make()->title('Membro adicionado')->success()->send();
                    } catch (\Illuminate\Validation\ValidationException $e) {
                        Notification::make()
                            ->title('Erro ao adicionar membro')
                            ->body(collect($e->errors())->flatten()->first())
                            ->danger()->send();
                    }
                }),
        ];
    }
}
