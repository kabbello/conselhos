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
            $table->foreignId('conselheiro_id')->constrained('conselheiros')->cascadeOnDelete();
            $table->foreignId('entidade_id')->nullable()->constrained('entidades')->nullOnDelete();
            $table->foreignId('cargo_id')->nullable()->constrained('cargos')->nullOnDelete();
            $table->enum('tipo', [
                'PRESIDENTE',
                'SECRETARIO',
                'VICE_PRESIDENTE',
                'MEMBRO',
                'SUPLENTE',
            ])->default('MEMBRO');
            $table->enum('tipo_representacao', ['TITULAR', 'SUPLENTE'])->default('TITULAR');
            $table->date('data_inicio')->nullable();
            $table->date('data_fim')->nullable();
            $table->boolean('ativo')->default(true);
            $table->text('observacoes')->nullable();
            $table->unsignedBigInteger('legacy_id')->nullable()->comment('ID original em composicao legada');
            $table->timestamps();
            $table->softDeletes();

            // RN-003: um conselheiro não pode aparecer duas vezes no mesmo conselho (ativo)
            $table->unique(['conselho_id', 'conselheiro_id'], 'unique_conselheiro_por_conselho');
            $table->index(['conselho_id', 'tipo', 'ativo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('composicao');
    }
};
