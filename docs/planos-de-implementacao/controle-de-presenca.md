# Plano de implementação — controle de presença

## 1. Objetivo

Este plano detalha a execução da feature especificada em [Controle de presença](../controle-de-presenca.md). Cada task corresponde a um commit, e as tasks devem ser executadas em ordem.

Consultar antes de implementar: [Modelo de dados](../modelo-de-dados.md), [Gerenciamento administrativo](../admin-gerenciamento.md) e [Decisões estruturais](../decisoes-estruturais.md).

## 2. Contrato de execução

- Usar Laravel, Blade, CSS e JavaScript nativos; não introduzir framework JavaScript, serviço externo, fila ou operação offline.
- Manter `/inscricoes` e os registros existentes intactos, exceto pelas migrations aditivas deste plano.
- Aplicar `auth` a todas as rotas; não criar perfis, atribuições de atividade ou entidade de evento.
- Usar PT-BR e não versionar credenciais, `.env` ou dados reais de presença.
- Cada task exige Pint, build e a verificação manual indicada. Não criar testes automatizados sem decisão posterior.

## 3. Fase 1 — Fundação e persistência

### [ ] 1.1 [RF-001, RF-005, RF-006] Configurar calendário de turnos

**Criar:** `apps/web/app/Support/Presencas/CalendarioDePresencas.php`.

**Modificar:** `apps/web/config/snctzo.php`.

**Padrão:** `apps/web/app/Support/Inscricoes/PeriodoDeInscricao.php`.

**Escopo:** configurar 20/10/2026 com manhã, tarde e noite; 21/10/2026 com manhã e tarde; expor datas, turnos ordenados, rótulos e validação de uma combinação data/turno. Não criar tela de configuração ou usar env vars.

**AC:** combinações fora do calendário são rejeitadas e a ordem é manhã, tarde, noite.

**Verificar:** `php artisan config:clear` e consulta das combinações pelo Tinker.

**Commit:** `feat(presencas): Configura calendário de turnos`.

### [ ] 1.2 [RF-001, RF-002, RF-003, RF-007] Criar estrutura de presença

**Criar:** `apps/web/database/migrations/*_adicionar_cadastro_tardio_a_vinculos_de_atividades.php`, `apps/web/database/migrations/*_create_periodos_de_presenca_table.php`, `apps/web/database/migrations/*_create_presencas_alunos_table.php` e `apps/web/database/migrations/*_create_presencas_professores_table.php`.

**Padrão:** migrations de `atividade_aluno` e `atividade_professor`.

**Escopo:** adicionar `cadastrado_tardiamente` com padrão `false` aos dois vínculos; criar períodos com atividade, data, turno, usuário e instante da última confirmação; criar marcações separadas para aluno e professor, com status `presente` ou `ausente`.

**AC:** unicidade em `(atividade_id, data, turno)` e por período/pessoa; chaves estrangeiras preservam integridade; participantes existentes recebem flag falsa.

**Verificar:** `php artisan migrate`, rollback e nova migration em banco local descartável.

**Commit:** `feat(presencas): Cria estrutura de registros`.

### [ ] 1.3 [RF-001, RF-002, RF-007] Modelar relações Eloquent

**Criar:** `apps/web/app/Models/PeriodoDePresenca.php`, `apps/web/app/Models/PresencaAluno.php` e `apps/web/app/Models/PresencaProfessor.php`.

**Modificar:** `apps/web/app/Models/Atividade.php`, `apps/web/app/Models/Aluno.php`, `apps/web/app/Models/Professor.php` e `apps/web/app/Models/User.php`.

**Padrão:** relações, casts e campos permitidos de `apps/web/app/Models/Atividade.php`.

**AC:** atividade carrega períodos; período carrega autor e marcações; pivôs expõem a flag tardia; não há relação polimórfica sem FK.

**Verificar:** criar e consultar relações e pivôs no Tinker.

**Commit:** `feat(presencas): Modela relações de presença`.

## 4. Fase 2 — Fluxos administrativos

### [ ] 2.1 [RF-004, RF-005, RF-008] Listar atividades por data e turno

