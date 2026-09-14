<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Processos que tramitam nos conselhos municipais.
 *
 * Um processo é a formalização de uma matéria que precisa ser analisada
 * pelo conselho. O fluxo típico (com base na Lei 9.784/99 e regimentos):
 *
 *  ABERTO → EM_ANALISE → (ENCAMINHADO_COMISSAO) → VOTADO → APROVADO / REJEITADO → ARQUIVADO
 *                      ↕
 *              AGUARDANDO_COMPLEMENTACAO (quando falta documentação)
 *
 * Tipos:
 *  NORMATIVO      - proposta de resolução/norma interna
 *  DELIBERATIVO   - deliberação sobre política pública (mais comum)
 *  CONSULTIVO     - consulta/parecer solicitado pelo governo
 *  FISCALIZACAO   - exercício da função fiscalizatória do conselho
 *  OUTROS         - outros assuntos
 *
 * Origens:
 *  GOVERNO         - proposto pelo órgão gestor municipal
 *  SOCIEDADE_CIVIL - proposto por entidade ou cidadão
 *  MEMBRO_CONSELHO - proposto por conselheiro
 *  EXTERNO         - proposto por outro órgão (MP, TCE, etc.)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('processos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conselho_id')->constrained('conselhos')->cascadeOnDelete();

            // Numeração: "001/2025" — gerada automaticamente pelo sistema
            $table->string('numero', 20)->nullable()->comment('Ex.: 001/2025');
            $table->unsignedSmallInteger('ano');

            $table->string('titulo');
            $table->text('descricao')->nullable();

            $table->enum('tipo', [
                'NORMATIVO',
                'DELIBERATIVO',
                'CONSULTIVO',
                'FISCALIZACAO',
                'OUTROS',
            ])->default('DELIBERATIVO');

            $table->enum('status', [
                'ABERTO',
                'EM_ANALISE',
                'AGUARDANDO_COMPLEMENTACAO',
                'ENCAMINHADO_COMISSAO',
                'VOTADO',
                'APROVADO',
                'REJEITADO',
                'ARQUIVADO',
            ])->default('ABERTO');

            $table->enum('origem', [
                'GOVERNO',
                'SOCIEDADE_CIVIL',
                'MEMBRO_CONSELHO',
                'EXTERNO',
            ])->nullable();

            // Quem apresentou a matéria (nome/entidade)
            $table->string('requerente')->nullable();

            // Comissão responsável pela análise (nullable: processos direto em plenária)
            $table->foreignId('comissao_id')
                ->nullable()
                ->constrained('comissoes')
                ->nullOnDelete();

            $table->date('data_abertura')->nullable();
            $table->date('data_encerramento')->nullable();

            $table->text('observacoes')->nullable();
            $table->unsignedBigInteger('legacy_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Número único por conselho/ano
            $table->unique(['conselho_id', 'numero', 'ano'], 'unique_processo_numero_ano');
            $table->index(['conselho_id', 'status', 'ano']);
            $table->index(['conselho_id', 'tipo']);
            $table->index('comissao_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('processos');
    }
};
