<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A07 — Adiciona hash_integridade à tabela atos_normativos.
 *
 * O campo armazena o SHA-256 do arquivo PDF associado ao ato normativo.
 * É calculado automaticamente pelo hook `saved` do model AtoNormativo
 * sempre que arquivo_path muda, garantindo detecção de adulteração.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('atos_normativos', function (Blueprint $table) {
            $table->string('hash_integridade', 64)
                ->nullable()
                ->after('arquivo_path')
                ->comment('SHA-256 do arquivo PDF; calculado automaticamente ao salvar arquivo_path');
        });
    }

    public function down(): void
    {
        Schema::table('atos_normativos', function (Blueprint $table) {
            $table->dropColumn('hash_integridade');
        });
    }
};
