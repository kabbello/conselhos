---
title: "Configurar o município e os conselhos"
description: "Como gerenciar os dados institucionais do município e as configurações individuais de cada conselho, incluindo identidade visual, contatos e o que é exibido no portal público."
secao: "Administração"
topico: "Configurações"
---

## Visão geral

O Portal dos Conselhos Municipais opera em dois níveis de configuração: o **nível do município** (gerenciado pelo super_admin no painel administrativo central) e o **nível do conselho** (gerenciado pelo admin_municipal ou pelo gestor_conselho no painel do município). Entender essa distinção é importante para saber onde cada ajuste deve ser feito.

> 💡 Configurações de nível municipal — como nome, código IBGE e identidade visual do município — só podem ser alteradas pelo **super_admin** da plataforma. Se precisar corrigir esses dados, abra uma solicitação junto ao administrador da plataforma.

---

## Dados do município

Os dados a seguir são configurados pelo **super_admin** no painel administrativo central da plataforma e identificam o município perante o sistema e o portal público.

### Identidade

- **Nome do município:** nome oficial, usado em todos os cabeçalhos e documentos gerados.
- **Sigla:** abreviação, usada em contextos compactos.
- **UF:** unidade federativa (estado), selecionada a partir de lista padronizada.
- **Código IBGE:** código oficial de 7 dígitos. Usado como identificador único do município na plataforma.

### Identidade visual

- **Logomarca:** imagem em formato PNG ou SVG exibida no cabeçalho do portal público e do painel administrativo.
- **Brasão:** imagem oficial do município, exibida em documentos gerados pelo sistema.
- **Cor primária do portal:** código hexadecimal da cor principal utilizada na interface do portal público (botões, destaques, links).

> ⚠ Substitua a logomarca e o brasão apenas com arquivos de alta resolução (mínimo 300 dpi para o brasão). Imagens de baixa qualidade prejudicam a apresentação do portal público.

### Contato institucional

- **E-mail institucional:** endereço de e-mail geral do município, exibido no portal.
- **Telefone:** telefone da prefeitura ou da secretaria responsável pelos conselhos.
- **Site oficial:** URL do portal da prefeitura.
- **Link do portal de transparência:** URL do portal de transparência, exibido no rodapé do portal dos conselhos.
- **Link da ouvidoria:** URL da ouvidoria municipal, exibido no portal para orientar cidadãos.

### Endereço e dados institucionais

- **Endereço da sede:** logradouro, número, bairro, CEP.
- **Nome do prefeito(a):** exibido em documentos gerados pelo sistema.
- **População estimada:** dado informativo, usado em relatórios estatísticos.
- **Área territorial (km²):** dado informativo.

---

## Dados de cada conselho

Cada conselho possui suas próprias configurações, editáveis pelo **admin_municipal** (todos os conselhos do município) ou pelo **gestor_conselho** (apenas os conselhos aos quais está vinculado).

Para acessar: **Conselhos → [nome do conselho] → Editar**.

### Identificação

- **Nome completo:** nome oficial do conselho, exibido no portal e em documentos.
- **Sigla:** abreviação do nome, usada em listagens compactas.
- **Tipo:** categoria do conselho (ex: Conselho de Saúde, Conselho de Educação, Conselho Tutelar). Tipos são configurados pelo super_admin e pelo admin_municipal.
- **Descrição:** texto livre que apresenta o conselho ao cidadão. **Aparece no portal público** quando o conselho é marcado como público.

### Identidade visual do conselho

- **Logo do conselho:** imagem específica do conselho (PNG ou SVG), exibida na página do conselho no portal público. Caso não seja definida, o sistema usa a logomarca do município.

### Contato do conselho

- **E-mail de contato:** endereço específico do conselho para recebimento de demandas da sociedade.
- **Telefone:** telefone do conselho ou da secretaria de apoio.
- **Endereço de funcionamento:** local onde o conselho realiza suas reuniões e atende o público, se diferente da sede da prefeitura.

### Notificações

- **Notificações por e-mail:** quando ativado, o sistema envia e-mails automáticos para os conselheiros sobre novas reuniões, documentos publicados e mudanças de status.
- **Notificações por WhatsApp:** quando ativado (requer integração configurada pelo super_admin), envia alertas via WhatsApp para os números cadastrados nos perfis dos conselheiros.

> ⚠ Ativar notificações por WhatsApp exige que a integração com a API do WhatsApp Business esteja configurada e ativa no nível da plataforma. Verifique com o super_admin antes de habilitar essa opção.

---

## Configurar tipos de documento

O sistema permite que o admin_municipal crie **tipos de documento personalizados** para classificar os arquivos enviados em cada conselho. Isso facilita a organização e a busca no portal público.

1. Acesse **Configurações → Tipos de Documento**.
2. Clique em **Novo tipo**.
3. Informe o **nome** (ex: "Ata de Reunião Ordinária", "Relatório Anual", "Parecer Técnico").
4. Defina se o tipo é **público** (visível no portal) ou **interno** (visível apenas no painel administrativo).
5. Clique em **Salvar**.

> 💡 Tipos de documento bem definidos facilitam a navegação do cidadão no portal e melhoram a conformidade com a Lei de Acesso à Informação (LAI).

---

## O que aparece no portal público

O portal público exibe apenas informações **marcadas como públicas** no sistema. Os critérios gerais são:

- **Conselho:** aparece no portal se o campo **Visibilidade** estiver como "Público".
- **Descrição do conselho:** exibida na página do conselho quando marcada como pública.
- **Contatos do conselho:** e-mail, telefone e endereço são exibidos quando o conselho está público.
- **Documentos:** cada documento possui individualmente um campo de visibilidade. Documentos marcados como "Público" são acessíveis no portal sem autenticação.
- **Reuniões:** reuniões com visibilidade "Pública" aparecem na agenda do portal, inclusive com a pauta quando disponível.
- **Atos Normativos e Legislações:** publicados automaticamente no portal quando cadastrados, a menos que sejam marcados como "Internos".
- **Composição:** a lista de membros ativos de um conselho é exibida no portal quando o conselho está público.

> ⚠ Antes de tornar um conselho público, verifique se todos os dados exibidos estão corretos e atualizados. O portal público é acessível a qualquer cidadão sem autenticação.

---

## Perguntas frequentes

**Posso alterar o código IBGE do município caso tenha sido cadastrado errado?**
Não diretamente. O código IBGE é um identificador crítico do município no sistema. Para corrigi-lo, entre em contato com o super_admin da plataforma, pois a alteração pode exigir ajustes em outros registros vinculados.

**Por que a logo do meu conselho não aparece no portal mesmo depois de fazer o upload?**
Verifique se o conselho está com a visibilidade definida como "Público". Conselhos com visibilidade "Interno" não aparecem no portal, e portanto sua logo também não será exibida. Se o conselho estiver público e a logo ainda não aparecer, tente limpar o cache do navegador ou aguarde alguns minutos para a propagação do cache do servidor.

**As notificações por e-mail são enviadas imediatamente após uma alteração?**
Não necessariamente. O envio de e-mails é processado em fila assíncrona (queue worker). Em condições normais, o e-mail chega em menos de 5 minutos após o evento. Em momentos de alta demanda, pode haver atraso maior. Consulte o super_admin se os e-mails estiverem atrasando sistematicamente.

**Um gestor_conselho pode alterar o nome oficial do conselho?**
Sim. O gestor_conselho tem permissão para editar os dados do conselho ao qual está vinculado, incluindo nome, sigla, descrição, contatos e configurações de notificação. Apenas as configurações de nível municipal (município, código IBGE, cor do portal) estão fora do seu alcance.
