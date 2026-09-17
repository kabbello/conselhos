<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Amplia colunas de telefone de varchar(20) para varchar(50).
 *
 * Motivo: telefones com ramal como "1343511000 ramal 52527" (22 chars)
 * e "(13) 3451-1000, Ramal 5257" (26 chars) eram silenciosamente truncados
 * pelo MySQL ao ultrapassar o limite de 20 caracteres, corrompendo dados.
 *
 * Afetados confirmados (Peruíbe):
 *   CMDCA: "(13) 3451-1000, Rama"    → "(13) 3451-1000, Ramal 5257"
 *   CONDEF: "133451-1000 ramal 52"   → "133451-1000 ramal 5258"
 *   CMAS:   "1343511000 ramal 525"   → "1343511000 ramal 52527"
 *
 * Reversão: reduz de volta a 20 — dados além de 20 chars serão re-truncados.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conselhos', function (Blueprint $table) {
            $table->string('telefone', 50)->nullable()->change();
        });

        Schema::table('composicao', function (Blueprint $table) {
            $table->string('telefone_contato', 50)->nullable()->change();
        });

        Schema::table('conselheiros', function (Blueprint $table) {
            $table->string('telefone', 50)->nullable()->change();
        });

        Schema::table('municipios', function (Blueprint $table) {
            $table->string('telefone', 50)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('conselhos', function (Blueprint $table) {
            $table->string('telefone', 20)->nullable()->change();
        });
        Schema::table('composicao', function (Blueprint $table) {
            $table->string('telefone_contato', 20)->nullable()->change();
        });
        Schema::table('conselheiros', function (Blueprint $table) {
            $table->string('telefone', 20)->nullable()->change();
        });
        Schema::table('municipios', function (Blueprint $table) {
            $table->string('telefone', 20)->nullable()->change();
        });
    }
};
