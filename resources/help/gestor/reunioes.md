---
title: "Registrar e gerenciar reuniões"
description: "Como criar reuniões, registrar presenças, lançar atas, adicionar anexos, notificar membros e publicar no portal."
secao: "Gestão do Conselho"
topico: "Reuniões"
---

## Ciclo de vida de uma reunião

Toda reunião passa por um conjunto de etapas desde o agendamento até a publicação final. Entender esse fluxo ajuda a manter o histórico completo e o portal atualizado:

1. **AGENDADA** — reunião criada com data futura. Aparece no portal como "reunião prevista".
2. **REALIZADA** — reunião ocorreu. Permite registrar presenças e ata.
3. **CANCELADA** — reunião não aconteceu. Informe o motivo para fins de registro histórico.

> 💡 Reuniões agendadas com data passada que não foram atualizadas ficam com status desatualizado. Mantenha o status sempre correto para que o portal reflita a realidade.

---

## Como criar uma reunião

Acesse o menu **Reuniões** do conselho e clique em **Nova Reunião**. Preencha os campos:

1. **Conselho**: selecione o conselho que realizará a reunião.
2. **Tipo**: escolha entre as opções disponíveis:
   - **Ordinária** — reunião regular, prevista no calendário do conselho.
   - **Extraordinária** — convocada fora do calendário regular para matéria urgente.
   - **Solene** — cerimônia formal (posse, homenagem).
   - **Audiência pública** — reunião aberta à participação da sociedade civil.
3. **Número da reunião**: número sequencial conforme o regimento (ex.: "12ª Reunião Ordinária de 2026").
4. **Data e horário**: data, hora de início e hora prevista de encerramento.
5. **Local**: endereço ou nome do espaço onde ocorrerá (ex.: "Auditório da Prefeitura, Sala 2").
6. **Pauta**: descreva os pontos que serão discutidos. Use uma linha por item de pauta.
7. **Link de transmissão**: se houver transmissão ao vivo, cole o link (YouTube, Zoom, Google Meet etc.).
8. **Audiência pública**: marque esta opção para que a reunião receba destaque no portal como "audiência pública aberta à participação".

Clique em **Salvar**. A reunião é criada com status **AGENDADA** e, se marcada como pública, já aparece no portal.

---

## Registrar presença

Após a realização da reunião, atualize o status para **REALIZADA** e registre as presenças:

1. Abra a reunião e clique na aba **Presenças**.
2. O sistema exibe automaticamente todos os membros ATIVOS da composição do conselho na data da reunião.
3. Marque cada membro como:
   - **Presente**
   - **Ausente com justificativa**
   - **Ausente sem justificativa**
4. Para membros que participaram virtualmente (videoconferência), marque **Presente** e adicione a observação "Participação remota" no campo de notas.
5. Salve as presenças.

Após salvar, o botão **Gerar PDF da lista de presença** fica disponível. O PDF gerado inclui nome, cargo, assinatura (linha em branco) e o carimbo da reunião.

> ⚠ O quórum mínimo para deliberação depende do regimento interno de cada conselho. O sistema não calcula quórum automaticamente — essa verificação cabe ao secretário executivo.

---

## Registrar a ata

A ata é o documento oficial que registra o que foi discutido e decidido na reunião.

1. Dentro da reunião, acesse a aba **Ata**.
2. Clique em **Redigir Ata** e escreva o texto no editor rich text disponível. Você pode formatar parágrafos, usar listas e negrito.
3. Salve o rascunho. O status da ata fica como **EM ELABORAÇÃO** até ser aprovada.
4. Quando a ata for aprovada pelo plenário, clique em **Aprovar Ata**.
5. Informe a **data de aprovação** (pode ser a data da própria reunião ou da reunião seguinte, conforme o regimento).
6. Confirme. O sistema sela um **timestamp** de aprovação que não pode ser alterado.

> ⚠ Uma vez aprovada, o texto da ata é bloqueado para edição. Se precisar fazer uma retificação, registre uma errata como novo documento vinculado à reunião.

---

## Anexos

Você pode anexar arquivos diretamente à reunião para organizar os documentos relacionados:

1. Acesse a aba **Anexos** dentro da reunião.
2. Clique em **Adicionar Anexo**.
3. Selecione o tipo do documento (Convocação, Ata Assinada, Deliberação, Relatório, Outro).
4. Faça o upload do arquivo (PDF, Word ou Excel, máximo 20MB por arquivo).
5. Salve.

Documentos marcados como **Público** dentro dos anexos da reunião aparecem automaticamente no portal vinculados àquela reunião.

---

## Notificar membros

Para enviar a convocação por e-mail ou WhatsApp:

1. Dentro da reunião, clique no botão **Notificar Membros**.
2. Revise o assunto e o corpo da mensagem gerados automaticamente pelo sistema.
3. Personalize se necessário (adicione informações sobre a pauta, documentos a serem levados etc.).
4. Selecione os canais: **E-mail**, **WhatsApp** ou ambos.
5. Confirme o envio.

O sistema envia a notificação para todos os membros ATIVOS da composição que possuem e-mail ou número de WhatsApp cadastrado. O histórico de envios fica registrado na aba **Notificações** da reunião.

---

## O que aparece no portal público

O portal exibe as seguintes informações de reuniões:

- **Reuniões agendadas**: data, tipo, número, horário e local. O link de transmissão é exibido quando disponível.
- **Audiências públicas**: recebem um destaque especial na página inicial do portal.
- **Reuniões realizadas**: data, tipo, número e link para a ata (quando publicada).

---

## Perguntas frequentes

### Posso criar uma reunião sem definir o local ainda?
Sim. O campo local não é obrigatório no momento do cadastro. Você pode salvar a reunião e atualizar o local quando definido. O sistema irá notificar os membros novamente caso você altere data, horário ou local após o primeiro envio de convocação.

### Como cancelar uma reunião já agendada?
Edite a reunião, altere o status para **CANCELADA** e informe o motivo. A reunião some do portal (não aparece mais como agendada) e o registro fica no histórico interno.

### A ata pode ser enviada por arquivo (PDF) em vez de digitada no sistema?
Sim. Você pode usar o campo de texto da ata para indicar "ver arquivo anexo" e fazer o upload do PDF da ata assinada na aba **Anexos**, marcando o tipo como "Ata Assinada" e visibilidade como Pública.

### O sistema envia convocação automaticamente quando crio a reunião?
Não por padrão. A convocação automática é enviada somente se a configuração **"Notificar ao criar reunião"** estiver ativa nas configurações do conselho. Verifique com o administrador do sistema.

### Como gerar o PDF da lista de presença?
O botão **Gerar PDF** fica disponível na aba **Presenças** depois que pelo menos um membro for marcado. O PDF é gerado com a lista completa, incluindo membros ausentes.

### Posso registrar a presença de visitantes ou participantes externos?
Sim. Na aba **Presenças**, há uma seção **Participantes externos** onde você pode adicionar nomes manualmente. Esses nomes aparecem na lista de presença, mas não são vinculados à composição.

### O link de transmissão aparece para todos no portal?
Sim, quando a reunião está marcada como **AGENDADA** ou **REALIZADA** e o link está preenchido, ele fica visível no portal público para qualquer visitante.
