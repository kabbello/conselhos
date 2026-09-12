<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipos_reuniao', function (Blueprint $table) {
            $table->id();
            $table->foreignId('municipio_id')->constrained('municipios')->cascadeOnDelete();
            $table->string('nome'); // Ordinária, Extraordinária, Oficina
            $table->timestamps();
        });

        Schema::create('reunioes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conselho_id')->constrained('conselhos')->cascadeOnDelete();
            $table->foreignId('tipo_id')->nullable()->constrained('tipos_reuniao')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->datetime('data_hora');
            $table->string('local')->nullable();
            $table->longText('pauta')->nullable(); // HTML
            $table->text('observacoes')->nullable();
            $table->enum('status', ['agendada', 'realizada', 'cancelada'])->default('agendada');
            $table->unsignedBigInteger('legacy_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['conselho_id', 'data_hora', 'status']);
        });

        Schema::create('reuniao_presencas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reuniao_id')->constrained('reunioes')->cascadeOnDelete();
            $table->foreignId('composicao_id')->constrained('composicao')->cascadeOnDelete();
            $table->boolean('presente')->default(false);
            $table->timestamps();

            $table->unique(['reuniao_id', 'composicao_id']);
        });

        Schema::create('reuniao_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reuniao_id')->constrained('reunioes')->cascadeOnDelete();
            $table->string('url', 1000);
            $table->string('plataforma')->nullable(); // Zoom, Meet, Jitsi
            $table->timestamps();
        });

        Schema::create('notificacoes_enviadas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reuniao_id')->constrained('reunioes')->cascadeOnDelete();
            $table->string('tipo');    // reminder_7d, reminder_1d, reminder_2h
            $table->string('canal');   // whatsapp, email, push
            $table->string('destino'); // número, e-mail ou endpoint
            $table->boolean('sucesso')->default(true);
            $table->text('resposta')->nullable();
            $table->timestamp('enviado_em')->useCurrent();
            $table->timestamps();

            // RN-011: idempotência — mesmo aviso não enviado duas vezes
            $table->unique(['reuniao_id', 'tipo', 'canal', 'destino'], 'unique_notificacao');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificacoes_enviadas');
        Schema::dropIfExists('reuniao_links');
        Schema::dropIfExists('reuniao_presencas');
        Schema::dropIfExists('reunioes');
        Schema::dropIfExists('tipos_reuniao');
    }
};
