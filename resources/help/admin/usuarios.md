---
title: "Gerenciar usuários e permissões"
description: "Como criar, editar, impersonar e remover usuários no Portal dos Conselhos Municipais, e como as funções (roles) controlam o acesso a cada funcionalidade."
secao: "Administração"
topico: "Usuários e Permissões"
---

## Visão geral

O Portal dos Conselhos Municipais utiliza um modelo de controle de acesso baseado em **funções (roles)**. Cada usuário recebe exatamente uma função, que determina o que ele pode visualizar, criar, editar ou excluir dentro do município ao qual pertence. O sistema é **multi-tenant**: um usuário de um município não enxerga dados de outro município.

> 💡 Um usuário pode ter apenas uma função por vez. Para alterar a função, edite o registro do usuário e selecione a nova função no campo **Funções**.

---

## Funções disponíveis e suas permissões

### admin_municipal

Acesso completo a todos os módulos do município: usuários, conselhos, composições, conselheiros, reuniões, documentos, legislações, atos normativos, comissões, processos e módulo LGPD. Pode criar, editar e excluir qualquer registro, além de impersonar outros usuários e exportar relatórios.

**Limitação:** não pode excluir o próprio município nem alterar configurações globais da plataforma (isso é exclusivo do super_admin).

### gestor_conselho

Gerencia um ou mais conselhos específicos aos quais foi vinculado. Pode criar e editar composições, conselheiros, reuniões, documentos e legislações desses conselhos. Não tem acesso aos módulos de outros conselhos do mesmo município, nem ao módulo LGPD ou à gestão de usuários.

### operador

Cria e edita registros nos módulos para os quais foi autorizado, mas **não pode excluir registros** nem executar ações sensíveis como anonimização, exportação de dados pessoais ou impersonação. Indicado para servidores que alimentam o sistema no dia a dia.

### conselheiro

Acesso de somente leitura ao painel restrito. Pode visualizar reuniões, documentos e composições do conselho ao qual pertence, mas não pode criar, editar ou excluir nenhum registro. Ideal para membros que precisam acompanhar o andamento sem alterar dados.

### encarregado_dados

Acesso completo ao módulo LGPD: solicitações de titulares, registro de atividades de tratamento e relatório de impacto. Também pode visualizar dados sensíveis de conselheiros e acessar o log de auditoria. Não gerencia cadastros operacionais (reuniões, documentos etc.).

### auditor

Acesso de somente leitura ao **log de auditoria**. Pode visualizar e exportar registros de auditoria, mas não pode criar, editar ou excluir nenhum dado do sistema. Indicado para auditorias internas ou controle externo.

---

## Como criar um novo usuário

1. No menu lateral, acesse **Administração → Usuários**.
2. Clique em **Novo usuário**.
3. Preencha os campos obrigatórios:
   - **Nome completo:** nome que aparecerá nos registros de auditoria.
   - **E-mail:** será usado como login. Deve ser único no sistema.
   - **Senha temporária:** defina uma senha inicial. O usuário será obrigado a redefini-la no primeiro acesso.
   - **Função:** selecione a função adequada (veja a seção anterior).
4. Se a função for **gestor_conselho**, o campo **Conselhos vinculados** ficará disponível — selecione os conselhos que esse usuário poderá gerenciar.
5. Clique em **Salvar**.

> ⚠ O sistema enviará um e-mail de boas-vindas com instruções de acesso apenas se o envio de e-mail estiver configurado pelo super_admin. Caso contrário, informe as credenciais ao usuário por outro canal seguro.

---

## Como alterar a função de um usuário existente

1. Acesse **Administração → Usuários**.
2. Localize o usuário na lista e clique em **Editar** (ícone de lápis).
3. No campo **Função**, selecione a nova função desejada.
4. Se necessário, atualize também o campo **Conselhos vinculados**.
5. Clique em **Salvar**.

A alteração entra em vigor imediatamente. Se o usuário estiver com sessão ativa, ele perderá ou ganhará permissões assim que navegar para outra página.

---

## Como forçar a redefinição de senha

Utilize esse recurso quando suspeitar de comprometimento de credenciais ou quando um usuário esquecer a senha.

1. Acesse **Administração → Usuários**.
2. Localize o usuário e clique em **Redefinir senha** (botão na linha do registro ou dentro da tela de edição).
3. Confirme a ação na janela de diálogo.

Após a confirmação, o usuário ficará **bloqueado** até que defina uma nova senha por meio do link enviado ao e-mail cadastrado. A redefinição é registrada no log de auditoria.

> ⚠ Se o e-mail do usuário estiver incorreto ou inacessível, o admin_municipal deve editar o e-mail antes de acionar a redefinição, ou criar um novo usuário.

---

## Impersonar um usuário

A impersonação permite que um administrador acesse o sistema **como se fosse outro usuário**, sem conhecer a senha dele. É um recurso destinado exclusivamente ao suporte técnico e à resolução de problemas.

1. Acesse **Administração → Usuários**.
2. Localize o usuário e clique em **Impersonar**.
3. Uma janela solicitará o **motivo da impersonação** — campo obrigatório, registrado em auditoria.
4. Confirme. O sistema abrirá uma nova sessão com as permissões do usuário selecionado. Uma barra de aviso laranja ficará visível no topo da tela indicando que você está em modo de impersonação.
5. Para encerrar, clique em **Deixar impersonação** na barra de aviso.

> ⚠ Toda impersonação é registrada no log de auditoria com: administrador que executou, usuário impersonado, motivo informado, data/hora de início e fim. Use esse recurso apenas quando estritamente necessário.

---

## Excluir um usuário

1. Acesse **Administração → Usuários**.
2. Localize o usuário e clique em **Excluir**.
3. Confirme a ação.

A exclusão **remove o acesso** do usuário ao sistema, mas **preserva o histórico de atividades** dele no log de auditoria. Os registros que ele criou (documentos, reuniões etc.) também são mantidos, atribuídos ao nome do usuário mesmo após a exclusão.

> 💡 Se quiser apenas suspender temporariamente o acesso sem excluir, prefira **desativar** o usuário — o campo **Ativo** na tela de edição controla isso.

---

## Perguntas frequentes

**Um usuário pode pertencer a dois municípios ao mesmo tempo?**
Não. O sistema é multi-tenant e cada usuário pertence a exatamente um município. Para dar acesso a um segundo município, crie um novo cadastro com e-mail diferente naquele município.

**Posso criar um usuário sem enviar e-mail de boas-vindas?**
Sim. Se o envio de e-mail não estiver configurado, o sistema criará o usuário normalmente, mas nenhum e-mail será disparado. Informe as credenciais ao usuário manualmente.

**O que acontece com os documentos criados por um usuário excluído?**
Os documentos e registros permanecem no sistema, vinculados ao nome do usuário excluído. O histórico de auditoria também é preservado integralmente.

**Um operador pode visualizar o log de auditoria?**
Não. O log de auditoria é acessível apenas para admin_municipal, auditor e encarregado_dados.

**Como sei qual função foi atribuída a um usuário?**
Na listagem **Administração → Usuários**, a coluna **Função** exibe a função atual de cada usuário. Você também pode usar o filtro de função para encontrar todos os usuários de um determinado perfil.
