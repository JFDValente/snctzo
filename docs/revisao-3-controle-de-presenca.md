# Revisão 3 — Exclusividade de presença por turno

## Objetivo

Impedir que uma mesma pessoa receba o status **Presente** em mais de uma atividade no mesmo dia e turno. A regra reduz registros simultâneos incompatíveis e orienta claramente o operador sobre a atividade que precisa ser corrigida.

Esta revisão complementa [Controle de presença](./controle-de-presenca.md). Em caso de conflito, as decisões deste documento prevalecem.

## Regra funcional

### RF-R3-01 — Exclusividade de presença

Um participante só poderá ter uma marcação `presente` por combinação de data e turno, ainda que esteja vinculado a várias atividades.

Exemplo: se José estiver `presente` na Atividade 1 em 20/10, turno da manhã, ele não poderá ser confirmado como `presente` na Atividade 2 para o mesmo período. Para confirmar a Atividade 2, o operador deverá alterar uma das marcações para `ausente`.

### RF-R3-02 — Estados que bloqueiam

Somente o status `presente` bloqueia outra atividade. Os estados `ausente` e `não registrado` não causam conflito.

### RF-R3-03 — Identidade considerada

A regra compara o mesmo registro persistido e o mesmo tipo de participante:

- aluno com o mesmo `aluno_id`;
- professor com o mesmo `professor_id`.

Ela não compara nomes e não cruza aluno com professor. Não há uma identidade compartilhada segura entre essas tabelas; usar o nome causaria bloqueios de homônimos ou de cadastros distintos.

## Fluxo de confirmação

1. O operador seleciona `Presente` ou `Ausente` e confirma a ficha da atividade.
2. O sistema completa como `Ausente` os campos não selecionados, conforme a regra atual.
3. Antes de alterar qualquer marcação, o backend procura presenças `presente` confirmadas para os alunos e professores selecionados, no mesmo dia e turno, em outras atividades visíveis.
4. Se não houver conflito, a confirmação segue como hoje.
5. Se houver conflito, a transação é desfeita integralmente. Nenhuma marcação, autor ou instante de confirmação da atividade em edição será alterado.

Ao editar uma atividade, a própria atividade é excluída da busca de conflito. Assim, manter uma presença já registrada nela continua permitido.

Atividades excluídas virtualmente não participam da verificação, pois já não fazem parte da operação normal da aplicação.

## Retorno ao operador

Quando a confirmação for bloqueada, a ficha permanece aberta e preserva as escolhas já feitas. Um diálogo de erro destacado exibirá uma linha para cada conflito, com:

- tipo e nome do participante;
- nome da outra atividade;
- data e turno conflitantes;
- orientação para manter `Presente` em apenas uma atividade.

Exemplo:

> Aluno José Silva já está marcado como presente na atividade “Atividade 2” em 20/10, no turno da manhã. Altere uma das duas atividades para ausente e confirme novamente.

Os cartões dos participantes conflitantes também receberão destaque visual, sem apagar os demais estados escolhidos.

## Concorrência e integridade

A verificação será obrigatoriamente feita no backend dentro da transação de confirmação. Antes da consulta de conflitos, os registros dos participantes marcados como presentes serão bloqueados em ordem estável de tipo e ID.

Essa serialização impede que dois operadores confirmem simultaneamente a mesma pessoa como presente em atividades diferentes para o mesmo período. A camada JavaScript pode melhorar a apresentação, mas não será usada como mecanismo de segurança ou validação definitiva.

## Critérios de aceite

1. Um aluno não pode ficar `presente` em duas atividades no mesmo dia e turno.
2. Um professor não pode ficar `presente` em duas atividades no mesmo dia e turno.
3. `Ausente` e `Não registrado` não bloqueiam confirmação em outra atividade.
4. Editar a mesma atividade não produz falso conflito contra ela própria.
5. Um conflito não persiste alterações parciais na atividade em edição.
6. O erro informa participante, outra atividade, data, turno e ação necessária.
7. Submissões concorrentes para a mesma pessoa não conseguem criar duas presenças `presente` no mesmo período.

## Fora do escopo

- identificação única entre alunos e professores;
- validação por nome ou semelhança de texto;
- bloqueio de uma pessoa como `ausente` em múltiplas atividades;
- agenda ou distribuição prévia de participantes entre atividades;
- histórico adicional de tentativas bloqueadas.

## Plano técnico

Ver [Plano da Revisão 3](./planos-de-implementacao/revisao-3-controle-de-presenca.md).
