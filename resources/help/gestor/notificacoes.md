---
title: "Enviar notificações aos conselheiros"
description: "Como usar os canais de notificação disponíveis (e-mail e WhatsApp), enviar convocações manualmente, consultar o histórico de envios e resolver problemas de entrega."
secao: "Gestão do Conselho"
topico: "Notificações"
---

## Canais de notificação disponíveis

O sistema suporta dois canais para enviar comunicados aos conselheiros:

- **E-mail** — sempre disponível. Qualquer conselheiro com endereço de e-mail cadastrado na composição pode receber notificações por esse canal.
- **WhatsApp** — disponível quando o módulo de integração estiver configurado pelo administrador do sistema. Requer número de celular cadastrado no formato correto (com DDD, sem espaços ou traços).

> 💡 O e-mail é o canal mais confiável para registros formais. O WhatsApp pode ter maior taxa de leitura, mas depende de configuração adicional. Quando ambos estiverem disponíveis, use os dois para garantir que a mensagem chegue.

---

## Quando o sistema envia notificações automaticamente

O sistema dispara notificações de forma automática nas seguintes situações:

- **Ao criar uma reunião** — se a configuração "Notificar ao criar reunião" estiver ativa no conselho, todos os membros ativos da composição recebem a convocação imediatamente.
- **Ao alterar data, horário ou local de uma reunião já agendada** — o sistema envia um aviso de atualização automaticamente para evitar que membros compareçam com informações desatualizadas.

> ⚠ A notificação automática ao criar reunião precisa estar habilitada nas **Configurações do Conselho**. Verifique com o administrador do sistema se essa opção está ativa para o seu conselho.

---

## Como enviar uma convocação manualmente

Além das notificações automáticas, você pode enviar a convocação manualmente a qualquer momento — útil para reforçar a convocação próximo à data da reunião ou quando a notificação automática não estava ativa.

1. Abra a reunião que deseja convocar.
2. Clique no botão **Notificar Membros** (ou **Enviar Convocação**).
3. O sistema pré-preenche automaticamente o assunto e o corpo da mensagem com os dados da reunião (data, horário, local e pauta).
4. Revise o texto. Você pode editar o corpo da mensagem para adicionar informações complementares, documentos que os membros devem trazer ou orientações específicas.
5. Selecione os canais de envio: **E-mail**, **WhatsApp** ou ambos.
6. Clique em **Confirmar envio**.

O sistema processa o envio em segundo plano e exibe uma confirmação quando concluído.

---

## Quem recebe as notificações

As notificações são enviadas para todos os membros **ATIVOS** da composição do conselho que possuam:

- E-mail cadastrado (para envios por e-mail), ou
- Número de celular cadastrado (para envios por WhatsApp).

Membros com mandato ENCERRADO ou sem dados de contato preenchidos não recebem as notificações.

> ⚠ Os dados de contato usados para notificação são os da **composição** (e-mail público e telefone público), não os dados pessoais do cadastro de conselheiro. Verifique se esses campos estão preenchidos corretamente em cada membro da composição.

---

## Histórico de notificações

Todas as notificações enviadas ficam registradas e podem ser consultadas:

1. Abra a reunião.
2. Acesse a aba **Notificações**.
3. A tabela exibe cada envio com as seguintes informações:
   - **Data e hora** do envio.
   - **Canal** utilizado (E-mail ou WhatsApp).
   - **Destinatário** (nome do conselheiro e endereço/número).
   - **Status** do envio: Enviado, Entregue, Erro.

O registro é imutável — você não pode excluir o histórico de notificações. Isso garante rastreabilidade para fins de comprovação de convocação.

---

## Boas práticas no envio de convocações

- **Prazo mínimo para reunião ordinária**: envie com pelo menos **48 horas de antecedência**. Isso garante que os membros tenham tempo hábil para organizar a agenda e preparar-se para os pontos de pauta.
- **Prazo mínimo para reunião extraordinária**: envie com pelo menos **24 horas de antecedência**.
- **Inclua na mensagem**: data, horário de início, local completo, pauta resumida e qualquer documento que os membros devam analisar previamente.
- **Reforce próximo à data**: envie um segundo lembrete no dia anterior (ou pela manhã do dia da reunião) para aumentar o comparecimento.
- **Use ambos os canais** quando disponíveis: e-mail para o registro formal, WhatsApp para a comunicação ágil.

---

## O que fazer se um conselheiro não receber a notificação

Se um membro relatar que não recebeu a convocação:

1. Acesse a aba **Notificações** da reunião e verifique se o envio para aquele conselheiro aparece com status **Enviado** ou **Erro**.
2. Se aparecer como **Erro**, o problema pode ser:
   - E-mail incorreto ou desatualizado — corrija na aba de composição do conselho, no campo **E-mail público** do membro.
   - Número de WhatsApp incorreto — verifique o formato (deve conter DDD + número, apenas dígitos, sem espaço ou traço).
   - Caixa de spam — oriente o conselheiro a verificar a pasta de spam e marcar o remetente como confiável.
3. Após corrigir os dados, reenvie a notificação manualmente pelo botão **Notificar Membros**.

> 💡 Para evitar problemas recorrentes, valide os dados de contato de todos os membros sempre que houver renovação da composição ou atualização de cadastro.

---

## Perguntas frequentes

### Posso notificar apenas um membro específico, em vez de todos?
Por padrão, o botão **Notificar Membros** envia para todos os ativos com contato cadastrado. Se precisar notificar apenas uma pessoa, entre em contato diretamente fora do sistema ou aguarde a implementação de filtros de destinatário, disponível em versões futuras.

### O sistema notifica membros de comissões também?
Sim. Dentro de uma **reunião de comissão**, o botão **Notificar Membros** envia a convocação para os membros daquela comissão (não para a composição do plenário). O funcionamento é idêntico.

### Posso personalizar o template padrão das notificações?
O template padrão pode ser personalizado pelo administrador do sistema nas **Configurações Gerais**. Ajustes de texto, assinatura e identidade visual do e-mail são feitos por lá. Usuários comuns do painel podem apenas editar o texto do corpo no momento do envio manual.
