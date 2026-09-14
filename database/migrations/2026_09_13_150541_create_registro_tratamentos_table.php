<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registro_tratamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('municipio_id')->constrained('municipios');
            $table->string('atividade');
            $table->text('finalidade');
            $table->text('dados_pessoais');
            $table->string('categorias_titulares')->default('Conselheiros');
            $table->enum('base_legal', [
                'consentimento',
                'contrato',
                'obrigacao_legal',
                'exercicio_regular_direitos',
                'protecao_vida',
                'tutela_saude',
                'interesse_legitimo',
                'protecao_credito',
                'politica_publica',
            ]);
            $table->string('prazo_retencao');
            $table->text('destinatarios')->nullable();
            $table->boolean('transferencia_internacional')->default(false);
            $table->text('medidas_seguranca')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->index(['municipio_id', 'ativo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registro_tratamentos');
    }
};
