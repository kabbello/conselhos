<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entidades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('municipio_id')->constrained('municipios')->cascadeOnDelete();
            $table->string('nome');
            $table->string('sigla', 30)->nullable();
            $table->string('tipo')->nullable(); // Pública, Privada, ONG, etc.
            $table->string('cnpj', 18)->nullable();
            $table->timestamps();

            $table->index('municipio_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entidades');
    }
};
