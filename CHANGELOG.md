# Changelog

Todas as mudanças notáveis do projeto são documentadas aqui.
Formato baseado em [Keep a Changelog](https://keepachangelog.com/pt-BR/1.0.0/).

---

## [0.3.0] — 2026-09-17

### Adicionado

#### Central de Ajuda (`/ajuda`)
- `AjudaController`: lê arquivos Markdown de `resources/help/`, faz parse de frontmatter YAML e renderiza HTML sem dependência externa
- Busca client-side indexando todos os tópicos automaticamente
- 3 views: `ajuda/layout.blade.php` (barra de busca global), `ajuda/index.blade.php` (cards por seção), `ajuda/topico.blade.php` (artigo com sidebar + navegação anterior/próximo)
- 18 tópicos de ajuda em `resources/help/`:
  - **Portal Público**: documentos, reuniões, reportar erro
  - **Conselheiro**: primeiro acesso, o que posso fazer, minha senha
  - **Gestão do Conselho**: composição, reuniões, documentos, atos normativos, comissões, processos, notificações
  - **Administração Municipal**: usuários, auditoria, configurações
  - **LGPD**: solicitações de titulares, registro de tratamento
- Rotas `/ajuda` e `/ajuda/{secao}/{topico}` adicionadas a `routes/web.php`
- Link "Central de Ajuda" no rodapé do portal público
- Link "Central de Ajuda" no menu lateral do painel (grupo Suporte, abre em nova aba)

#### Formulário "Reportar erro" no portal
- Modal nativo `<dialog>` com form (nome opcional, e-mail opcional, descrição obrigatória)
- Envio via Fetch API sem recarregar a página; feedback inline de sucesso/erro
- `ReportarErro` Mailable + view `mail/reportar-erro.blade.php`
- Rota `POST /{municipio}/reportar-erro` encaminha para `kabbello@hotmail.com`
- Botão na barra de acessibilidade substituído de `mailto:` para abertura do modal

#### Melhorias no portal público
- Descrição do conselho com `nl2br` para preservar quebras de linha
- Validação de e-mail com `filter_var(FILTER_VALIDATE_EMAIL)` antes de exibir
- Pauta expandível via `<details>`/`<summary>` nas listas de reuniões
- Busca client-side de conselhos com filtro por tipo na página inicial
- Links de Transparência e Ouvidoria municipais configuráveis (`link_transparencia`, `link_ouvidoria`) no cadastro do município

### Corrigido
- E-mails com domínio `@*.perui.be` (inexistentes) anulados via comando `dados:corrigir-legado-peruibe`
- Rodapé do portal: coluna "Contato e Suporte" removida; "Reportar erro" movido para faixa central destacada
- `canAccessPanel('painel')` corrigido para permitir acesso de `super_admin` sem `municipio_id`
- `make deploy` agora usa `git fetch + reset --hard` e `docker service update --force` — garante rollover da nova imagem no Swarm e evita conflitos de git

---

## [0.2.1] — 2026-09-16

### Corrigido

#### Auditoria e reconciliação de documentos (Peruíbe)
- **Causa raiz identificada**: o portal legado (PHP Maker) exibia todos os `arquivos` publicamente, ignorando o campo `Publico` (DEFAULT 0 no schema MariaDB). O comando de importação tratava `Publico=0` como privado → documentos importados com `publico=false`.
- **Discrepância antes**: Conselho da Cidade 73→10, CAE 52→1, COMBEM 71→16, FUNDEB 29→13, CMAS 3→0, Saúde 31→4
- **Fix aplicado**: 214 documentos publicados via `documentos:reconciliar-legado --publicar`
- **Pós-fix**: todos os 6 conselhos com parity total (1 doc do Conselho da Cidade ausente no DB pode ser recuperado com `--importar-faltantes` após importar o dump legado)
- `ImportLegacyPeruibe`: corrigida lógica de `publico` para documentos e legislação (agora `true` por padrão, como era no portal antigo)
- Novo comando `documentos:reconciliar-legado` com modos `--dry-run`, `--publicar`, `--importar-faltantes`, `--conselhos=`

---

## [0.2.0] — 2026-09-16

### Adicionado

#### Notificações automáticas de reunião
- `EnviarConvocacaoJob`: job em fila com idempotência via tabela `notificacoes_enviadas`
- `ConvocacaoMail` + view `mail/convocacao.blade.php`: template HTML responsivo fiel ao sistema legado
- `EvolutionApiService`: integração com Evolution API para envio de WhatsApp
- `ReuniaoObserver` reativado: dispara job em `created` (tipo `convocacao_criada`) e `updated` (tipo `upd_{hash}`) quando `data_hora`, `local`, `pauta`, `tipo_id` ou `observacoes` mudam
- Configuração por conselho: toggles `notif_email_ativo` (padrão ativo) e `notif_whatsapp_ativo` (padrão inativo) no formulário de edição
- Migration `add_notif_settings_to_conselhos`
- Entrada `evolution` em `config/services.php` lendo `WHATSAPP_API_URL`, `WHATSAPP_API_KEY`, `WHATSAPP_INSTANCE`

#### Logotipo do conselho
- Campo `FileUpload` para R2 no formulário de Conselho (seção Identificação)
- Accessor `logoUrl()` no model `Conselho` converte path R2 → URL pública
- Compatibilidade com valores legados que já sejam URLs completas
- `logo_url` adicionado ao `$fillable` do model

#### Gestor natural (presidente = gestor do conselho)
- `ComposicaoObserver`: ao salvar composição com `tipo=PRESIDENTE` + `ativo=true` + conselheiro com `user_id`, faz upsert em `user_conselho_gestores` e atribui role `gestor_conselho`; revoga ao remover/inativar
- Registrado em `AppServiceProvider`

#### E-mail via cPanel
- Configuração de SMTP via cPanel (`e9012.whmserver.net:465/ssl`)
- Remetente: `avisos@conselhosmunicipais.app.br`
- SPF atualizado para incluir IP do cPanel (`67.227.179.71`)
- `scripts/sync-swarm-env.sh`: sincroniza variáveis críticas do `.env` para os serviços Swarm (contorna limitação de bind-mount no Docker Swarm)
- Target `deploy-env` adicionado ao Makefile

### Corrigido
- Emails pessoais (`email_exibicao`) de presidente/vice/secretário removidos da seção Diretoria no portal público — mantido apenas o e-mail institucional do conselho

---

## [0.1.0] — 2026-09-14

### Adicionado

#### Infraestrutura
- Projeto Laravel 12 + Filament 3 inicializado
- Docker Compose com PHP 8.3-fpm-alpine, MySQL 8, Redis, Nginx, Horizon
- Deploy em Docker Swarm (`37.60.231.53`) via `make deploy`
- Makefile com targets: `up`, `down`, `shell`, `migrate`, `test`, `deploy`, `deploy-assets`, `deploy-env`
- Storage Cloudflare R2 para arquivos

#### Autenticação e controle de acesso
- Roles: `super_admin`, `admin_municipal`, `gestor_conselho`, `conselheiro`, `operador`
- Middleware `ForcePasswordReset`: bloqueia navegação até o usuário definir nova senha
- Fluxo de primeiro acesso para conselheiros (email-only, sem CPF)
- Impersonation via `lab404/laravel-impersonate` com trilha de auditoria
- Banner de aviso durante sessão de impersonation

#### Painel Admin (`/admin`)
- `MunicipioResource`: gerencia municípios, cria admin municipal, impersonation com motivo
- `UsuariosRelationManager`: lista usuários por município com edição, impersonation, histórico e reset de senha

#### Painel Municipal (`/painel/municipio/{slug}`)
- `ConselhoResource`: CRUD com campos identificação, contato, tipo, notificações, logotipo
- `ConselheiroResource`: CRUD com foto (R2), upload com editor de imagem 1:1
- `ComposicaoResource`: gerencia membros do conselho por tipo (presidente, vice, secretário, membro, suplente)
- `ReuniaoResource`: agendamento de reuniões com pauta, ata, status, presença, anexos, links
- Action "Gestores": atribui/revoga gestores por conselho, cria usuários externos
- Action "Notificar" manual: dispara e-mail avulso para composição
- Perfil do conselheiro: edita dados, senha, foto (com `must_reset_password` clearado ao trocar senha)

#### Portal público
- Página por município: lista todos os conselhos ativos com stats
- Página por conselho: diretoria, composição, próximas reuniões, reuniões realizadas, documentos, legislação, atos normativos
- PDF de lista de presença por reunião (DomPDF, A4 portrait)

#### Módulos
- `Municipios`: model, portal, slug
- `Conselhos`: model com notificações, gestores, relações
- `Composicao`: model com observer de gestor natural
- `Reunioes`: model, observer, notificações, anexos, links, presenças
- `Documentos`, `Legislacao`, `Resolucoes`, `Processos`, `Comissoes`: estrutura base

#### Auditoria
- Spatie Activity Log em todos os models principais
- Log de impersonation (take/leave) via eventos do package
- Log de perfil atualizado pelo conselheiro
- Log de gestor natural vinculado/revogado pelo observer

---

## [Não publicado]

### Planejado
- Notificações WhatsApp via Evolution API (infraestrutura pronta, aguarda conta)
- Portal de processos públicos
- Relatórios e exportações
- App mobile (PWA)
