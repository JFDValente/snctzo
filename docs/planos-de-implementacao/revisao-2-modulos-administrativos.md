# Plano de implementação — Revisão 2 dos módulos administrativos

## Objetivo e proteções

Implementar a [Revisão 2](../revisao-2-modulos-administrativos.md) com
Laravel, Blade, CSS e JavaScript nativos. Cada tarefa corresponde a um commit.

- Não remover registros existentes de presença.
- Não registrar senhas em código, Git, docs ou `.env`.
- Preservar streaming, BOM, `;` e proteção contra fórmulas nos CSVs.
- Restringir o relatório de presença a `acesso.modulo:atividades`.
- Não apresentar `/inscricao-atrasada` como mecanismo de segurança.

## Fase 1 — Presença

### 1.1 — Aplicar ausência automática

- **Requisito:** RF-R2-01.
- **Modificar:** `apps/web/app/Actions/Presencas/ConfirmarPeriodoDePresenca.php`.
- **Modificar:** `apps/web/app/Http/Requests/Admin/ConfirmarPresencaRequest.php`, se necessário.
- **Decisão:** completar, antes da validação e transação, todo aluno ou
  professor vinculado que não veio no envio com status `ausente`.
- **Aceite:** confirmação parcial persiste ausências automaticamente em criação
  e edição.
- **Verificar:** confirmar período com marcações parciais e consultar as duas
  tabelas de presença.

### 1.2 — Ajustar interface de presença

- **Requisitos:** RF-R2-02, RF-R2-03 e RF-R2-05.
- **Modificar:** `apps/web/resources/views/admin/presencas/show.blade.php`.
- **Modificar:** `apps/web/resources/views/admin/presencas/index.blade.php`.
- **Modificar:** `apps/web/resources/views/admin/presencas/partials/cartao-participante.blade.php`.
- **Modificar:** `apps/web/resources/js/presencas/participante-tardio.js`.
- **Modificar:** `apps/web/resources/css/admin.css`.
- **Decisão:** pré-selecionar presente após inclusão assíncrona, exibir
  `(adicionado)`, destacar data/turno e mover Situação para a primeira coluna.
- **Aceite:** marcações existentes são preservadas, novo cartão vem presente e
  a tela funciona em desktop e mobile.
- **Verificar:** incluir aluno/professor, confirmar e revisar a listagem em
  viewport desktop e mobile.

### 1.3 — Provisionar o quarto operador

- **Requisito:** RF-R2-04.
- **Modificar:** `apps/web/app/Console/Commands/ProvisionarOperadoresDePresenca.php`.
- **Padrão:** seguir o provisionamento interativo de `SNCTZO_01` a `SNCTZO_03`.
- **Decisão:** adicionar somente `SNCTZO_04`; senha solicitada e confirmada no
  terminal.
- **Aceite:** comando cria ou atualiza o operador sem e-mail, com perfil
  `operador_presenca`.
- **Verificar:** executar localmente e confirmar acesso a presenças e bloqueio
  de `/admin/atividades`.

## Fase 2 — Relatório de presença

### 2.1 — Gerar dados CSV por período e participante

- **Requisito:** RF-R2-06.
- **Modificar:** `apps/web/app/Http/Controllers/Admin/AtividadeController.php`.
- **Consultar como padrão:** `apps/web/app/Support/Presencas/CalendarioDePresencas.php`, `apps/web/app/Models/PeriodoDePresenca.php`, `apps/web/app/Models/PresencaAluno.php` e `apps/web/app/Models/PresencaProfessor.php`.
- **Decisão:** percorrer atividades, datas/turnos aplicáveis e participantes;
  emitir `Não registrado` quando não houver marcação.
- **Aceite:** cada linha contém atividade, período, participante, status,
  flag de adição e dados de última confirmação quando existentes.
- **Verificar:** baixar CSV com períodos confirmado, pendente e participante
  adicionado; abrir em planilha e validar colunas/codificação.

### 2.2 — Expor relatório no módulo de atividades

- **Requisito:** RF-R2-06.
- **Modificar:** `apps/web/routes/web.php`.
- **Modificar:** `apps/web/resources/views/admin/atividades/index.blade.php`.
- **Decisão:** criar rota nomeada e botão **Exportar relatório de presença** ao
  lado da exportação já existente, com `acesso.modulo:atividades`.
- **Aceite:** administrador baixa ambos CSVs; operador é bloqueado no backend.
- **Verificar:** `php artisan route:list`, download autenticado e tentativa com
  perfil operador.

## Fase 3 — Lista de atividades e inscrições

### 3.1 — Ordenar atividades alfabeticamente

- **Requisito:** RF-R2-07.
- **Modificar:** `apps/web/app/Http/Controllers/Admin/AtividadeController.php`.
- **Modificar:** `apps/web/resources/views/admin/atividades/index.blade.php`.
- **Decisão:** ordenar por `nome` e `id`, mantendo paginação de 20 itens.
- **Aceite:** ordem alfabética estável e texto da tela atualizado.
- **Verificar:** conferir atividades em páginas distintas e empate de nomes.

### 3.2 — Mover formulário à rota aceita

- **Requisito:** RF-R2-08.
- **Modificar:** `apps/web/routes/web.php`.
- **Modificar:** `apps/web/resources/views/inscricoes/create.blade.php` e
  scripts que consumam rotas nomeadas, se necessário.
- **Criar:** `apps/web/resources/views/inscricoes/encerradas.blade.php`.
- **Modificar:** testes de feature da raiz e inscrições.
- **Decisão:** migrar formulário, submissão, sucesso, catálogo e busca a
  `/inscricao-atrasada`; tornar rotas antigas apenas informativas e impedir
  persistência no `POST /inscricoes`.
- **Aceite:** o fluxo completo funciona só no novo prefixo; a rota antiga não
  cria registros.
- **Verificar:** fluxo e2e na nova URL, mensagem em `/inscricoes` e POST legado
  sem alteração no banco.

## Fase 4 — Verificação e publicação

### 4.1 — Validar a revisão integrada

- **Requisitos:** RF-R2-01 a RF-R2-08.
- **Modificar, se necessário:** `docs/publicacao-hostinger.md` para indicar o
  quarto operador e os caches pós-deploy.
- **Verificar:** `./vendor/bin/pint --dirty`, `php artisan test`,
  `php artisan route:cache`, `php artisan view:cache` e `npm run build` em
  `apps/web`.
- **Smoke tests:** ausência automática, inclusão tardia, permissões, os dois
  CSVs, `/inscricao-atrasada` e mensagem de encerramento em `/inscricoes`.

## Sequência

`1.1 → 1.2 → 1.3 → 2.1 → 2.2 → 3.1 → 3.2 → 4.1`.

Fases 2 e 3 podem ser desenvolvidas em paralelo após 1.3, mas a Fase 4 deve
validar toda a revisão em conjunto.
