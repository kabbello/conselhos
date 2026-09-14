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
        Schema::table('documentos', function (Blueprint $table) {
            $table->string('arquivo_url', 1000)->nullable()->after('publico')
                ->comment('URL pública do arquivo (legado ou storage)');
        });

        Schema::table('legislacao', function (Blueprint $table) {
            $table->string('arquivo_url', 1000)->nullable()->after('link')
                ->comment('URL do arquivo local (leis_conselhos/)');
        });
    }

    public function down(): void
    {
        Schema::table('documentos', function (Blueprint $table) {
            $table->dropColumn('arquivo_url');
        });
        Schema::table('legislacao', function (Blueprint $table) {
            $table->dropColumn('arquivo_url');
        });
    }
};
