<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // conselheiros = cadastro de pessoas nomeadas para conselhos
        // user_id = conta de login; nullable porque nem todo nomeado tem acesso ao sistema
        Schema::create('conselheiros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('municipio_id')->constrained('municipios')->cascadeOnDelete();

            // Vínculo com conta de login (nullable: nomeados sem acesso ao sistema)
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->unsignedBigInteger('legacy_id')->nullable()->comment('id_conselheiro no sistema legado');
            $table->string('nome');
            $table->string('email')->nullable();
            $table->string('telefone', 20)->nullable();
            $table->string('cpf', 14)->nullable();
            $table->string('foto_path')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Email único por município, mas apenas para emails reais
            // Emails placeholder (sem-email+uuid@conselho.local) não conflitam pois o uuid é único
            $table->unique(['municipio_id', 'email'], 'unique_conselheiro_email_municipio');
            $table->index(['municipio_id', 'ativo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conselheiros');
    }
};
