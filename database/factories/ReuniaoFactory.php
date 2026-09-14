<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Conselhos\Models\Conselho;
use Modules\Reunioes\Models\Reuniao;

class ReuniaoFactory extends Factory
{
    protected $model = Reuniao::class;

    public function definition(): array
    {
        return [
            'conselho_id' => Conselho::factory(),
            'data_hora'   => fake()->dateTimeBetween('-6 months', '+2 months'),
            'local'       => fake()->randomElement([
                'Sede da Prefeitura — Sala de Reuniões',
                'Câmara Municipal',
                'Centro de Convenções Municipal',
                'Online (Google Meet)',
            ]),
            'status'      => 'agendada',
            'pauta'       => fake()->sentences(3, true),
        ];
    }

    public function realizada(): static
    {
        return $this->state([
            'status'   => 'realizada',
            'data_hora' => fake()->dateTimeBetween('-6 months', '-1 day'),
        ]);
    }

    public function cancelada(): static
    {
        return $this->state(['status' => 'cancelada']);
    }
}
