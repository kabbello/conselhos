<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Conselhos\Models\Conselho;
use Modules\Municipios\Models\Municipio;

class ConselhoFactory extends Factory
{
    protected $model = Conselho::class;

    public function definition(): array
    {
        $nomes = [
            'Conselho Municipal de Saúde',
            'Conselho Municipal de Educação',
            'Conselho Municipal de Assistência Social',
            'Conselho Municipal dos Direitos da Criança e do Adolescente',
            'Conselho Municipal de Habitação',
            'Conselho Municipal de Meio Ambiente',
        ];

        $nome = fake()->randomElement($nomes);
        $slug = Str::slug($nome) . '-' . fake()->numberBetween(1, 999);

        return [
            'municipio_id' => Municipio::factory(),
            'nome'         => $nome,
            'slug'         => $slug,
            'sigla'        => strtoupper(fake()->lexify('C???')),
            'tipo'         => fake()->randomElement(['DELIBERATIVO', 'CONSULTIVO', 'FISCALIZADOR']),
            'ativo'        => true,
        ];
    }
}
