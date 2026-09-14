<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Composicao\Models\Conselheiro;
use Modules\Municipios\Models\Municipio;

class ConselheiroFactory extends Factory
{
    protected $model = Conselheiro::class;

    public function definition(): array
    {
        return [
            'municipio_id' => Municipio::factory(),
            'user_id'      => null,
            'nome'         => fake('pt_BR')->name(),
            'email'        => fake()->unique()->safeEmail(),
            'telefone'     => fake('pt_BR')->phoneNumber(),
            'cpf'          => null,
            'foto_path'    => null,
            'ativo'        => true,
        ];
    }

    public function comLogin(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => \App\Models\User::factory()->create([
                'municipio_id' => $attributes['municipio_id'],
            ])->id,
        ]);
    }
}
