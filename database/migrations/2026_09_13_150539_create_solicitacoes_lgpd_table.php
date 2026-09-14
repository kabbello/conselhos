<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitacoes_lgpd', function (Blueprint $table) {
            $table->id();
            $table->foreignId('municipio_id')->constrained('municipios');
            $table->string('protocolo', 30)->unique();
            $table->string('nome_titular');
            $table->string('email_titular')->nullable();
            $table->string('cpf_titular', 14)->nullable();
            $table->enum('tipo', [
                'acesso',
                'correcao',
                'exclusao',
                'portabilidade',
                'oposicao',
                'revogacao_consentimento',
                'informacao',
                'outro',
            ]);
            $table->text('descricao');
            $table->enum('status', [
                'pendente',
                'em_atendimento',
                'atendida',
                'negada',
                'arquivada',
            ])->default('pendente');
            $table->text('resposta')->nullable();
            $table->date('prazo_legal');
            $table->timestamp('atendida_em')->nullable();
            $table->foreignId('atendida_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['municipio_id', 'status']);
            $table->index('prazo_legal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitacoes_lgpd');
    }
};
