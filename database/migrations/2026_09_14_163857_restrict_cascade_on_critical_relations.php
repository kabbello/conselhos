<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * A13: troca CASCADE → RESTRICT nas FKs de entidades com valor arquivístico.
     * Impede exclusão acidental de conselhos que tenham composição, reuniões,
     * documentos, processos ou atos normativos vinculados.
     */
    public function up(): void
    {
        // composicao → conselhos
        Schema::table('composicao', function (Blueprint $table) {
            $table->dropForeign(['conselho_id']);
            $table->foreign('conselho_id')->references('id')->on('conselhos')->restrictOnDelete();
        });

        // reunioes → conselhos
        Schema::table('reunioes', function (Blueprint $table) {
            $table->dropForeign(['conselho_id']);
            $table->foreign('conselho_id')->references('id')->on('conselhos')->restrictOnDelete();
        });

        // documentos → conselhos
        Schema::table('documentos', function (Blueprint $table) {
            $table->dropForeign(['conselho_id']);
            $table->foreign('conselho_id')->references('id')->on('conselhos')->restrictOnDelete();
        });

        // legislacao → conselhos
        Schema::table('legislacao', function (Blueprint $table) {
            $table->dropForeign(['conselho_id']);
            $table->foreign('conselho_id')->references('id')->on('conselhos')->restrictOnDelete();
        });

        // processos → conselhos
        Schema::table('processos', function (Blueprint $table) {
            $table->dropForeign(['conselho_id']);
            $table->foreign('conselho_id')->references('id')->on('conselhos')->restrictOnDelete();
        });

        // atos_normativos → conselhos
        Schema::table('atos_normativos', function (Blueprint $table) {
            $table->dropForeign(['conselho_id']);
            $table->foreign('conselho_id')->references('id')->on('conselhos')->restrictOnDelete();
        });

        // comissoes → conselhos
        Schema::table('comissoes', function (Blueprint $table) {
            $table->dropForeign(['conselho_id']);
            $table->foreign('conselho_id')->references('id')->on('conselhos')->restrictOnDelete();
        });

        // comissao_reunioes → comissoes
        Schema::table('comissao_reunioes', function (Blueprint $table) {
            $table->dropForeign(['comissao_id']);
            $table->foreign('comissao_id')->references('id')->on('comissoes')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        // Restaura CASCADE (comportamento original)
        Schema::table('composicao', function (Blueprint $table) {
            $table->dropForeign(['conselho_id']);
            $table->foreign('conselho_id')->references('id')->on('conselhos')->cascadeOnDelete();
        });
        Schema::table('reunioes', function (Blueprint $table) {
            $table->dropForeign(['conselho_id']);
            $table->foreign('conselho_id')->references('id')->on('conselhos')->cascadeOnDelete();
        });
        Schema::table('documentos', function (Blueprint $table) {
            $table->dropForeign(['conselho_id']);
            $table->foreign('conselho_id')->references('id')->on('conselhos')->cascadeOnDelete();
        });
        Schema::table('legislacao', function (Blueprint $table) {
            $table->dropForeign(['conselho_id']);
            $table->foreign('conselho_id')->references('id')->on('conselhos')->cascadeOnDelete();
        });
        Schema::table('processos', function (Blueprint $table) {
            $table->dropForeign(['conselho_id']);
            $table->foreign('conselho_id')->references('id')->on('conselhos')->cascadeOnDelete();
        });
        Schema::table('atos_normativos', function (Blueprint $table) {
            $table->dropForeign(['conselho_id']);
            $table->foreign('conselho_id')->references('id')->on('conselhos')->cascadeOnDelete();
        });
        Schema::table('comissoes', function (Blueprint $table) {
            $table->dropForeign(['conselho_id']);
            $table->foreign('conselho_id')->references('id')->on('conselhos')->cascadeOnDelete();
        });
        Schema::table('comissao_reunioes', function (Blueprint $table) {
            $table->dropForeign(['comissao_id']);
            $table->foreign('comissao_id')->references('id')->on('comissoes')->cascadeOnDelete();
        });
    }
};
