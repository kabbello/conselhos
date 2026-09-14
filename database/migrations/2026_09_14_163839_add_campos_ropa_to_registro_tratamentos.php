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
        Schema::table('registro_tratamentos', function (Blueprint $table) {
            // A05: campos faltantes no ROPA conforme LGPD e guia ANPD
            $table->string('controlador')->nullable()->after('municipio_id')
                ->comment('Nome do controlador responsável pelo tratamento');
            $table->text('operadores')->nullable()->after('controlador')
                ->comment('Operadores/suboperadores que acessam os dados');
            $table->text('sistemas')->nullable()->after('operadores')
                ->comment('Sistemas e bases de dados envolvidos');
            $table->text('origem_dados')->nullable()->after('sistemas')
                ->comment('Fonte/origem dos dados pessoais');
            $table->boolean('dados_sensiveis')->default(false)->after('dados_pessoais')
                ->comment('Contém dados sensíveis (art. 11 LGPD)?');
            $table->string('base_legal_sensiveis')->nullable()->after('base_legal')
                ->comment('Base legal específica para dados sensíveis (art. 11)');
            $table->boolean('envolve_criancas')->default(false)->after('categorias_titulares')
                ->comment('Envolve dados de crianças ou adolescentes?');
            $table->text('metodo_descarte')->nullable()->after('prazo_retencao')
                ->comment('Como os dados são descartados ao fim do prazo');
            $table->string('responsavel_revisao')->nullable()->after('metodo_descarte')
                ->comment('Responsável pela próxima revisão deste registro');
            $table->date('data_proxima_revisao')->nullable()->after('responsavel_revisao');
            $table->text('observacoes_ripd')->nullable()->after('medidas_seguranca')
                ->comment('Referência ao RIPD associado, se aplicável');
        });
    }

    public function down(): void
    {
        Schema::table('registro_tratamentos', function (Blueprint $table) {
            $table->dropColumn([
                'controlador', 'operadores', 'sistemas', 'origem_dados',
                'dados_sensiveis', 'base_legal_sensiveis', 'envolve_criancas',
                'metodo_descarte', 'responsavel_revisao', 'data_proxima_revisao',
                'observacoes_ripd',
            ]);
        });
    }
};
