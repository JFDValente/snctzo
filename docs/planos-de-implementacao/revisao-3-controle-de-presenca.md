# Plano de implementação — Revisão 3 do controle de presença

## Objetivo

Implementar a exclusividade de presença `presente` por participante, data e turno, conforme a [Revisão 3](../revisao-3-controle-de-presenca.md), sem alterar o fluxo público, o calendário ou os dados já confirmados.

## Fase 1 — Serviço de detecção e integridade transacional

### [ ] 1.1 [RF-R3-01 a RF-R3-03] Detectar conflitos no backend

**Criar:** ação ou serviço em `apps/web/app/Actions/Presencas/`, caso a responsabilidade não permaneça coesa em `ConfirmarPeriodoDePresenca`.

**Modificar:** `apps/web/app/Actions/Presencas/ConfirmarPeriodoDePresenca.php`.

**Escopo:** depois de completar ausências e validar os participantes da atividade, selecionar somente IDs com status `presente`. Consultar `presencas_alunos` e `presencas_professores` por data e turno, por meio de `periodos_de_presenca`, excluindo a atividade em edição e atividades com `deleted_at` preenchido.

**Regra:** aluno compara somente com aluno; professor compara somente com professor. `ausente` não participa da consulta.

**AC:** nenhuma presença duplicada em atividade distinta é criada; editar a própria atividade não gera conflito.

**Commit:** `feat(presencas): Detecta conflito por turno`.

### [ ] 1.2 [RF-R3-01, RF-R3-07] Serializar confirmações concorrentes

**Modificar:** `apps/web/app/Actions/Presencas/ConfirmarPeriodoDePresenca.php`.

**Escopo:** mover a detecção para dentro da transação já existente. Bloquear os registros de `alunos` e `professores` que serão marcados presentes, sempre em ordem crescente de ID e com ordem de tipo fixa. Só depois consultar conflitos, criar ou atualizar o período e substituir as marcações.

**AC:** duas confirmações concorrentes para a mesma pessoa e período não podem ambas ser persistidas como `presente`.

**Commit:** `fix(presencas): Serializa presença por participante`.

## Fase 2 — Erros claros e preservação da ficha

### [ ] 2.1 [RF-R3-04 a RF-R3-06] Retornar conflitos estruturados

**Modificar:** ação de confirmação e, se necessário, `apps/web/app/Http/Controllers/Admin/PresencaController.php`.

**Escopo:** retornar `ValidationException` no campo de participantes com uma mensagem por conflito contendo tipo, nome, outra atividade, data e turno. Não alterar período, presenças, autor ou instante quando existir erro.

**AC:** a resposta preserva `old input`; o retorno identifica todos os conflitos do envio, não apenas o primeiro.

**Commit:** `feat(presencas): Explica conflito de presença`.

### [ ] 2.2 [RF-R3-06] Destacar conflito na interface

**Modificar:** `apps/web/resources/views/admin/presencas/show.blade.php`, partials de cartões de participante, JavaScript e CSS de administração.

**Escopo:** abrir o diálogo de erro já existente com a lista completa de conflitos, preservar seleções e aplicar classe visual aos cartões envolvidos. O comportamento precisa ser legível e tocável em telas pequenas.

**AC:** operador entende quem conflita, em qual atividade e como corrigir; nenhuma seleção é perdida após fechar o diálogo.

**Commit:** `style(presencas): Destaca conflito por turno`.

## Fase 3 — Testes e validação

### [ ] 3.1 [RF-R3-01 a RF-R3-07] Cobrir fluxos críticos

**Criar ou modificar:** testes de feature para confirmação de presença.

**Casos mínimos:**

1. mesmo aluno presente em atividades distintas, mesma data/turno: bloquear;
2. mesmo professor presente em atividades distintas, mesma data/turno: bloquear;
3. presente em turnos ou datas diferentes: permitir;
4. ausente em outra atividade: permitir;
5. edição da própria atividade: permitir;
6. atividade excluída virtualmente: não bloquear;
7. falha não altera presença previamente confirmada nem metadados do período.

**Verificar:** Pint, suíte Laravel, build, cache de views, rotas e configuração.

**Commit:** `test(presencas): Cobre exclusividade por turno`.

## Cobertura

| Requisito | Tasks |
|---|---|
| RF-R3-01 | 1.1, 1.2, 3.1 |
| RF-R3-02 | 1.1, 3.1 |
| RF-R3-03 | 1.1, 3.1 |
| RF-R3-04 | 2.1, 3.1 |
| RF-R3-05 | 2.1, 3.1 |
| RF-R3-06 | 2.1, 2.2, 3.1 |
| RF-R3-07 | 1.2, 3.1 |
