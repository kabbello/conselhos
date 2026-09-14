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
        Schema::table('conselhos', function (Blueprint $table) {
            $table->string('logo_url', 500)->nullable()->after('legacy_id');
        });
    }

    public function down(): void
    {
        Schema::table('conselhos', function (Blueprint $table) {
            $table->dropColumn('logo_url');
        });
    }
};
