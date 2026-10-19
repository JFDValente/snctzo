# Controle de presença — SNCTZO 2026

## 1. Objetivo e escopo

Este documento define o módulo administrativo de controle de presença dos
participantes das atividades da SNCTZO 2026. O módulo será acessível apenas
por usuários autenticados do gerenciamento, no mesmo subdomínio da aplicação.

O objetivo é permitir que vários usuários registrem, confirmem e editem a
presença de participantes por atividade, data e turno, com prioridade para o
uso em smartphones e tablets, sem perder a usabilidade no desktop.

O módulo não cria atribuição de atividades por usuário: qualquer usuário
autenticado poderá consultar e registrar a presença de qualquer atividade.

## 2. Decisões confirmadas

| Tema | Decisão |
|---|---|
| Controle de acesso | Reutilizar autenticação administrativa existente; todas as rotas exigem `auth` |
| Participantes exibidos | Apenas alunos e professores vinculados à atividade; o responsável aparece somente se também for participante |
| Estados | `Não registrado`, `Presente` e `Ausente` |
| Confirmação | Toda gravação ou edição exige confirmação explícita do usuário |
| Preenchimento | A primeira confirmação de um período exige `Presente` ou `Ausente` para todos os participantes existentes naquele momento |
| Edição | Permitida a qualquer tempo, para qualquer data e turno configurados |
| Concorrência | Avisar que houve confirmação posterior por outro usuário, mas permitir salvar; a última confirmação prevalece |
| Participante tardio | Integra a atividade, recebe marcação especial no vínculo e participa dos turnos futuros; em turnos anteriores aparece como `Não registrado` |
| Duplicidade tardia | Bloquear participante com mesmo tipo e nome normalizado já vinculado à mesma atividade |
| Histórico completo | Fora do escopo; será mantido o autor e instante da última confirmação do período |

## 3. Calendário e turnos

O módulo trabalha com os turnos `manhã`, `tarde` e `noite`. Nem todos os
turnos precisam existir em todos os dias.

Para a edição de 2026, o calendário inicial será:

| Data | Turnos disponíveis |
|---|---|
| 20/10/2026 | Manhã, tarde e noite |
| 21/10/2026 | Manhã e tarde |

O calendário não terá interface de administração nesta entrega. Ele deverá
ficar em configuração do backend, permitindo alterar dias e turnos sem mudar o
fluxo de presença. A aplicação não criará uma entidade de evento nesta fase.

Uma atividade só aparece no filtro de uma data quando ela estiver agendada para
aquele dia (`participa_dia_20` ou `participa_dia_21`). Para cada atividade
visível, todos os turnos configurados para a data estarão disponíveis.

## 4. Navegação e interface

### 4.1 Entrada do módulo

O menu administrativo terá o item **Controle de presença**, apontando para
`/admin/presencas`.

A tela inicial apresentará, antes da listagem:

1. seletor de data;
2. seletor de turno, limitado aos turnos configurados na data selecionada;
3. listagem das atividades agendadas para aquela data.

O turno é escolhido antes de abrir uma atividade. Assim, cada tela de registro
trata exclusivamente de uma combinação de atividade, data e turno.

Em celular e tablet, os filtros devem ficar no topo e ser fáceis de operar com
toque. A lista de atividades deve usar cartões ou tabela responsiva, sem exigir
rolagem horizontal para executar o registro.

### 4.2 Indicadores na listagem

Cada atividade exibirá indicadores apenas dos turnos que pertencem à data da
atividade. O indicador de cada período terá um dos estados:

- **Pendente**: nunca houve confirmação para o período;
- **Confirmado**: há uma confirmação registrada;
- **Atualizado**: opcionalmente usado pela interface para destacar a última
  confirmação depois de uma edição, sem alterar o significado de confirmado.

Além do filtro de data exigido, a listagem pode mostrar os indicadores de todos
os períodos aplicáveis para permitir uma visão rápida do andamento. A ação de
abrir o registro preservará a data e o turno escolhidos.

