# Revisão 2 — Módulos administrativos e acesso às inscrições

## Objetivo

Esta revisão complementa o [controle de presença](./controle-de-presenca.md)
e o [gerenciamento administrativo](./admin-gerenciamento.md). Em conflito,
as decisões abaixo prevalecem sobre documentos e revisões anteriores.

## Controle de presença

### RF-R2-01 — Ausência automática

Ao confirmar um período, todo participante sem `Presente` ou `Ausente`
selecionado receberá `Ausente` automaticamente. A falta de seleção manual não
recusará a confirmação. A regra vale para primeira confirmação e edição: numa
edição, toda opção desmarcada será gravada como ausência.

### RF-R2-02 — Participante adicionado

Após a inclusão tardia, o cartão inserido na ficha atual virá com `Presente`
selecionado. O status será persistido somente ao confirmar o período. O
rótulo visível mudará de `(cadastro tardio)` para `(adicionado)` para aluno e
professor; a flag técnica `cadastrado_tardiamente` permanece no banco.

### RF-R2-03 — Identificação do período

Data e turno terão destaque visual no cabeçalho da ficha de presença em
desktop e mobile. O nome da atividade continua sendo o título principal.

### RF-R2-04 — Operador adicional

Será provisionado `SNCTZO_04` como `operador_presenca`, sem e-mail. Terá
login por username, entrada em `/admin/presencas` e nenhum acesso ao módulo de
atividades. A senha será solicitada apenas pelo comando Artisan interativo e
não será incluída em código, Git, docs ou `.env`.

### RF-R2-05 — Listagem do turno

Na listagem de atividades de presença, **Situação** será a primeira coluna e
os valores `Pendente` e `Confirmado` aparecerão em negrito. O filtro de
professor responsável e as demais colunas permanecem.

## Módulo de atividades

### RF-R2-06 — Relatório de presença em CSV

O módulo de atividades terá uma segunda exportação: **Exportar relatório de
presença**. A rota será exclusiva de administradores; operadores não a
acessarão diretamente por URL.

O arquivo usará UTF-8 com BOM, separador `;`, proteção contra fórmulas e
streaming, sem arquivo temporário. Uma linha representará:

`atividade + data + turno + participante`.

Ele abrangerá todos os turnos configurados nas datas em que a atividade está
agendada, mesmo sem confirmação. Ausência de marcação será exportada como
**Não registrado**, inclusive para participante adicionado posteriormente.

| Coluna | Conteúdo |
|---|---|
| Atividade | nome da atividade |
| Professor responsável | nome do responsável |
| Unidade acadêmica | instituição do curso principal |
| Curso principal | curso da atividade |
| Data | `dd/mm/aaaa` |
| Turno | manhã, tarde ou noite |
| Tipo | aluno ou professor |
| Participante | nome completo |
| Status | Presente, Ausente ou Não registrado |
| Participante adicionado | Sim ou Não, conforme o vínculo |
| Confirmado em | data/hora da última confirmação, se existir |
| Confirmado por | usuário da última confirmação, se existir |

A ordenação será por atividade, data, sequência do turno e participante, com
ID técnico como desempate.

### RF-R2-07 — Ordem da lista administrativa

`/admin/atividades` será ordenada alfabeticamente pelo nome da atividade, com
`id` como desempate. A paginação permanece em 20 registros por página e o
texto explicativo da tela refletirá a nova ordem.

### RF-R2-08 — Formulário público encerrado

As rotas `/` e `/inscricoes` não exibirão mais o formulário e apresentarão:

> O prazo de inscrições já foi encerrado

`/` pode redirecionar a `/inscricoes`, desde que preserve a mensagem.

O formulário continuará público, sem autenticação, em `/inscricao-atrasada`.
Exibição, submissão, sucesso, catálogo e busca de professor usarão esse novo
prefixo. O `POST /inscricoes` legado não poderá gravar inscrição.

Essa URL não é controle de acesso: quem a conhecer poderá utilizá-la. Esse
risco foi aceito para reduzir o esforço. Período de inscrição, limite por IP,
validações e CSRF continuam em vigor.

## Critérios de aceite

1. Confirmação atribui `Ausente` a toda opção não marcada.
2. Novo participante aparece com `Presente` selecionado, sem gravar antes da
   confirmação.
3. Os quatro operadores acessam somente presença.
4. Situação abre a tabela de presença e aparece em negrito.
5. O CSV inclui registros confirmados e não registrados, uma linha por
   participante e período aplicável.
6. A lista administrativa é alfabética e segue paginada.
7. `/` e `/inscricoes` não submetem inscrições; apenas
   `/inscricao-atrasada` conclui o fluxo.

## Fora do escopo

- Convite, token, e-mail ou senha para proteger o formulário;
- histórico de alterações de presença;
- filtros para exportação de presença;
- edição/exclusão de atividades, participantes ou presenças;
- gestão visual de usuários e calendário.

## Plano técnico

Ver [Plano da Revisão 2](./planos-de-implementacao/revisao-2-modulos-administrativos.md).
