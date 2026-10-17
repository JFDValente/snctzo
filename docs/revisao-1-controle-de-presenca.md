# Revisão 1 — Módulo de controle de presença

## Objetivo

Esta revisão prepara o módulo para operadores dedicados ao lançamento de
presença, aprimora a listagem e torna a inclusão tardia e os retornos de
sucesso/erro mais adequados para desktop e mobile.

Ela complementa [Controle de presença](./controle-de-presenca.md). Em caso de
conflito, as decisões abaixo prevalecem.

## Acesso, usuários e rotas

### RF-R1-01 — Perfis

| Perfil | Acesso |
|---|---|
| `administrador` | Todos os recursos administrativos atuais |
| `operador_presenca` | Somente presenças e encerramento da própria sessão |

Operadores não poderão acessar atividades, exportação CSV ou detalhe de
atividade mesmo informando a URL diretamente. O menu apenas complementa essa
proteção: o bloqueio será aplicado pelo backend.

Os operadores iniciais terão os usernames `SNCTZO_01`, `SNCTZO_02` e
`SNCTZO_03`. Senhas serão provisionadas fora do código, Git, `.env` e docs
versionadas, sempre como hash do Laravel.

### RF-R1-02 — Login por e-mail ou usuário

O login terá o campo **E-mail ou usuário**. Administradores existentes seguem
usando e-mail; operadores usam username. Username será único e não sensível a
maiúsculas/minúsculas. E-mail poderá ser nulo para usuários operacionais.

Sem URL pretendida, administrador segue para `/admin/atividades` e operador
segue para `/admin/presencas`. Uma URL pretendida autorizada sempre prevalece.

### RF-R1-03 — Atalho

`/admin/presencas` continua sendo a rota canônica. A rota `/presenca` será um
atalho protegido: visitante deslogado vai ao login e, após autenticação,
retorna a `/admin/presencas`; usuário autenticado é redirecionado diretamente.

## Listagem de atividades

### RF-R1-04 — Ordenação e dados

Para a data e turno selecionados, a lista será ordenada alfabeticamente pelo
nome da atividade, com ID como desempate. A coluna **Unidade acadêmica** será
substituída por **Professor responsável**. O estado `Pendente` ou `Confirmado`
permanece.

### RF-R1-05 — Busca por responsável

O filtro local será renomeado para **Buscar professor responsável**. Ele
filtrará automaticamente pelo nome parcial, ignorando caixa e acentos, sem
nova requisição ao servidor.

## Participante tardio

### RF-R1-06 — Formulário sob demanda

O formulário não ficará visível inicialmente. Antes de **Confirmar presença do
turno**, haverá **Adicionar participante**. O botão abrirá campos de Tipo
(aluno ou professor) e Nome completo.

No desktop, será um diálogo central; em mobile, um painel amplo adequado ao
toque, ancorado na base ou quase em largura total.

### RF-R1-07 — Inclusão assíncrona

A inclusão ocorrerá sem recarregar a ficha. Após sucesso, a pessoa será
adicionada à lista como `Aluno (cadastro tardio)` ou `Professor (cadastro
tardio)`. As marcações já feitas permanecem intactas e a nova pessoa deverá
receber `Presente` ou `Ausente` antes da confirmação do turno.

Erros de nome ausente ou duplicidade aparecem no próprio diálogo e não apagam
marcações da ficha.

## Retorno da confirmação

### RF-R1-08 — Sucesso

Após salvar, a própria ficha exibirá diálogo de sucesso. O usuário irá à lista
do mesmo dia e turno somente quando clicar em **OK**; não haverá redirecionamento
automático.

### RF-R1-09 — Erro

Quando houver falha, a ficha permanecerá aberta. Um diálogo exibirá o erro;
ao fechá-lo, o usuário corrigirá os campos preservados na mesma tela. Campos
inválidos devem ficar destacados.

Sucesso e erro usarão componentes visuais da aplicação, não `alert` nem
`confirm` nativos.

## Regras e limites

- Todas as rotas de presença permanecem protegidas por `auth`.
- O atalho deve preservar a URL pretendida na sessão.
- Inclusão assíncrona deve respeitar CSRF, autorização e as validações atuais.
- Não alterar o fluxo público de `/inscricoes`.
- Fora do escopo: novos perfis, gestão visual de usuários, recuperação de
  senha, atribuição de atividades, exclusões e regras de calendário.

## Critérios de aceite

1. Operador acessa apenas presença e inicia nela após login.
2. Administrador mantém todos os acessos atuais.
3. `/presenca` funciona deslogado e autenticado.
4. A lista é alfabética, mostra responsável e busca por ele.
5. Inclusão tardia não recarrega nem perde marcações.
6. Sucesso exige OK antes de voltar à lista; erro mantém a ficha corrigível.

## Plano técnico

Ver [Plano da Revisão 1](./planos-de-implementacao/revisao-1-controle-de-presenca.md).