### 4.3 Registro de um período

A página de registro identificará de forma destacada:

- nome da atividade;
- data selecionada;
- turno selecionado;
- situação da última confirmação, com usuário e data/hora quando houver.

Cada participante será apresentado com nome, tipo e controles grandes de toque
para escolher `Presente` ou `Ausente`. Antes da primeira confirmação, todos
começam como `Não registrado`.

No fim da página haverá a ação **Confirmar presença do turno**. Ela deve abrir
uma confirmação final e só então persistir as marcações. Se existir participante
obrigatório sem decisão, a confirmação será recusada e os campos pendentes
serão destacados.

## 5. Fluxos funcionais

### 5.1 Registrar a presença pela primeira vez

1. O usuário autenticado seleciona data e turno.
2. O sistema lista somente atividades agendadas naquela data.
3. O usuário abre uma atividade e vê participantes daquele período como `Não
   registrado`.
4. O usuário escolhe `Presente` ou `Ausente` para todos os participantes.
5. O usuário confirma o registro.
6. O sistema persiste todas as marcações de forma atômica, grava autor e
   instante da confirmação e atualiza o indicador da listagem para
   **Confirmado**.

### 5.2 Editar uma presença confirmada

1. O usuário seleciona o mesmo dia, turno e atividade.
2. O sistema carrega as últimas marcações confirmadas e os dados da última
   confirmação.
3. O usuário modifica uma ou mais marcações e confirma novamente.
4. O sistema substitui o estado daquele período pela nova confirmação, atualiza
   autor e instante e mantém somente o estado mais recente como vigente.

### 5.3 Uso simultâneo

Ao abrir a tela, o sistema registra a versão ou instante da última confirmação
conhecida. Antes de salvar, se outra confirmação tiver ocorrido nesse intervalo,
o sistema exibirá um aviso com o usuário e a data/hora mais recentes.

O aviso não bloqueia a operação. O usuário poderá confirmar mesmo assim e a
última confirmação persistida será a vigente. A interface deve deixar esse
comportamento claro para evitar sobrescritas acidentais.

### 5.4 Incluir participante tardio

Na tela do período, o usuário poderá escolher **Adicionar participante** e
informar somente:

- tipo: `Aluno` ou `Professor`;
- nome completo.

Ao confirmar a inclusão, o sistema:

1. normaliza o nome e bloqueia duplicidade de tipo e nome na mesma atividade;
2. cria o aluno ou professor quando necessário;
3. cria o vínculo com a atividade;
4. marca esse vínculo como `cadastrado_tardiamente`;
5. inclui a pessoa nos turnos futuros ainda não registrados.

Em períodos já confirmados antes da inclusão, o participante tardio é exibido
como `Não registrado`. A inclusão não invalida nem torna pendente a confirmação
anterior. O usuário pode, contudo, editar um período passado e definir
manualmente `Presente` ou `Ausente` para esse participante.

Em uma primeira confirmação de período, todos os participantes já existentes
na atividade devem receber decisão. A exceção é o participante tardio incluído
depois de uma confirmação daquele período: em período passado ele pode
permanecer como `Não registrado`.

## 6. Proposta de persistência

### 6.1 Vínculos de participantes

As tabelas de vínculo já existentes receberão uma flag com padrão `false`:

- `atividade_aluno.cadastrado_tardiamente`;
- `atividade_professor.cadastrado_tardiamente`.

A flag pertence ao vínculo, e não ao aluno ou professor global. Uma mesma
pessoa pode ser cadastrada tardiamente em uma atividade e regular em outra.

### 6.2 Período de presença

Será criada uma tabela conceitual de confirmação por atividade, data e turno,
com ao menos:

| Campo | Finalidade |
|---|---|
| `id` | identificador técnico |
| `atividade_id` | atividade controlada |
| `data` | data configurada do evento |
| `turno` | manhã, tarde ou noite |
| `confirmado_por_id` | usuário da última confirmação |
| `confirmado_em` | instante da última confirmação |
| `created_at` / `updated_at` | suporte à concorrência e auditoria mínima |

