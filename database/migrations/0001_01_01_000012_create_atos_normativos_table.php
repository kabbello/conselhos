<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Atos normativos produzidos pelos conselhos municipais.
 *
 * Tipos (com base na prática de conselhos municipais brasileiros):
 *
 *  RESOLUCAO     - ato normativo vinculante de maior hierarquia do conselho
 *                  Publicada no Diário Oficial; define diretrizes, normas, aprovações
 *                  Base: Lei 8.142/90 (saúde), LOAS 8.742/93 (assistência), ECA 8.069/90
 *
 *  DELIBERACAO   - decisão deliberativa da plenária; equivale à Resolução em alguns conselhos
 *                  Usada principalmente pelo CNS (Conselho Nacional de Saúde)
 *
 *  RECOMENDACAO  - orientação não vinculante ao poder público
 *                  Sugere ação sem obrigar legalmente
 *
 *  PARECER       - opinião técnica fundamentada, geralmente produzida por comissão
 *                  Caráter consultivo; base para deliberações
 *
 *  MOCAO         - manifestação de posição (aprovação, reconhecimento, repúdio)
 *                  Não vinculante; expressa posição política/ética do conselho
 *
 *  PORTARIA      - ato administrativo interno do conselho (ex.: designação de membros)
 *
 * Numeração: cada tipo tem sua própria sequência por ano (ex.: Resolução 001/2025, Moção 001/2025)
 * O campo numero_completo é calculado: "{numero}/{ano}" formatado com zeros à esquerda.
 *
 * Autovinculação: revoga_id aponta para o ato que ESTA resolução revoga.
 * revogado_por_id é preenchido automaticamente quando outro ato revoga este.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('atos_normativos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conselho_id')->constrained('conselhos')->cascadeOnDelete();

            // Origem: processo que gerou este ato (nullable: atos aprovados diretamente)
            $table->foreignId('processo_id')
                ->nullable()
                ->constrained('processos')
                ->nullOnDelete();

            // Comissão que produziu o parecer/proposta que originou este ato
            $table->foreignId('comissao_id')
                ->nullable()
                ->constrained('comissoes')
                ->nullOnDelete();

            $table->enum('tipo', [
                'RESOLUCAO',
                'DELIBERACAO',
                'RECOMENDACAO',
                'PARECER',
                'MOCAO',
                'PORTARIA',
            ])->default('RESOLUCAO');

            // Numeração sequencial por tipo+ano (ex.: 1 → "001/2025")
            $table->unsignedSmallInteger('numero')->nullable();
            $table->unsignedSmallInteger('ano');

            // Campo calculado para exibição (ex.: "Resolução nº 001/2025")
            // Pode ser gerado via accessor ou stored column
            $table->string('numero_completo', 50)->nullable()
                ->comment('Ex.: 001/2025 — gerado automaticamente');

            $table->string('titulo')->nullable();

            // Ementa: resumo em uma linha do que o ato faz
            $table->text('ementa')->nullable()
                ->comment('Ex.: "Aprova o Plano Municipal de Assistência Social 2025-2027"');

            $table->longText('texto_completo')->nullable();

            $table->enum('status', [
                'RASCUNHO',           // em elaboração, ainda não votado
                'APROVADO',           // votado e aprovado, aguardando publicação
                'VIGENTE',            // publicado e em vigor
                'REVOGADO',           // revogado por outro ato
                'SUSPENSO',           // suspenso por decisão judicial ou do conselho
            ])->default('RASCUNHO');

            // Datas
            $table->date('data_aprovacao')->nullable()->comment('Data da votação em plenária');
            $table->date('data_publicacao')->nullable()->comment('Publicação no Diário Oficial');

            // Publicação / Transparência (LAI)
            $table->boolean('publicado')->default(false);
            $table->string('diario_oficial_referencia')->nullable()
                ->comment('Ex.: "DOM nº 1234, de 15/01/2025"');

            // Arquivo PDF (Spatie MediaLibrary usa sua própria tabela)
            $table->string('arquivo_path', 500)->nullable();

            // Autovinculação para controle de revogações
            $table->foreignId('revoga_id')
                ->nullable()
                ->constrained('atos_normativos')
                ->nullOnDelete()
                ->comment('Qual ato anterior este ato revoga');

            $table->foreignId('revogado_por_id')
                ->nullable()
                ->constrained('atos_normativos')
                ->nullOnDelete()
                ->comment('Preenchido quando outro ato revoga este');

            $table->text('observacoes')->nullable();
            $table->unsignedBigInteger('legacy_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Número único por tipo, conselho e ano
            $table->unique(
                ['conselho_id', 'tipo', 'numero', 'ano'],
                'unique_ato_numero_tipo_ano'
            );
            $table->index(['conselho_id', 'tipo', 'status', 'ano']);
            $table->index(['conselho_id', 'publicado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('atos_normativos');
    }
};
