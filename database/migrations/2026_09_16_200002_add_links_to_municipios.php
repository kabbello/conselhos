<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adiciona URLs específicas de serviços municipais ao município.
 *
 * Motivo: o portal usava $municipio->site (site da prefeitura) como destino
 * de "Portal de Transparência Municipal" e "Ouvidoria Municipal", que são
 * páginas distintas. Campos dedicados evitam esse link genérico.
 *
 * Reversão: remove as colunas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('municipios', function (Blueprint $table) {
            $table->string('link_transparencia', 500)->nullable()->after('site')
                ->comment('URL do Portal de Transparência Municipal (e-SIC, etc.)');
            $table->string('link_ouvidoria', 500)->nullable()->after('link_transparencia')
                ->comment('URL da Ouvidoria Municipal');
        });
    }

    public function down(): void
    {
        Schema::table('municipios', function (Blueprint $table) {
            $table->dropColumn(['link_transparencia', 'link_ouvidoria']);
        });
    }
};
