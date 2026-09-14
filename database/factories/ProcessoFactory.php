<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Conselhos\Models\Conselho;
use Modules\Resolucoes\Models\Processo;

class ProcessoFactory extends Factory
{
    protected $model = Processo::class;

    public function definition(): array
    {
        $ano    = now()->year;
        $numero = str_pad(fake()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT) . "/{$ano}";

        return [
            'conselho_id'   => Conselho::factory(),
            'numero'        => $numero,
            'ano'           => $ano,
            'titulo'        => fake()->sentence(6),
            'descricao'     => fake()->paragraph(),
            'tipo'          => fake()->randomElement(['NORMATIVO', 'DELIBERATIVO', 'CONSULTIVO', 'FISCALIZACAO', 'OUTROS']),
            'status'        => 'ABERTO',
            'origem'        => fake()->randomElement(['GOVERNO', 'SOCIEDADE_CIVIL', 'MEMBRO_CONSELHO', 'EXTERNO']),
            'requerente'    => fake()->name(),
            'data_abertura' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
        ];
    }

    public function aprovado(): static
    {
        return $this->state([
            'status'             => 'APROVADO',
            'data_encerramento'  => now()->toDateString(),
        ]);
    }

    public function arquivado(): static
    {
        return $this->state([
            'status'            => 'ARQUIVADO',
            'data_encerramento' => now()->toDateString(),
        ]);
    }
}
