---
title: "Publicar documentos no portal"
description: "Como fazer upload de documentos, controlar visibilidade pública ou privada, verificar integridade por hash SHA-256 e encontrar arquivos já cadastrados."
secao: "Gestão do Conselho"
topico: "Documentos"
---

## Tipos de arquivo suportados

O sistema aceita os seguintes formatos de arquivo:

- **PDF** (.pdf) — recomendado para documentos oficiais e publicações no portal.
- **Word** (.doc, .docx) — aceito para documentos em edição ou minutas.
- **Excel** (.xls, .xlsx) — para planilhas, orçamentos e relatórios tabulares.

O tamanho máximo por arquivo é **20MB**. Arquivos maiores devem ser comprimidos ou divididos antes do upload.

> 💡 Para documentos que serão publicados no portal, prefira sempre o formato PDF. Isso garante que qualquer pessoa possa abrir o arquivo sem precisar de software específico e evita problemas de formatação.

---

## Campos obrigatórios ao cadastrar um documento

Ao criar um novo documento, os seguintes campos são obrigatórios:

- **Conselho**: o conselho ao qual o documento pertence.
- **Tipo de documento**: categoria que classifica o documento (veja lista abaixo).
- **Título**: nome descritivo do documento (ex.: "Ata da 8ª Reunião Ordinária — Agosto/2026").
- **Data do documento**: data em que o documento foi produzido ou aprovado (não a data de upload).

Os demais campos são opcionais, mas recomendados:

- **Número/referência**: número de identificação do documento no conselho.
- **Descrição**: resumo do conteúdo ou observações relevantes.
- **Visibilidade**: Público ou Privado (padrão: Privado).

---

## Tipos de documento disponíveis

Os tipos de documento padronizados no sistema são:

- **Ata** — registro oficial das reuniões plenárias.
- **Convocação** — comunicado formal de reunião enviado aos membros.
- **Resolução** — ato normativo aprovado pelo plenário.
- **Deliberação** — decisão sobre matéria específica.
- **Relatório** — relatório de atividades, financeiro ou técnico.
- **Plano de Trabalho** — planejamento anual ou semestral do conselho.
- **Regimento** — regimento interno do conselho.
- **Outros** — documentos que não se encaixam nas categorias acima.

---

## Diferença entre documento privado e público

Todo documento é criado como **Privado** por padrão. Isso significa que ele fica acessível apenas dentro do painel administrativo, para usuários com login no sistema.

Ao marcar um documento como **Público**, ele passa a aparecer no **portal público** do município, visível para qualquer pessoa sem necessidade de login.

| Situação | Painel administrativo | Portal público |
|---|---|---|
| Privado | Visível | Não aparece |
| Público | Visível | Aparece |

> ⚠ Antes de publicar um documento, certifique-se de que ele não contém informações pessoais sensíveis (CPF, dados bancários, endereços residenciais de conselheiros) que não devam ser divulgadas publicamente.

---

## Como publicar um documento

1. Acesse o menu **Documentos** no painel do conselho.
2. Clique em **Novo Documento**.
3. Preencha os campos obrigatórios: conselho, tipo, título e data.
4. No campo **Arquivo**, clique em **Fazer upload** e selecione o arquivo no seu computador.
5. Aguarde a conclusão do upload. O sistema exibirá a barra de progresso.
6. Após o upload, o sistema calcula automaticamente o **hash SHA-256** do arquivo (veja explicação abaixo).
7. No campo **Visibilidade**, selecione **Público**.
8. Clique em **Salvar**.

O documento aparecerá imediatamente no portal público do município, na seção de documentos do conselho.

---

## Hash SHA-256 — verificação de integridade

Ao fazer o upload de qualquer arquivo, o sistema calcula automaticamente um código de verificação chamado **hash SHA-256**. Esse código é exibido nos detalhes do documento e funciona como uma "impressão digital" do arquivo.

**Para que serve:**
- Comprova que o arquivo não foi alterado depois de publicado.
- Qualquer pessoa pode baixar o documento e verificar se o hash bate com o exibido no portal.
- É especialmente importante para atas, resoluções e deliberações, que têm validade jurídica.

Você não precisa fazer nada para ativar essa funcionalidade — ela é automática em todos os uploads.

---

## Como encontrar documentos já cadastrados

Use os filtros disponíveis na listagem de documentos:

- **Tipo de documento**: filtra por ata, convocação, resolução etc.
- **Conselho**: útil para usuários que gerenciam mais de um conselho.
- **Visibilidade**: filtra entre Público e Privado.
- **Período**: filtra pela data do documento (não pela data de upload).

Você também pode usar a **barra de busca** para pesquisar pelo título do documento.

---

## Como substituir um documento já publicado

Se precisar corrigir ou atualizar um arquivo já publicado:

1. Localize o documento na listagem.
2. Clique em **Editar**.
3. No campo **Arquivo**, clique em **Substituir arquivo** e faça o upload do novo arquivo.
4. O sistema calculará um novo hash SHA-256 para o arquivo atualizado.
5. Salve as alterações.

> ⚠ Substituir o arquivo atualiza o hash e sobrescreve o arquivo anterior. Se precisar manter o documento original para fins de histórico, crie um novo registro em vez de substituir o existente.

---

## Perguntas frequentes

### O arquivo some do portal se eu mudar a visibilidade para Privado depois de publicar?
Sim. Ao alterar a visibilidade de Público para Privado, o documento é retirado imediatamente do portal. O link anterior para o arquivo passa a retornar erro 404.

### Posso vincular um documento a uma reunião específica?
Sim. Dentro da reunião, acesse a aba **Anexos** e faça o upload diretamente por lá. O documento ficará vinculado à reunião e também aparecerá na listagem geral de documentos do conselho.

### Há limite de quantos documentos posso cadastrar?
Não há limite de quantidade de registros. O limite é de 20MB por arquivo. Caso precise armazenar apresentações ou vídeos de grandes dimensões, considere usar links externos (Google Drive, YouTube) e cadastrar apenas a referência no campo de descrição.

### Como sei se um documento foi realmente publicado no portal?
Acesse o portal público do seu município como visitante (sem estar logado no painel) e navegue até a página do conselho. Se o documento aparecer lá, a publicação está ativa. Você também pode verificar o campo **Visibilidade** na listagem de documentos — deve estar marcado como **Público**.
