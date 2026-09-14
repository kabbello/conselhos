<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reuniao_links', function (Blueprint $table) {
            // C03: somente links marcados como audiência pública são exibidos no portal
            $table->boolean('audiencia_publica')->default(false)->after('plataforma')
                ->comment('Exibir este link no portal público');
        });
    }

    public function down(): void
    {
        Schema::table('reuniao_links', function (Blueprint $table) {
            $table->dropColumn('audiencia_publica');
        });
    }
};
