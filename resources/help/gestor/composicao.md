---
title: "Gerenciar a composição do conselho"
description: "Como adicionar membros, encerrar mandatos, promover cargos e controlar os dados públicos da composição do seu conselho municipal."
secao: "Gestão do Conselho"
topico: "Composição"
---

## O que é a composição do conselho

A **composição** é o conjunto de membros com mandato ativo em um conselho municipal. Ela representa quem ocupa formalmente os cargos do conselho em um determinado período, com datas de início e fim de mandato, decreto de nomeação e cargo exercido.

É importante distinguir dois cadastros separados:

- **Conselheiro**: a pessoa (nome, CPF, contato, histórico) — cadastrada uma vez e pode participar de vários conselhos ao longo do tempo.
- **Composição**: o vínculo entre essa pessoa e um cargo específico, com datas e decreto — representa o mandato.

> 💡 Um mesmo conselheiro pode estar na composição de mais de um conselho ao mesmo tempo, ou aparecer em mandatos diferentes no mesmo conselho em períodos distintos.

---

## Cargos disponíveis

Ao incluir um membro na composição, você deve selecionar um dos cargos abaixo:

- **PRESIDENTE** — responsável pela direção do conselho. Só pode haver um presidente ativo por vez.
- **VICE-PRESIDENTE** — substitui o presidente em suas ausências.
- **SECRETÁRIO** — responsável pela organização administrativa, atas e convocações. Só pode haver um secretário ativo por vez.
- **MEMBRO TITULAR** — membro pleno com direito a voto nas plenárias.
- **SUPLENTE** — substitui titulares ausentes; em geral não tem voto quando o titular está presente.

---

## Como adicionar um membro à composição

Adicionar um membro envolve dois passos distintos:

1. **Cadastre o Conselheiro (pessoa)** — acesse o menu **Conselheiros** e verifique se a pessoa já existe pelo CPF. Se não existir, clique em **Novo Conselheiro** e preencha nome completo, CPF, e-mail, telefone e demais dados pessoais.
2. **Crie o registro de Composição** — acesse o conselho desejado, clique na aba **Composição** e depois em **Adicionar Membro**. Preencha:
   - **Conselheiro**: busque pelo nome ou CPF
   - **Cargo**: selecione entre os cargos disponíveis
   - **Representação**: entidade ou segmento que o membro representa (ex.: Secretaria de Saúde, Sociedade Civil)
   - **Data de início do mandato**: data em que o mandato entra em vigor
   - **Data de fim do mandato**: data prevista de encerramento
   - **Número do decreto de nomeação**: número do decreto, portaria ou resolução que nomeia o membro
   - **Data do decreto**: data de publicação do ato de nomeação

3. Clique em **Salvar**. O membro aparecerá na lista da composição com status **ATIVO**.

> ⚠ Sempre cadastre o decreto de nomeação corretamente. Esse dado é exibido no portal público e serve como comprovação da legalidade da nomeação.

---

## Como encerrar um mandato

Encerrar um mandato é diferente de excluir o membro. O **histórico deve ser preservado** — o sistema nunca apaga registros de composição.

1. Na lista da composição, localize o membro.
2. Clique no botão **Encerrar mandato** (ícone de calendário com X).
3. Preencha o **motivo do encerramento** (obrigatório). Exemplos: "Fim do mandato", "Renúncia", "Destituição", "Falecimento".
4. Confirme a data de encerramento efetivo.
5. Clique em **Confirmar encerramento**.

O registro passa para status **ENCERRADO** e fica visível nos filtros de histórico, mas não aparece mais na composição atual nem no portal.

> ⚠ Não use o botão **Excluir** para remover membros. Exclusão apaga o histórico permanentemente e pode causar inconsistências em atas, presenças e documentos vinculados.

---

## Como promover um membro para outro cargo

Se um membro titular for eleito presidente ou secretário, use o fluxo de **promoção** em vez de editar diretamente o cargo:

1. Localize o membro na lista da composição.
2. Clique em **Promover**.
3. Selecione o novo cargo (ex.: PRESIDENTE).
4. Informe o ato que fundamenta a promoção (número e data da resolução ou ata de eleição).
5. Confirme.

O sistema vai encerrar automaticamente o cargo anterior e criar um novo registro com o cargo promovido, preservando a continuidade do histórico.

> ⚠ O sistema impede que dois membros fiquem com o cargo PRESIDENTE ou SECRETÁRIO ativos ao mesmo tempo. Se houver outro membro nesses cargos, você precisará encerrar o mandato do anterior antes de promover o novo.

---

## Dados de exibição pública

Para cada membro da composição, existem campos específicos que controlam o que aparece **no portal público**. Esses dados podem ser diferentes das informações pessoais do conselheiro:

- **Nome de exibição**: nome que aparece no portal (pode ser nome social ou abreviado)
- **E-mail público**: e-mail de contato institucional (diferente do e-mail pessoal do conselheiro)
- **Telefone público**: ramal ou telefone institucional

> 💡 Esses campos são opcionais. Se não preenchidos, o portal exibe o nome completo do conselheiro e oculta os demais dados de contato por padrão.

---

## Filtros disponíveis

Na tela de composição você pode filtrar por:

- **Cargo**: exibe apenas membros com o cargo selecionado
- **Situação**: ATIVO (mandatos vigentes) ou ENCERRADO (histórico)
- **Conselho**: útil para usuários com acesso a mais de um conselho

---

## Perguntas frequentes

### Posso ter dois vice-presidentes ao mesmo tempo?
Sim. Não há restrição de quantidade para VICE-PRESIDENTE, MEMBRO TITULAR e SUPLENTE. A restrição de "apenas um ativo" se aplica somente a PRESIDENTE e SECRETÁRIO.

### O conselheiro precisa ter acesso ao sistema para ser incluído na composição?
Não. O cadastro de conselheiro é apenas um registro de pessoa. O acesso ao sistema (login) é uma configuração separada, feita no cadastro de usuários.

### Como vejo o histórico completo de quem já foi membro do conselho?
Na tela de composição, use o filtro **Situação: ENCERRADO** (ou selecione "Todos"). O sistema exibe todos os registros, inclusive os encerrados, em ordem cronológica.

### Posso editar o decreto de nomeação depois de salvar?
Sim. Edite o registro de composição normalmente. Alterações em dados como decreto, datas e representação são registradas no log de auditoria.

### O que acontece com as presenças em reuniões quando um mandato é encerrado?
Nada muda. Os registros de presença ficam vinculados ao histórico da composição na data em que ocorreram. O encerramento do mandato só afeta a situação atual.

### Como sei se a composição está incompleta?
O painel do conselho exibe um alerta quando não há PRESIDENTE ativo. Verifique regularmente se todos os cargos obrigatórios definidos no regimento estão preenchidos.

### Posso importar a composição de uma planilha?
Por enquanto, o cadastro é feito manualmente. Caso precise cadastrar muitos membros de uma vez, entre em contato com o suporte para verificar a disponibilidade de importação em lote.
