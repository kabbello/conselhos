<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conselhos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('municipio_id')->constrained('municipios')->cascadeOnDelete();
            $table->string('nome');
            $table->string('slug');
            $table->string('sigla', 20)->nullable();
            $table->string('tipo')->nullable(); // Municipal, Estadual, etc.
            $table->text('descricao')->nullable();
            $table->string('email')->nullable();
            $table->string('telefone', 20)->nullable();
            $table->string('endereco')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->unique(['municipio_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conselhos');
    }
};