Deve existir unicidade em `(atividade_id, data, turno)`.

### 6.3 Marcações individuais

As marcações devem preservar chaves estrangeiras reais para alunos e
professores. Por isso, a implementação poderá manter tabelas distintas para
alunos e professores, relacionadas ao período de presença, com:

| Campo | Finalidade |
|---|---|
| referência ao período de presença | identifica atividade, data e turno |
| referência ao aluno ou professor | identifica o participante |
| `status` | `presente` ou `ausente` |
| timestamps | rastreio técnico da última alteração |

Não é necessário persistir uma linha para `Não registrado`: esse estado é
derivado da ausência de marcação para um participante vinculado. Isso permite
mostrar corretamente participantes tardios nos períodos anteriores.

Cada tabela terá unicidade entre período e participante, impedindo duas
marcações do mesmo tipo para a mesma pessoa no mesmo período.

### 6.4 Atomicidade

A confirmação de um período será transacional: criar ou atualizar o período,
substituir suas marcações vigentes e atualizar autor/instante deve ocorrer como
uma única operação. Em caso de falha, nenhuma marcação parcial poderá ficar
persistida.

## 7. Rotas propostas

| Método | Rota | Nome sugerido | Finalidade |
|---|---|---|---|
| `GET` | `/admin/presencas` | `admin.presencas.index` | Filtros de data/turno e atividades elegíveis |
| `GET` | `/admin/presencas/{atividade}` | `admin.presencas.show` | Exibe um único período da atividade selecionada |
| `POST` | `/admin/presencas/{atividade}/confirmar` | `admin.presencas.confirmar` | Confirma ou substitui as marcações do período |
| `POST` | `/admin/presencas/{atividade}/participantes` | `admin.presencas.participantes.store` | Inclui participante tardio |

Data e turno deverão ser parâmetros validados da consulta ou do corpo da
requisição. Todas as rotas ficarão no grupo `auth` já aplicado ao admin.

## 8. Regras de validação

- A data e o turno precisam existir no calendário configurado.
- A atividade deve estar agendada para a data selecionada.
- O turno não pode ser usado se não estiver configurado para aquela data.
- Na primeira confirmação, cada participante existente deve estar marcado como
  presente ou ausente.
- Não permitir duplicidade de aluno ou professor no mesmo período.
- Não permitir participante tardio do mesmo tipo e nome normalizado já
  vinculado à atividade.
- A inclusão tardia exige nome completo e tipo válido.
- Toda confirmação deve registrar o usuário autenticado e o instante.
- A edição exige a mesma confirmação explícita que o primeiro registro.

## 9. Fora do escopo

- Perfis, papéis ou atribuição de atividades a usuários;
- exclusão de participantes ou de registros de presença;
- histórico completo de cada edição;
- justificativa de ausência;
- leitura por QR code, matrícula, biometria ou integração externa;
- funcionamento offline;
- emissão de certificados, livros ou relatórios derivados da presença;
- configuração de dias e turnos por interface administrativa;
- nova entidade de evento ou suporte a múltiplas edições.

## 10. Dependências futuras

A flag `cadastrado_tardiamente` deverá ser considerada por funcionalidades
futuras de certificado e livro, para que esses participantes possam ser
tratados conforme a regra de negócio que vier a ser definida.

O calendário configurável deve ser reavaliado quando houver uma tela de
administração do evento ou suporte a edições futuras da SNCTZO.

## 11. Plano técnico

As tasks, caminhos previstos, critérios de aceite e verificações estão no
[Plano de implementação do controle de presença](./planos-de-implementacao/controle-de-presenca.md).

As decisões posteriores estão em [Revisão 1](./revisao-1-controle-de-presenca.md),
[Revisão 2](./revisao-2-modulos-administrativos.md) e
[Revisão 3](./revisao-3-controle-de-presenca.md).
