<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Membros de cada comissão.
 *
 * Regra: membros são escolhidos dentre os conselheiros ativos (composicao_id).
 * composicao_id é nullable para suportar casos onde o membro não tem vínculo
 * formal com a composição (ex.: técnicos convidados sem mandato de conselheiro).
 *
 * Papéis:
 *  COORDENADOR       - preside os trabalhos da comissão (equivale ao "presidente" da comissão)
 *  VICE_COORDENADOR  - substitui o coordenador em suas ausências
 *  RELATOR           - elabora pareceres e relatórios
 *  MEMBRO            - participante ordinário
 *
 * A paridade governo/sociedade civil é garantida via composição do conselho.
 * Esta tabela não replica esse controle — ele já está em composicao.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comissao_membros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comissao_id')->constrained('comissoes')->cascadeOnDelete();

            // Vínculo com o conselheiro via composição do conselho
            $table->foreignId('composicao_id')
                ->nullable()
                ->constrained('composicao')
                ->nullOnDelete();

            // Nome para exibição — preenchido automaticamente de composicao.nome_exibicao
            // mas pode ser sobrescrito (para técnicos sem composicao_id)
            $table->string('nome_exibicao')->nullable();

            $table->enum('papel', [
                'COORDENADOR',
                'VICE_COORDENADOR',
                'RELATOR',
                'MEMBRO',
            ])->default('MEMBRO');

            $table->date('data_entrada')->nullable();
            $table->date('data_saida')->nullable()->comment('null = ativo na comissão');
            $table->boolean('ativo')->default(true);
            $table->text('observacoes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Um conselheiro só pode ter um papel por comissão
            $table->unique(
                ['comissao_id', 'composicao_id'],
                'unique_membro_por_comissao'
            );

            $table->index(['comissao_id', 'ativo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comissao_membros');
    }
};
