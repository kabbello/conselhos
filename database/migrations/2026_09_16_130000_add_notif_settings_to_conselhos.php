<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Configurações de notificação automática por conselho.
 *
 * O gestor do conselho pode habilitar/desabilitar e-mail e WhatsApp
 * independentemente. WhatsApp requer que a Evolution API esteja configurada
 * no servidor (env WHATSAPP_API_URL / WHATSAPP_API_KEY / WHATSAPP_INSTANCE).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conselhos', function (Blueprint $table) {
            $table->boolean('notif_email_ativo')
                ->default(true)
                ->after('ativo')
                ->comment('Envia e-mail à composição ao criar/alterar reunião');

            $table->boolean('notif_whatsapp_ativo')
                ->default(false)
                ->after('notif_email_ativo')
                ->comment('Envia WhatsApp (Evolution API) ao criar/alterar reunião');
        });
    }

    public function down(): void
    {
        Schema::table('conselhos', function (Blueprint $table) {
            $table->dropColumn(['notif_email_ativo', 'notif_whatsapp_ativo']);
        });
    }
};
