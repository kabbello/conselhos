<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comissao_reuniao_presencas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comissao_reuniao_id')->constrained('comissao_reunioes')->cascadeOnDelete();
            $table->foreignId('comissao_membro_id')->constrained('comissao_membros')->cascadeOnDelete();
            $table->boolean('presente')->default(false);
            $table->timestamps();

            $table->unique(['comissao_reuniao_id', 'comissao_membro_id'], 'crp_reuniao_membro_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comissao_reuniao_presencas');
    }
};
