<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Comissões de um conselho municipal.
 *
 * Base legal:
 *  - Lei 8.742/1993 (LOAS) - comissões temáticas do CMAS
 *  - Lei 8.142/1990 - comissões do Conselho de Saúde
 *  - Lei 8.069/1990 (ECA) - comissões do CMDCA
 *  - Resolução CNAS 100/2023 - "câmaras técnicas" equivalentes a comissões
 *
 * Tipos:
 *  PERMANENTE     - acompanha área temática continuamente (ex.: Comissão de Finanças)
 *  TEMPORARIA     - criada para finalidade específica, extinta ao concluir
 *  ESPECIAL       - para matérias que não se enquadram nas permanentes
 *  GRUPO_TRABALHO - estrutura menos formal, definida no regimento interno
 *  CAMARA_TECNICA - nomenclatura adotada por conselhos de assistência social (CNAS 100/2023)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comissoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conselho_id')->constrained('conselhos')->cascadeOnDelete();

            $table->string('nome');
            $table->enum('tipo', [
                'PERMANENTE',
                'TEMPORARIA',
                'ESPECIAL',
                'GRUPO_TRABALHO',
                'CAMARA_TECNICA',
            ])->default('TEMPORARIA');

            // Objeto/finalidade: o que a comissão deve estudar ou propor
            $table->text('finalidade')->nullable();

            // Ato que criou a comissão (ex.: "Resolução nº 02/2025")
            $table->string('ato_criacao')->nullable();

            // Mandato
            $table->date('data_criacao')->nullable();
            $table->date('data_encerramento')->nullable()->comment('null = ativa');

            $table->enum('status', ['ATIVA', 'ENCERRADA', 'SUSPENSA'])->default('ATIVA');
            $table->boolean('ativo')->default(true);
            $table->text('observacoes')->nullable();
            $table->unsignedBigInteger('legacy_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['conselho_id', 'status', 'ativo']);
            $table->index(['conselho_id', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comissoes');
    }
};
