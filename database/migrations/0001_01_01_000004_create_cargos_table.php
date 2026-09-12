<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cargos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('municipio_id')->constrained('municipios')->cascadeOnDelete();
            $table->string('nome');
            $table->timestamps();

            $table->unique(['municipio_id', 'nome']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cargos');
    }
};
