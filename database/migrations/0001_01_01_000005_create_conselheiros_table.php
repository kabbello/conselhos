<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Conselheiros são os usuários do sistema com perfis específicos
        // Vinculados a users via user_id (nullable para conselheiros sem acesso ao sistema)
        Schema::create('conselheiros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('municipio_id')->constrained('municipios')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('legacy_id')->nullable()->comment('ID original no sistema legado');
            $table->string('nome');
            $table->string('email')->nullable();
            $table->string('telefone', 20)->nullable();
            $table->string('cpf', 14)->nullable();
            $table->string('foto_path')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['municipio_id', 'email']);
            $table->index(['municipio_id', 'ativo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conselheiros');
    }
};
