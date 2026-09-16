<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Limpar cache de permissões
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // --- Permissões ---

        $permissions = [
            // Municípios
            'view.municipios', 'create.municipios', 'update.municipios', 'delete.municipios',

            // Conselhos
            'view.conselhos', 'create.conselhos', 'update.conselhos', 'delete.conselhos',

            // Composição
            'view.composicao', 'create.composicao', 'update.composicao',
            'inativar.composicao', 'importar.composicao', 'view-contato.composicao',
            'promover.composicao',

            // Conselheiros
            'view.conselheiros', 'create.conselheiros', 'update.conselheiros',
            'delete.conselheiros', 'view-sensiveis.conselheiros', 'toggle-ativo.conselheiros',

            // Comissões
            'view.comissoes', 'create.comissoes', 'update.comissoes', 'delete.comissoes',

            // Processos
            'view.processos', 'create.processos', 'update.processos', 'delete.processos',

            // Atos Normativos
            'view.atos-normativos', 'create.atos-normativos', 'update.atos-normativos',
            'delete.atos-normativos', 'publicar.atos-normativos',

            // Reuniões
            'view.reunioes', 'create.reunioes', 'update.reunioes', 'cancelar.reunioes',
            'registrar-presenca.reunioes', 'upload-gravacao.reunioes', 'delete-gravacao.reunioes',
            'upload-imagem.reunioes', 'gerenciar-link.reunioes', 'gerar-pdf.reunioes',
            'enviar-comunicacao.reunioes', 'gerenciar-anexos.reunioes', 'aprovar-ata.reunioes',

            // Documentos
            'view.documentos', 'view-privados.documentos', 'create.documentos',
            'update.documentos', 'publicar.documentos', 'delete.documentos',

            // Legislação
            'view.legislacao', 'create.legislacao', 'update.legislacao', 'delete.legislacao',

            // Notificações
            'view.notificacoes', 'reenviar.notificacoes', 'configurar.notificacoes',

            // Comunicações
            'view.comunicacoes', 'create.comunicacoes',

            // Calendário
            'view.calendario', 'create.calendario', 'update.calendario', 'delete.calendario',

            // Configurações
            'view.configuracoes', 'update.configuracoes', 'view-secrets.configuracoes',

            // Auditoria
            'view.auditoria', 'export.auditoria',

            // LGPD
            'view.lgpd-solicitacoes', 'create.lgpd-solicitacoes', 'atender.lgpd-solicitacoes',
            'anonimizar.lgpd', 'view.lgpd-registro', 'update.lgpd-registro',

            // Relatórios
            'view.relatorios', 'export.relatorios', 'view-municipio.relatorios',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // --- Roles ---

        // super_admin: acesso total via Gate::before (sem listar permissões)
        Role::findOrCreate('super_admin');

        // admin_municipal: tudo no município, exceto cross-município e super_admin actions
        $adminMunicipal = Role::findOrCreate('admin_municipal');
        $adminMunicipal->syncPermissions([
            'view.conselhos', 'create.conselhos', 'update.conselhos', 'delete.conselhos',
            'view.composicao', 'create.composicao', 'update.composicao',
            'inativar.composicao', 'importar.composicao', 'view-contato.composicao',
            'promover.composicao',
            'view.conselheiros', 'create.conselheiros', 'update.conselheiros',
            'delete.conselheiros', 'view-sensiveis.conselheiros', 'toggle-ativo.conselheiros',
            'view.comissoes', 'create.comissoes', 'update.comissoes', 'delete.comissoes',
            'view.processos', 'create.processos', 'update.processos', 'delete.processos',
            'view.atos-normativos', 'create.atos-normativos', 'update.atos-normativos',
            'delete.atos-normativos', 'publicar.atos-normativos',
            'view.reunioes', 'create.reunioes', 'update.reunioes', 'cancelar.reunioes',
            'registrar-presenca.reunioes', 'upload-gravacao.reunioes', 'delete-gravacao.reunioes',
            'upload-imagem.reunioes', 'gerenciar-link.reunioes', 'gerar-pdf.reunioes',
            'enviar-comunicacao.reunioes', 'gerenciar-anexos.reunioes', 'aprovar-ata.reunioes',
            'view.documentos', 'view-privados.documentos', 'create.documentos',
            'update.documentos', 'publicar.documentos', 'delete.documentos',
            'view.legislacao', 'create.legislacao', 'update.legislacao', 'delete.legislacao',
            'view.notificacoes', 'reenviar.notificacoes', 'configurar.notificacoes',
            'view.comunicacoes', 'create.comunicacoes',
            'view.calendario', 'create.calendario', 'update.calendario', 'delete.calendario',
            'view.configuracoes', 'update.configuracoes',
            'view.auditoria', 'export.auditoria',
            'view.lgpd-solicitacoes', 'create.lgpd-solicitacoes', 'atender.lgpd-solicitacoes',
            'anonimizar.lgpd', 'view.lgpd-registro', 'update.lgpd-registro',
            'view.relatorios', 'export.relatorios',
        ]);

        // gestor_conselho: gestão do próprio conselho (presidente/secretário)
        $gestorConselho = Role::findOrCreate('gestor_conselho');
        $gestorConselho->syncPermissions([
            'view.conselhos',
            'view.composicao', 'create.composicao', 'update.composicao',
            'inativar.composicao', 'importar.composicao', 'view-contato.composicao',
            'promover.composicao',
            'view.conselheiros', 'create.conselheiros', 'update.conselheiros',
            'view.comissoes', 'create.comissoes', 'update.comissoes',
            'view.processos', 'create.processos', 'update.processos',
            'view.atos-normativos', 'create.atos-normativos', 'update.atos-normativos', 'publicar.atos-normativos',
            'view.reunioes', 'create.reunioes', 'update.reunioes', 'cancelar.reunioes',
            'registrar-presenca.reunioes', 'upload-gravacao.reunioes', 'delete-gravacao.reunioes',
            'upload-imagem.reunioes', 'gerenciar-link.reunioes', 'gerar-pdf.reunioes',
            'enviar-comunicacao.reunioes', 'gerenciar-anexos.reunioes', 'aprovar-ata.reunioes',
            'view.documentos', 'view-privados.documentos', 'create.documentos',
            'update.documentos', 'publicar.documentos', 'delete.documentos',
            'view.legislacao', 'create.legislacao', 'update.legislacao',
            'view.notificacoes', 'reenviar.notificacoes',
            'view.comunicacoes', 'create.comunicacoes',
            'view.calendario', 'create.calendario', 'update.calendario',
            'view.relatorios', 'export.relatorios',
        ]);

        // conselheiro: somente leitura no próprio conselho
        $conselheiro = Role::findOrCreate('conselheiro');
        $conselheiro->syncPermissions([
            'view.conselhos',
            'view.composicao',
            'view.reunioes',
            'view.documentos', 'view-privados.documentos',
            'view.legislacao',
            'view.relatorios',
            'view.calendario',
        ]);

        // operador: escrita sem exclusão ou ações sensíveis
        $operador = Role::findOrCreate('operador');
        $operador->syncPermissions([
            'view.conselhos', 'update.conselhos',
            'view.composicao', 'create.composicao', 'update.composicao', 'view-contato.composicao',
            'view.conselheiros', 'create.conselheiros', 'update.conselheiros',
            'view.comissoes', 'create.comissoes', 'update.comissoes',
            'view.processos', 'create.processos', 'update.processos',
            'view.atos-normativos', 'create.atos-normativos', 'update.atos-normativos',
            'view.reunioes', 'create.reunioes', 'update.reunioes',
            'registrar-presenca.reunioes', 'upload-gravacao.reunioes',
            'upload-imagem.reunioes', 'gerenciar-link.reunioes', 'gerar-pdf.reunioes',
            'enviar-comunicacao.reunioes', 'gerenciar-anexos.reunioes',
            'view.documentos', 'view-privados.documentos', 'create.documentos', 'update.documentos',
            'view.legislacao', 'create.legislacao', 'update.legislacao',
            'view.notificacoes', 'reenviar.notificacoes',
            'view.comunicacoes', 'create.comunicacoes',
            'view.calendario', 'create.calendario', 'update.calendario',
            'view.relatorios', 'export.relatorios',
        ]);

        // encarregado_dados: acesso a dados pessoais e LGPD
        $encarregado = Role::findOrCreate('encarregado_dados');
        $encarregado->syncPermissions([
            'view.composicao', 'view-contato.composicao',
            'view.conselheiros', 'view-sensiveis.conselheiros',
            'view.auditoria', 'export.auditoria',
            'view.lgpd-solicitacoes', 'create.lgpd-solicitacoes', 'atender.lgpd-solicitacoes',
            'anonimizar.lgpd', 'view.lgpd-registro', 'update.lgpd-registro',
            'view.relatorios', 'export.relatorios', 'view-municipio.relatorios',
            'view.notificacoes',
        ]);

        // auditor: somente leitura de logs e relatórios
        $auditor = Role::findOrCreate('auditor');
        $auditor->syncPermissions([
            'view.auditoria', 'export.auditoria',
            'view.relatorios',
        ]);
    }
}