**Criar:** `apps/web/app/Http/Controllers/Admin/PresencaController.php` e `apps/web/resources/views/admin/presencas/index.blade.php`.

**Modificar:** `apps/web/routes/web.php`.

**Padrão:** `apps/web/app/Http/Controllers/Admin/AtividadeController.php` e `apps/web/resources/views/admin/atividades/index.blade.php`.

**Escopo:** criar `GET /admin/presencas`; validar filtros no calendário; listar somente atividades agendadas na data; carregar indicadores de presença antecipadamente e preservar filtros nos links.

**AC:** visitante é redirecionado ao login; filtros inválidos são rejeitados; cada indicador mostra Pendente ou Confirmado.

**Verificar:** testar datas, turnos, lista vazia e atividades em um ou dois dias.

**Commit:** `feat(presencas): Lista atividades por turno`.

### [ ] 2.2 [RF-001, RF-004, RF-006, RF-008] Exibir ficha do período

**Criar:** `apps/web/resources/views/admin/presencas/show.blade.php`.

**Modificar:** `apps/web/app/Http/Controllers/Admin/PresencaController.php` e `apps/web/routes/web.php`.

**Padrão:** método `show` de `apps/web/app/Http/Controllers/Admin/AtividadeController.php`.

**Escopo:** criar `GET /admin/presencas/{atividade}` para data/turno válido; exibir atividade, período, última confirmação e participantes vinculados; mostrar marcação vigente ou Não registrado. Responsável só aparece se for participante.

**AC:** período novo não persiste no carregamento; confirmado carrega estado vigente; atividade não agendada para a data retorna erro apropriado.

**Verificar:** conferir período novo, confirmado e com participante tardio em smartphone e desktop.

**Commit:** `feat(presencas): Exibe ficha de turno`.

### [ ] 2.3 [RF-001, RF-002, RF-003, RF-006, RF-008] Confirmar presenças transacionalmente

**Criar:** `apps/web/app/Http/Requests/Admin/ConfirmarPresencaRequest.php` e `apps/web/app/Actions/Presencas/ConfirmarPeriodoDePresenca.php`.

**Modificar:** `apps/web/app/Http/Controllers/Admin/PresencaController.php`, `apps/web/resources/views/admin/presencas/show.blade.php` e `apps/web/routes/web.php`.

**Padrão:** `apps/web/app/Actions/Inscricoes/CriarAtividade.php` e Form Requests de `apps/web/app/Http/Requests/`.

**Escopo:** criar `POST /admin/presencas/{atividade}/confirmar`; validar atividade, data, turno, participantes e status; exigir Presente/Ausente antes da primeira confirmação; salvar período, marcações, autor e instante em uma transação. Edição exige nova confirmação.

**AC:** preenchimento incompleto é recusado; falhas não deixam linhas parciais; edição substitui estado vigente e atualiza usuário/data.

**Verificar:** testar primeiro registro completo e incompleto, edição, período inválido e pessoa de outra atividade.

**Commit:** `feat(presencas): Confirma registros do turno`.

### [ ] 2.4 [RF-002, RF-003] Avisar alterações concorrentes

**Criar:** `apps/web/resources/js/presencas/confirmacao.js`.

**Modificar:** `apps/web/app/Http/Controllers/Admin/PresencaController.php`, `apps/web/resources/views/admin/presencas/show.blade.php`, `apps/web/resources/js/app.js` e `apps/web/routes/web.php`.

**Padrão:** imports nativos em `apps/web/resources/js/app.js`.

**Escopo:** expor rota autenticada de leitura da versão e última confirmação; antes de salvar, comparar a versão aberta com a atual; se outro usuário confirmou depois, avisar nome e data/hora e manter a confirmação disponível. A última confirmação deve prevalecer.

**AC:** sem concorrência, o fluxo permanece direto; com concorrência, há aviso não bloqueante e a nova versão aparece após salvar.

**Verificar:** testar o mesmo período em duas sessões autenticadas.

**Commit:** `feat(presencas): Avisa alterações concorrentes`.

