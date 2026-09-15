<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('municipios', function (Blueprint $table) {
            $table->string('brasao_path', 500)->nullable()->after('logo_path');
            $table->string('codigo_ibge', 10)->nullable()->after('uf');
            $table->string('email', 255)->nullable()->after('brasao_path');
            $table->string('telefone', 20)->nullable()->after('email');
            $table->string('site', 255)->nullable()->after('telefone');
            $table->string('endereco', 500)->nullable()->after('site');
            $table->string('cep', 9)->nullable()->after('endereco');
            $table->string('prefeito', 255)->nullable()->after('cep');
            $table->unsignedInteger('populacao')->nullable()->after('prefeito');
            $table->decimal('area_km2', 10, 2)->nullable()->after('populacao');
            $table->string('cor_primaria', 7)->nullable()->default('#1e40af')->after('area_km2');
            $table->text('descricao')->nullable()->after('cor_primaria');
        });
    }

    public function down(): void
    {
        Schema::table('municipios', function (Blueprint $table) {
            $table->dropColumn([
                'brasao_path', 'codigo_ibge', 'email', 'telefone', 'site',
                'endereco', 'cep', 'prefeito', 'populacao', 'area_km2',
                'cor_primaria', 'descricao',
            ]);
        });
    }
};
