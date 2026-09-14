<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reuniões de comissões.
 *
 * Base legal:
 *  - Lei 12.527/2011 (LAI): atas devem ser publicadas em até 10 dias úteis
 *  - Regimentos internos: definem tipos de reunião (ordinária/extraordinária)
 *
 * O campo ata_texto armazena o texto completo da ata diretamente.
 * Documentos anexos ficam em comissao_documentos com tipo = 'ATA'.
 * ata_aprovada indica se a ata foi aprovada na reunião seguinte (prática comum).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comissao_reunioes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comissao_id')->constrained('comissoes')->cascadeOnDelete();

            // Número sequencial dentro da comissão (ex.: 1ª Reunião, 2ª Reunião)
            $table->unsignedSmallInteger('numero')->nullable();

            $table->enum('tipo', ['ORDINARIA', 'EXTRAORDINARIA'])->default('ORDINARIA');

            $table->dateTime('data_hora')->nullable();
            $table->string('local')->nullable();

            // Pauta / Ordem do Dia
            $table->text('pauta')->nullable()->comment('Ordem do dia — pontos pautados antes da reunião');

            $table->enum('status', [
                'AGENDADA',
                'REALIZADA',
                'CANCELADA',
            ])->default('AGENDADA');

            // Ata
            $table->longText('ata_texto')->nullable()->comment('Texto completo da ata da reunião');
            $table->boolean('ata_aprovada')->default(false)->comment('Aprovada na reunião seguinte (prática comum)');
            $table->date('data_aprovacao_ata')->nullable();
            $table->date('data_publicacao_ata')->nullable()->comment('Publicação no portal (LAI: até 10 dias úteis)');

            $table->text('observacoes')->nullable();
            $table->unsignedBigInteger('legacy_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Número único por comissão
            $table->unique(['comissao_id', 'numero'], 'unique_numero_reuniao_comissao');
            $table->index(['comissao_id', 'status']);
            $table->index(['comissao_id', 'data_hora']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comissao_reunioes');
    }
};
