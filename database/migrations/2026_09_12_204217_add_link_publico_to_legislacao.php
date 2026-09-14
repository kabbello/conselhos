<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('legislacao', function (Blueprint $table) {
            $table->string('link', 500)->nullable()->after('numero');
            $table->boolean('publico')->default(true)->after('link');
        });
    }

    public function down(): void
    {
        Schema::table('legislacao', function (Blueprint $table) {
            $table->dropColumn(['link', 'publico']);
        });
    }
};
