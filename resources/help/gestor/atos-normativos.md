---
title: "Criar e publicar atos normativos"
description: "Como registrar resoluções, deliberações, recomendações e demais atos normativos do conselho, gerenciar o ciclo de aprovação e publicar no portal."
secao: "Gestão do Conselho"
topico: "Atos Normativos"
---

## O que são atos normativos

**Atos normativos** são documentos produzidos pelo conselho que têm efeitos jurídicos formais. Diferentemente de uma ata (que registra o que aconteceu) ou de um relatório (que descreve uma situação), o ato normativo **decide, orienta, recomenda ou regulamenta** algo de forma oficial.

Eles integram o ordenamento interno do conselho e, dependendo do tipo, podem ter força vinculante para a administração pública municipal.

---

## Tipos de atos normativos e quando usar cada um

### Resolução
Decisão vinculante aprovada pelo plenário sobre matéria de competência do conselho. Tem força obrigatória para a administração municipal no âmbito das atribuições do conselho.

**Exemplos:** Aprovação de plano de trabalho, definição de critérios de credenciamento, aprovação de normas internas.

### Deliberação
Decisão do plenário sobre um caso ou matéria específica, geralmente mais pontual que uma resolução.

**Exemplos:** Aprovação de relatório de gestão, manifestação sobre proposta orçamentária, decisão sobre recurso administrativo.

### Recomendação
Manifestação do conselho sugerindo uma ação ao poder público ou à sociedade, sem caráter vinculante. Tem força política e moral, mas não obriga juridicamente.

**Exemplos:** Recomendação para ampliação de serviço público, sugestão de adequação de política municipal.

### Parecer
Opinião técnica ou jurídica emitida pelo conselho ou por uma comissão sobre determinado assunto submetido à apreciação.

**Exemplos:** Parecer sobre prestação de contas, parecer sobre proposta legislativa enviada pelo executivo.

### Moção
Manifestação política, de congratulação, de pesar ou de repúdio do conselho sobre um fato ou situação.

**Exemplos:** Moção de aplausos a entidade, moção de repúdio a ato do executivo, moção de pesar por falecimento.

### Portaria
Ato administrativo interno que organiza o funcionamento do conselho. Não tem efeito externo vinculante.

**Exemplos:** Portaria de designação de comissão, portaria de aprovação de calendário anual de reuniões.

---

## Status dos atos normativos

- **APROVADO** — ato votado e aprovado pelo plenário, mas ainda não publicado oficialmente. Pode ser corrigido.
- **VIGENTE** — ato publicado e em plena vigência. Aparece no portal público.
- **REVOGADO** — ato que foi expressamente substituído ou cancelado por outro ato normativo posterior.
- **SUSPENSO** — ato com efeitos temporariamente interrompidos (por decisão judicial, por exemplo).

---

## Fluxo típico de publicação

O caminho mais comum para um ato normativo percorre as seguintes etapas:

1. Plenário vota e aprova o ato na reunião.
2. Secretário cria o registro no sistema com status **APROVADO** e lança o texto completo.
3. O presidente ou secretário revisa o texto e os dados.
4. Usuário com permissão de publicação clica em **Publicar** — o status muda automaticamente para **VIGENTE**.
5. O ato aparece no portal público na seção de atos normativos do conselho.

> ⚠ A publicação é irreversível no sentido prático: uma vez que o ato passa para VIGENTE e é acessado pelo público, qualquer alteração deve ser feita via errata ou revogação, nunca por edição silenciosa do texto. O sistema mantém log de todas as alterações.

---

## Como criar um ato normativo

1. Acesse o menu **Atos Normativos** no painel do conselho.
2. Clique em **Novo Ato Normativo**.
3. Preencha os campos:
   - **Conselho**: selecione o conselho.
   - **Tipo**: escolha o tipo conforme a natureza do ato (Resolução, Deliberação etc.).
   - **Número**: número sequencial conforme a numeração do conselho (ex.: "007").
   - **Ano**: ano de aprovação do ato (ex.: "2026").
   - **Ementa**: resumo em uma frase do que o ato decide. É o texto exibido na listagem do portal.
   - **Reunião de origem**: vincule à reunião em que o ato foi aprovado (opcional, mas recomendado).
   - **Data de aprovação**: data em que o plenário votou.
   - **Texto completo**: redija o texto no editor rich text. O editor suporta parágrafos, listas, tabelas e negrito.
4. Clique em **Salvar como rascunho** (status APROVADO).

---

## Publicar o ato normativo

A publicação exige a permissão **publicar.atos-normativos**. Se você não tiver essa permissão, o botão **Publicar** não aparece na tela — nesse caso, solicite ao administrador do sistema.

Para publicar:

1. Abra o ato normativo que está com status **APROVADO**.
2. Revise o texto, a ementa, o número e o ano.
3. Clique em **Publicar**.
4. Confirme na janela de confirmação.
5. O status muda para **VIGENTE** e o ato aparece imediatamente no portal.

> 💡 A ementa é o campo mais importante para a experiência do usuário no portal. Escreva de forma clara e objetiva. Exemplo: "Aprova o Regimento Interno do Conselho Municipal de Saúde" ou "Recomenda ao Executivo Municipal a ampliação das equipes da Atenção Básica".

---

## Revogar ou suspender um ato

Para revogar um ato que não está mais em vigor:

1. Abra o ato normativo.
2. Clique em **Revogar**.
3. Informe o ato normativo posterior que fundamenta a revogação (número e ano).
4. Confirme.

O status muda para **REVOGADO** e o portal exibe uma indicação de que o ato foi revogado, com referência ao ato revogador.

---

## Perguntas frequentes

### Posso editar o texto de um ato depois de publicar?
O sistema permite edição técnica com registro em log de auditoria, mas do ponto de vista jurídico, um ato publicado não deve ter seu texto alterado. Se houver erro material, a prática correta é emitir uma **errata** (um novo ato corrigindo o anterior) e vinculá-la ao ato original.

### O número do ato é gerado automaticamente?
Não. O número é preenchido manualmente para respeitar a numeração própria de cada conselho, que pode ter sequências diferentes por tipo de ato (ex.: Resolução 1/2026, Deliberação 1/2026). Certifique-se de consultar o histórico antes de atribuir um número.

### Quem pode publicar atos normativos?
Apenas usuários com a permissão **publicar.atos-normativos** atribuída no perfil. Normalmente essa permissão é concedida ao presidente e ao secretário executivo do conselho. Fale com o administrador do sistema para ajustar as permissões.

### Como faço para que um ato apareça vinculado à ata da reunião?
Vincule o ato ao campo **Reunião de origem** ao criá-lo. Dessa forma, o portal exibirá o ato tanto na seção de atos normativos quanto nos detalhes daquela reunião.
