<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Comissoes\Models\Comissao;
use Modules\Conselhos\Models\Conselho;

class ComissaoFactory extends Factory
{
    protected $model = Comissao::class;

    public function definition(): array
    {
        $tipos = ['PERMANENTE', 'TEMPORARIA', 'ESPECIAL', 'GRUPO_TRABALHO', 'CAMARA_TECNICA'];
        $tipo  = fake()->randomElement($tipos);

        $nomes = [
            'Comissão de Acompanhamento Orçamentário',
            'Comissão de Fiscalização de Políticas Públicas',
            'Câmara Técnica de Habitação',
            'Grupo de Trabalho sobre Saúde Mental',
            'Comissão Especial de Normatização',
            'Comissão de Defesa dos Direitos da Criança',
        ];

        return [
            'conselho_id'      => Conselho::factory(),
            'nome'             => fake()->randomElement($nomes),
            'tipo'             => $tipo,
            'finalidade'       => fake()->sentence(12),
            'ato_criacao'      => 'Resolução nº ' . fake()->numberBetween(1, 50) . '/' . now()->year,
            'data_criacao'     => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
            'status'           => 'ATIVA',
            'ativo'            => true,
        ];
    }

    public function inativa(): static
    {
        return $this->state(['status' => 'INATIVA', 'ativo' => false]);
    }

    public function encerrada(): static
    {
        return $this->state([
            'status'            => 'ENCERRADA',
            'ativo'             => false,
            'data_encerramento' => now()->toDateString(),
        ]);
    }
}
