<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reunioes', function (Blueprint $table) {
            $table->unsignedSmallInteger('numero')->nullable()->after('conselho_id');
            $table->longText('ata_texto')->nullable()->after('pauta');
            $table->boolean('ata_aprovada')->default(false)->after('ata_texto');
            $table->timestamp('ata_aprovada_em')->nullable()->after('ata_aprovada');
        });
    }

    public function down(): void
    {
        Schema::table('reunioes', function (Blueprint $table) {
            $table->dropColumn(['numero', 'ata_texto', 'ata_aprovada', 'ata_aprovada_em']);
        });
    }
};
