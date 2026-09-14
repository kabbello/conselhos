<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Vincula usuários com papel gestor_conselho a conselhos específicos.
 *
 * O papel gestor_conselho é amplo (cobre vários conselhos no município)
 * mas deve ser limitado ao(s) conselho(s) atribuídos. Sem este vínculo,
 * qualquer usuário com o papel acessa todos os conselhos do município.
 *
 * Quem atribui: o PRESIDENTE ativo do conselho (ou admin_municipal).
 * Não é necessário que o gestor seja membro da composição.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_conselho_gestores', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('conselho_id')
                ->constrained('conselhos')
                ->cascadeOnDelete();

            // Quem fez a atribuição (presidente ou admin_municipal)
            $table->foreignId('atribuido_por')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('atribuido_em')->useCurrent();
            $table->timestamp('revogado_em')->nullable();

            // Um usuário só pode ser gestor ativo de um conselho uma vez
            $table->unique(['user_id', 'conselho_id']);

            $table->index(['conselho_id', 'revogado_em']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_conselho_gestores');
    }
};
