---
title: "Consultar o log de auditoria"
description: "Como acessar, filtrar e interpretar os registros de auditoria do Portal dos Conselhos Municipais, incluindo eventos de impersonação e exportação."
secao: "Administração"
topico: "Auditoria"
---

## Visão geral

O **log de auditoria** é o registro cronológico e imutável de todas as ações relevantes realizadas no sistema. Ele responde às perguntas: quem fez, o que fez, em qual registro e quando. Esse mecanismo é essencial para a transparência administrativa, para o cumprimento da LGPD e para a investigação de incidentes.

O log é gravado automaticamente pelo sistema — nenhuma ação manual é necessária para ativá-lo.

> 💡 Somente usuários com as funções **admin_municipal**, **auditor** ou **encarregado_dados** têm acesso ao módulo de auditoria.

---

## O que é auditado

O sistema registra automaticamente os seguintes eventos em cada um dos módulos abaixo:

- **Criação** (evento: `created`): quando um novo registro é salvo pela primeira vez.
- **Edição** (evento: `updated`): quando qualquer campo de um registro existente é alterado.
- **Exclusão** (evento: `deleted`): quando um registro é removido permanentemente.

**Módulos auditados:**

- Conselhos
- Composições
- Conselheiros
- Reuniões
- Documentos
- Legislações
- Atos Normativos
- Comissões
- Usuários (incluindo criação, alteração de função e ativação/desativação)
- Solicitações LGPD (abertura, alteração de status — mas não o campo Resposta)
- Impersonações (evento especial com motivo registrado)

---

## O que NÃO é auditado

Para proteger dados sensíveis, os seguintes elementos **não são gravados** no log de auditoria:

- **Senhas:** nem o hash, nem qualquer indicação do valor anterior ou novo.
- **Campo "Resposta" das solicitações LGPD:** pode conter dados pessoais sensíveis do titular, e por isso é excluído do registro de auditoria por design.

> ⚠ A ausência desses campos no log é intencional e está em conformidade com as boas práticas da LGPD. Não tente contornar essa limitação.

---

## Como acessar o log de auditoria

1. No menu lateral, acesse **Administração → Auditoria**.
2. A listagem exibe os registros em ordem cronológica decrescente (mais recentes primeiro).
3. Use os filtros disponíveis para refinar a busca (veja a seção seguinte).

---

## Como usar os filtros

O módulo de auditoria oferece os seguintes filtros combináveis:

- **Usuário (quem fez):** selecione um usuário específico para ver apenas as ações realizadas por ele. Útil para investigar um colaborador ou auditar um período de impersonação.
- **Evento (ação):** filtre por `created`, `updated`, `deleted` ou pelo evento especial `impersonation`. Permite, por exemplo, ver apenas exclusões realizadas no período.
- **Entidade (módulo):** selecione o tipo de registro afetado — por exemplo, `Conselho`, `Reunião` ou `Documento`. Combine com o filtro de evento para resultados mais precisos.
- **Período:** defina uma **data inicial** e uma **data final** para restringir os resultados a um intervalo específico. As datas são inclusivas.

> 💡 Os filtros são cumulativos. Você pode combinar todos eles ao mesmo tempo para uma busca muito específica — por exemplo: "todas as exclusões feitas pelo usuário João Silva no módulo Documentos entre 01/09/2026 e 15/09/2026".

---

## Como ler um registro de auditoria

Cada linha da listagem contém as seguintes informações:

- **Data/hora:** timestamp exato da ação, no fuso horário configurado para o município.
- **Usuário responsável:** nome e e-mail de quem executou a ação. Em caso de impersonação, aparece o nome do administrador que impersonou, seguido de "(impersonando [nome do usuário])".
- **Evento:** ação realizada — criou, editou ou excluiu.
- **Entidade:** tipo do registro afetado (ex: `Conselho`, `Reunião`).
- **Identificação do registro:** nome ou identificador do item afetado (ex: nome do conselho, título da reunião).
- **Detalhes:** botão que abre o modal de detalhes completos.

---

## Ação "Detalhes": lendo as alterações campo a campo

Ao clicar em **Detalhes** em um registro de auditoria do tipo `updated`, um modal exibe todas as alterações realizadas naquela ação, campo a campo:

- **Campo:** nome do campo alterado.
- **Valor anterior:** o conteúdo do campo antes da alteração.
- **Valor novo:** o conteúdo do campo após a alteração.

Para eventos do tipo `created`, o painel mostra todos os valores definidos no momento da criação (o "valor anterior" fica em branco). Para `deleted`, mostra os valores que o registro tinha antes de ser excluído.

> ⚠ Campos que armazenam arquivos (como uploads de documentos) são exibidos com o nome do arquivo, não com o conteúdo binário.

---

## Eventos de impersonação

Quando um administrador impersona outro usuário, o sistema gera um registro de auditoria com o evento **`impersonation`**, contendo:

- Nome e e-mail do administrador que ativou a impersonação.
- Nome e e-mail do usuário impersonado.
- **Motivo informado** pelo administrador (campo obrigatório).
- Timestamp de início e de encerramento da impersonação.

Todas as ações realizadas durante a sessão de impersonação também são registradas normalmente, com indicação de que foram feitas em modo impersonado.

---

## Exportação do log de auditoria

Usuários com a permissão **`export.relatorios`** (concedida por padrão a admin_municipal e auditor) podem exportar o log filtrado em formato **CSV** ou **XLSX**.

1. Aplique os filtros desejados.
2. Clique no botão **Exportar** no canto superior direito da listagem.
3. Selecione o formato desejado e confirme.
4. O arquivo será gerado e baixado automaticamente.

> ⚠ A exportação do log de auditoria em si também é auditada — o sistema registra quem exportou, quando e com quais filtros aplicados.

---

## Perguntas frequentes

**Posso excluir ou editar um registro do log de auditoria?**
Não. O log de auditoria é **imutável por design**. Nenhum usuário — incluindo o super_admin — pode alterar ou remover registros de auditoria. Isso garante a integridade da trilha de evidências.

**Por quanto tempo os registros de auditoria são mantidos?**
Os registros são mantidos indefinidamente, a menos que o super_admin configure uma política de retenção no painel administrativo central. Consulte o administrador da plataforma para saber a política em vigor no seu município.

**O log registra tentativas de login malsucedidas?**
Sim. Tentativas de login com credenciais inválidas são registradas com o evento `login_failed`, incluindo o e-mail utilizado na tentativa. Isso permite identificar ataques de força bruta ou tentativas de acesso não autorizado.

**Como identificar ações realizadas durante uma impersonação?**
Use o filtro de **evento** e selecione `impersonation` para ver os eventos de início e fim de cada sessão. As ações individuais realizadas durante a impersonação aparecerão identificadas com a nota "(impersonando [nome])" no campo de usuário responsável.

**O auditor pode exportar o log?**
Sim. A função **auditor** possui a permissão `export.relatorios` por padrão, podendo exportar o log com qualquer combinação de filtros.
