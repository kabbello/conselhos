<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('composicao', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conselho_id')->constrained('conselhos')->cascadeOnDelete();

            // Nullable: ~40% dos registros legados não têm vínculo com login
            // (conselheiros nomeados mas sem conta no sistema)
            $table->foreignId('conselheiro_id')->nullable()->constrained('conselheiros')->nullOnDelete();

            $table->foreignId('entidade_id')->nullable()->constrained('entidades')->nullOnDelete();
            $table->foreignId('cargo_id')->nullable()->constrained('cargos')->nullOnDelete();

            // Papel no conselho — mistura cargo na mesa diretora e tipo de assento
            // (igual ao legado: PRESIDENTE, VICE_PRESIDENTE, SECRETARIO, MEMBRO, SUPLENTE)
            // SUPLENTE = suplente da entidade representada (não suplente de outra pessoa)
            $table->enum('tipo', [
                'PRESIDENTE',
                'VICE_PRESIDENTE',
                'SECRETARIO',
                'MEMBRO',
                'SUPLENTE',
            ])->default('MEMBRO');

            // Nome/email/telefone desnormalizados para exibição pública
            // mesmo quando conselheiro_id é NULL (nomeados sem login)
            $table->string('nome_exibicao')->nullable()->comment('Nome para exibição pública; auto-preenchido de conselheiros.nome');
            $table->string('email_exibicao')->nullable()->comment('Auto-preenchido; não é o login');
            $table->string('telefone_contato', 20)->nullable();

            // Mandato
            $table->date('data_nomeacao')->nullable()->comment('Data do decreto de nomeação');
            $table->date('data_fim')->nullable()->comment('Fim do mandato; null = mandato ativo');
            $table->string('decreto_nomeacao')->nullable()->comment('Ex.: Decreto nº 6.731, de 16/12/2025');

            $table->boolean('ativo')->default(true);
            $table->text('observacoes')->nullable();
            $table->unsignedBigInteger('legacy_id')->nullable()->comment('id_membro do sistema legado');
            $table->timestamps();
            $table->softDeletes();

            // Uma pessoa pode ser MEMBRO e SUPLENTE no mesmo conselho (caso real no legado)
            // A unique inclui tipo para permitir esse cenário
            $table->unique(
                ['conselho_id', 'conselheiro_id', 'tipo'],
                'unique_conselheiro_tipo_por_conselho'
            );

            $table->index(['conselho_id', 'tipo', 'ativo']);
            $table->index('conselheiro_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('composicao');
    }
};
