<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Conselhos\Models\Conselho;
use Modules\Resolucoes\Models\AtoNormativo;

class AtoNormativoFactory extends Factory
{
    protected $model = AtoNormativo::class;

    public function definition(): array
    {
        $ano    = now()->year;
        $numero = fake()->numberBetween(1, 99);
        $tipo   = fake()->randomElement(['RESOLUCAO', 'DELIBERACAO', 'RECOMENDACAO', 'PARECER', 'MOCAO', 'PORTARIA']);

        return [
            'conselho_id'    => Conselho::factory(),
            'tipo'           => $tipo,
            'numero'         => $numero,
            'ano'            => $ano,
            'numero_completo' => str_pad($numero, 3, '0', STR_PAD_LEFT) . "/{$ano}",
            'titulo'         => fake()->sentence(8),
            'ementa'         => fake()->sentence(15),
            'texto_completo' => fake()->paragraphs(3, true),
            'status'         => 'RASCUNHO',
            'publicado'      => false,
        ];
    }

    public function vigente(): static
    {
        return $this->state([
            'status'          => 'VIGENTE',
            'publicado'       => true,
            'data_aprovacao'  => fake()->dateTimeBetween('-1 year', '-1 month')->format('Y-m-d'),
            'data_publicacao' => fake()->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
        ]);
    }

    public function aprovado(): static
    {
        return $this->state([
            'status'         => 'APROVADO',
            'data_aprovacao' => now()->toDateString(),
        ]);
    }

    public function revogado(): static
    {
        return $this->state([
            'status'    => 'REVOGADO',
            'publicado' => true,
        ]);
    }
}