### [ ] 2.5 [RF-007, RF-008] Incluir participante tardio

**Criar:** `apps/web/app/Http/Requests/Admin/AdicionarParticipanteTardioRequest.php` e `apps/web/app/Actions/Presencas/AdicionarParticipanteTardio.php`.

**Modificar:** `apps/web/app/Http/Controllers/Admin/PresencaController.php`, `apps/web/resources/views/admin/presencas/show.blade.php` e `apps/web/routes/web.php`.

**Padrão:** resolução de pessoas em `apps/web/app/Actions/Inscricoes/CriarAtividade.php`.

**Escopo:** criar `POST /admin/presencas/{atividade}/participantes`; receber tipo e nome; criar pessoa quando necessário e vincular com flag tardia; bloquear mesmo tipo e nome normalizado na atividade; preservar filtros ao retornar à ficha.

**AC:** a inclusão não altera período já confirmado; a pessoa pode ficar Não registrada em período passado e ser marcada em edição; duplicidade é rejeitada.

**Verificar:** incluir aluno e professor em atividade com período confirmado, testar período futuro, edição retroativa e duplicidade.

**Commit:** `feat(presencas): Adiciona participante tardio`.

## 5. Fase 3 — Interface e validação

### [ ] 3.1 [RN-001, RF-004, RF-005, RF-006] Ajustar interface para operação móvel

**Modificar:** `apps/web/resources/views/layouts/admin.blade.php`, `apps/web/resources/css/admin.css`, `apps/web/resources/views/admin/presencas/index.blade.php` e `apps/web/resources/views/admin/presencas/show.blade.php`.

**Padrão:** tokens e breakpoints de `apps/web/resources/css/admin.css`.

**Escopo:** adicionar menu Controle de presença; adaptar filtros, indicadores, cartões, marcações e confirmação para toque; destacar campos pendentes e aviso de concorrência sem rolagem horizontal obrigatória.

**AC:** funciona em 360 px, tablet e desktop; data/turno ficam claros; navegação por teclado tem labels adequados.

**Verificar:** executar fluxo por toque e teclado nas três larguras.

**Commit:** `style(presencas): Ajusta interface para operação móvel`.

### [ ] 3.2 [RN-001, RF-001 a RF-008] Validar e preparar publicação

**Modificar:** `docs/validacao-mvp.md` e `docs/planos-de-implementacao/controle-de-presenca.md`.

**Escopo:** registrar validação manual; executar migrations locais, Pint, build e caches; validar autenticação, filtros, primeiro registro, edição, concorrência, participante tardio e regressão de `/inscricoes`.

**Verificar:**

```bash
cd apps/web
./vendor/bin/pint --dirty
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**AC:** login é obrigatório; formulário público continua funcional; migrations e assets entram no deploy.

**Commit:** `chore(presencas): Valida módulo administrativo`.

## 6. Gates e cobertura

**Gate 1:** calendário válido, migrations aditivas, constraints e flag tardia prontos.

**Gate 2:** filtro, confirmação transacional, edição, aviso concorrente e participante tardio operantes.

**Gate 3:** interface validada em mobile/tablet/desktop, Pint/build/caches concluídos e smoke test do admin e da inscrição pública aprovado.

| Requisito | Tasks |
|---|---|
| RN-001 | 3.1, 3.2 |
| RF-001 | 1.1, 1.2, 1.3, 2.2, 2.3 |
| RF-002 | 1.2, 1.3, 2.3, 2.4 |
| RF-003 | 1.2, 2.3, 2.4 |
| RF-004 | 2.1, 2.2, 3.1 |
| RF-005 | 1.1, 2.1, 3.1 |
| RF-006 | 1.1, 2.2, 3.1 |
| RF-007 | 1.2, 1.3, 2.5 |
| RF-008 | 2.1, 2.2, 2.3, 2.5, 3.2 |

## 7. Fora deste plano

- Perfis, atribuição de atividades, exclusões e histórico completo;
- QR code, biometria, integrações externas e operação offline;
- certificados, livro ou relatórios derivados; entidade de evento ou administração visual do calendário.
