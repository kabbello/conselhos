<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('importacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conselho_id')->constrained('conselhos')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('hash_arquivo', 64)->comment('SHA-256 do arquivo importado');
            $table->string('tipo_documento')->nullable(); // Decreto, Lei, Portaria, Resolução
            $table->string('numero_documento')->nullable();
            $table->date('data_documento')->nullable();
            $table->json('preview_data')->nullable()->comment('JSON com resultado do preview');
            $table->enum('status', ['pendente', 'aplicado', 'cancelado'])->default('pendente');
            $table->timestamp('aplicado_em')->nullable();
            $table->unsignedBigInteger('legacy_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('importacoes');
    }
};
