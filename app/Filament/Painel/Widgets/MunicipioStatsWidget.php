<?php

namespace App\Filament\Painel\Widgets;

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Modules\Conselhos\Models\Conselho;
use Modules\Composicao\Models\Composicao;
use Modules\Reunioes\Models\Reuniao;

class MunicipioStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $municipio = Filament::getTenant();

        if (! $municipio) {
            return [];
        }

        $conselhos = Conselho::where('municipio_id', $municipio->id);

        $totalConselhos  = (clone $conselhos)->count();
        $conselhosAtivos = (clone $conselhos)->where('ativo', true)->count();

        $conselhoIds = (clone $conselhos)->pluck('id');

        $membrosAtivos = Composicao::whereIn('conselho_id', $conselhoIds)
            ->where('ativo', true)
            ->whereNull('deleted_at')
            ->count();

        $reunioesAgendadas = Reuniao::whereIn('conselho_id', $conselhoIds)
            ->where('status', 'agendada')
            ->where('data_hora', '>=', now())
            ->count();

        $usuarios = User::where('municipio_id', $municipio->id)->count();

        return [
            Stat::make('Conselhos ativos', $conselhosAtivos)
                ->description("{$totalConselhos} no total")
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('primary'),

            Stat::make('Conselheiros ativos', $membrosAtivos)
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),

            Stat::make('Reuniões agendadas', $reunioesAgendadas)
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('warning'),

            Stat::make('Usuários do sistema', $usuarios)
                ->descriptionIcon('heroicon-m-user-circle')
                ->color('info'),
        ];
    }
}
