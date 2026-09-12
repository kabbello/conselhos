<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipos_documento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('municipio_id')->constrained('municipios')->cascadeOnDelete();
            $table->string('nome');
            $table->timestamps();
        });

        Schema::create('documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conselho_id')->constrained('conselhos')->cascadeOnDelete();
            $table->foreignId('tipo_documento_id')->nullable()->constrained('tipos_documento')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->string('numero_documento')->nullable();
            $table->date('data_documento')->nullable();
            $table->date('data_publicacao')->nullable();
            $table->boolean('publico')->default(false);
            $table->string('hash_integridade', 64)->nullable()->comment('SHA-256 do arquivo');
            $table->unsignedBigInteger('legacy_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['conselho_id', 'publico']);
        });

        Schema::create('tipos_legislacao', function (Blueprint $table) {
            $table->id();
            $table->foreignId('municipio_id')->constrained('municipios')->cascadeOnDelete();
            $table->string('nome');
            $table->timestamps();
        });

        Schema::create('legislacao', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conselho_id')->constrained('conselhos')->cascadeOnDelete();
            $table->foreignId('tipo_legislacao_id')->nullable()->constrained('tipos_legislacao')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->string('numero')->nullable();
            $table->date('data')->nullable();
            $table->string('hash_integridade', 64)->nullable();
            $table->unsignedBigInteger('legacy_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legislacao');
        Schema::dropIfExists('tipos_legislacao');
        Schema::dropIfExists('documentos');
        Schema::dropIfExists('tipos_documento');
    }
};
