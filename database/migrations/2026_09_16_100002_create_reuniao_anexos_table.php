<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reuniao_anexos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reuniao_id')->constrained('reunioes')->cascadeOnDelete();
            $table->string('titulo', 255);
            $table->string('tipo', 50)->default('DOCUMENTO');
            $table->text('descricao')->nullable();
            $table->string('arquivo_path', 500)->nullable();
            $table->boolean('publicado')->default(false);
            $table->date('data_documento')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['reuniao_id', 'tipo', 'publicado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reuniao_anexos');
    }
};
