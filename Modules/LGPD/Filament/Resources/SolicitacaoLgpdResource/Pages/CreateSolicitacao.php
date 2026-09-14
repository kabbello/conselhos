<?php

namespace Modules\LGPD\Filament\Resources\SolicitacaoLgpdResource\Pages;

use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\LGPD\Filament\Resources\SolicitacaoLgpdResource;
use Modules\LGPD\Models\SolicitacaoLgpd;

class CreateSolicitacao extends CreateRecord
{
    protected static string $resource = SolicitacaoLgpdResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['municipio_id'] = Filament::getTenant()->id;
        // A04: prazo varia conforme o tipo de direito (LGPD arts. 18/19)
        // Confirmação/acesso: resposta imediata simplificada ou até 15 dias (Art. 19)
        // Outros direitos requerem triagem — prazo de referência interno de 30 dias
        $data['prazo_legal'] = now()->addDays(
            SolicitacaoLgpd::prazoDias($data['tipo'] ?? 'outro')
        )->toDateString();

        return $data;
    }

    /**
     * A12: geração de protocolo e criação do registro em transação única e atômica.
     *
     * Anteriormente o protocolo era gerado em mutateFormDataBeforeCreate(), que abre e
     * fecha sua própria transação antes de o Filament chamar ::create(). Isso liberava
     * o lockForUpdate antes da inserção, permitindo que duas requisições concorrentes
     * obtivessem a mesma sequência.
     *
     * Solução: sobrescrever handleRecordCreation para que gerarProtocoloNovaSequencia()
     * (que usa lockForUpdate) e ::create() ocorram dentro do mesmo DB::transaction(),
     * garantindo que o lock seja mantido até o commit.
     */
    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $data['protocolo'] = SolicitacaoLgpd::gerarProtocoloNovaSequencia($data['municipio_id']);

            return SolicitacaoLgpd::create($data);
        });
    }
}
