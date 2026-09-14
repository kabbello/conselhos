<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Documentos produzidos por comissões.
 *
 * Tipos de documentos comuns em comissões de conselhos:
 *  ATA           - registro das reuniões (complementa ata_texto em comissao_reunioes)
 *  RELATORIO     - relatório de atividades ou de estudo
 *  PARECER       - opinião técnica fundamentada (resultado do trabalho da comissão)
 *  ESTUDO        - estudo técnico elaborado pela comissão
 *  PROPOSTA      - proposta de resolução ou ação encaminhada à plenária
 *  NOTA_TECNICA  - nota técnica sobre assunto específico
 *  OUTRO         - outros documentos
 *
 * Arquivos são gerenciados via Spatie MediaLibrary (coluna arquivo_path é fallback).
 * publicado = true → visível no portal público.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comissao_documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comissao_id')->constrained('comissoes')->cascadeOnDelete();

            // Vinculação opcional a uma reunião específica
            $table->foreignId('comissao_reuniao_id')
                ->nullable()
                ->constrained('comissao_reunioes')
                ->nullOnDelete();

            $table->string('titulo');
            $table->enum('tipo', [
                'ATA',
                'RELATORIO',
                'PARECER',
                'ESTUDO',
                'PROPOSTA',
                'NOTA_TECNICA',
                'OUTRO',
            ])->default('OUTRO');

            $table->text('descricao')->nullable();

            // Arquivo (Spatie MediaLibrary usa sua própria tabela; este campo é fallback/legado)
            $table->string('arquivo_path', 500)->nullable();

            $table->date('data_documento')->nullable();

            // Controle de publicação (LAI / portal de transparência)
            $table->boolean('publicado')->default(false);
            $table->date('data_publicacao')->nullable();

            $table->unsignedBigInteger('legacy_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['comissao_id', 'tipo', 'publicado']);
            $table->index('comissao_reuniao_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comissao_documentos');
    }
};
