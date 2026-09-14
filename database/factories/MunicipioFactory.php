<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Municipios\Models\Municipio;

class MunicipioFactory extends Factory
{
    protected $model = Municipio::class;

    public function definition(): array
    {
        $nome = fake('pt_BR')->city();

        return [
            'nome'  => $nome,
            'slug'  => Str::slug($nome) . '-' . fake()->numberBetween(1, 999),
            'uf'    => fake()->stateAbbr(),
            'ativo' => true,
        ];
    }
}
