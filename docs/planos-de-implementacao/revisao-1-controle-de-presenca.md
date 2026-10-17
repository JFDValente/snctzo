# Plano de implementação — Revisão 1 do controle de presença

## Objetivo e proteções

Implementar a [Revisão 1](../revisao-1-controle-de-presenca.md) em incrementos
independentes. Cada item concluído gera um commit. Usar Laravel, Blade, CSS e
JavaScript nativos; preservar inscrições públicas, dados existentes e rotas
canônicas de presença.

Senhas reais, hashes operacionais, `.env` e credenciais não entram no Git.

## Fase 1 — Identidade e autorização

### [ ] 1.1 [RF-R1-01, RF-R1-02] Evoluir dados de usuário

**Criar:** migration aditiva em `apps/web/database/migrations/`.

**Modificar:** `apps/web/app/Models/User.php` e
`apps/web/database/factories/UserFactory.php`.

**Padrão:** `apps/web/database/migrations/2026_10_15_000019_create_periodos_de_presenca_table.php`.

**Escopo:** adicionar `username` único e opcional, `perfil` com padrão
`administrador` e permitir e-mail nulo para operadores. Preservar usuários
existentes como administradores.

**AC:** usernames são únicos sem distinção de caixa; usuários atuais não
perdem e-mail, senha ou acesso; nenhuma senha é registrada na migration.

**Verificar:** executar migration em banco local com usuários existentes.

**Commit:** `feat(usuarios): Adiciona perfil de operador`.

### [ ] 1.2 [RF-R1-01, RF-R1-02, RF-R1-03] Aplicar login e acesso por módulo

**Criar:** middleware em `apps/web/app/Http/Middleware/`.

**Modificar:** `apps/web/app/Http/Controllers/Admin/AutenticacaoController.php`,
`apps/web/app/Http/Requests/Admin/LoginRequest.php`,
`apps/web/resources/views/admin/autenticacao/login.blade.php`,
`apps/web/routes/web.php` e `apps/web/bootstrap/app.php`.

**Padrão:** grupo administrativo em `apps/web/routes/web.php`.

**Escopo:** aceitar e-mail ou username; proteger atividades e presenças por
perfil; definir página inicial por perfil; criar `/presenca` como atalho que
preserva a URL pretendida para visitantes.

**AC:** operador recebe 403 nas rotas de atividades; administrador mantém
acesso; alias retorna à presença depois do login.

**Verificar:** testar administrador e operador em sessões separadas, incluindo
login, logout, alias e acesso direto a URLs bloqueadas.

**Commit:** `feat(autorizacao): Restringe operador à presença`.

### [ ] 1.3 [RF-R1-01] Provisionar operadores sem versionar credenciais

**Criar:** comando Artisan idempotente em `apps/web/app/Console/Commands/` ou
procedimento operacional seguro para o deploy.

**Modificar:** registro de comando, apenas se necessário.

**Padrão:** cast `password` de `apps/web/app/Models/User.php`.

**Escopo:** criar ou atualizar os três operadores iniciais com perfil
`operador_presenca`, recebendo senha apenas em execução protegida.

**AC:** contas existem com hash válido e sem acesso às atividades.

**Verificar:** provisionar contas locais temporárias e autenticar como cada
perfil.

**Commit:** `chore(usuarios): Provisiona operadores de presença`.

## Fase 2 — Lista do operador

### [ ] 2.1 [RF-R1-04, RF-R1-05] Listar e buscar por professor responsável

**Modificar:** `apps/web/app/Http/Controllers/Admin/PresencaController.php`,
`apps/web/resources/views/admin/presencas/index.blade.php`,
`apps/web/resources/js/presencas/busca-atividades.js` e, se necessário,
`apps/web/resources/css/admin.css`.

**Padrão:** relações em `apps/web/app/Models/Atividade.php` e listagem atual de
`PresencaController`.

**Escopo:** carregar `professorResponsavel`, ordenar por atividade e ID,
substituir instituição pelo responsável e alterar a busca frontend para o nome
normalizado dele.

**AC:** ordem é estável; busca ignora caixa e acentos; estado do período segue
correto; não há nova requisição durante a digitação.

**Verificar:** testar datas/turnos, responsáveis distintos, busca parcial e
sem resultados.

**Commit:** `feat(presencas): Busca por professor responsável`.

## Fase 3 — Participante tardio sem recarga

### [ ] 3.1 [RF-R1-06, RF-R1-07] Expor inclusão tardia assíncrona

**Modificar:** `apps/web/app/Actions/Presencas/AdicionarParticipanteTardio.php`,
`apps/web/app/Http/Controllers/Admin/PresencaController.php`,
`apps/web/app/Http/Requests/Admin/AdicionarParticipanteTardioRequest.php` e
`apps/web/routes/web.php`.

**Criar:** partial em `apps/web/resources/views/admin/presencas/partials/` se
necessário para devolver cartão seguro da nova pessoa.

**Padrão:** endpoint JSON `situacao` do `PresencaController`.

**Escopo:** aceitar POST assíncrono com CSRF, retornar participante criado com
ID, tipo, nome e flag tardia, e preservar fallback HTTP sem JavaScript.

**AC:** inclusão válida não muda as marcações existentes; duplicidade e dados
inválidos devolvem erros tratáveis.

**Verificar:** testar inclusão válida, nome ausente, duplicidade e preservação
de escolhas já feitas.

**Commit:** `feat(presencas): Inclui participante sem recarga`.

### [ ] 3.2 [RF-R1-06, RF-R1-07] Criar diálogo responsivo de inclusão

**Criar:** módulo em `apps/web/resources/js/presencas/`.

**Modificar:** `apps/web/resources/views/admin/presencas/show.blade.php`,
`apps/web/resources/js/app.js` e `apps/web/resources/css/admin.css`.

**Padrão:** `apps/web/resources/js/presencas/situacao.js` e cartões existentes
em `show.blade.php`.

**Escopo:** ocultar formulário inicial, abrir diálogo central no desktop e
painel amplo no mobile, enviar via fetch e inserir o cartão sem recarga.

**AC:** formulário só aparece pelo botão; erro permanece no diálogo; novo
cartão mostra cadastro tardio e exige situação antes de confirmar o turno.

**Verificar:** testar toque em 360 px, tablet e desktop após marcar presenças.

**Commit:** `feat(presencas): Abre cadastro tardio sob demanda`.

## Fase 4 — Feedback de confirmação

### [ ] 4.1 [RF-R1-08, RF-R1-09] Exibir diálogos de sucesso e erro

**Criar:** partial de diálogo em `apps/web/resources/views/admin/partials/`, se
a reutilização for adequada.

**Modificar:** `apps/web/app/Http/Controllers/Admin/PresencaController.php`,
`apps/web/resources/views/admin/presencas/show.blade.php`,
`apps/web/resources/js/presencas/confirmacao.js` e
`apps/web/resources/css/admin.css`.

**Padrão:** flash de sessão do controller e aviso concorrente atual.

**Escopo:** abrir sucesso na ficha depois de persistir; botão OK leva à lista
com mesma data/turno. Em falha, abrir erro na ficha, manter old input e
destacar campos inválidos. Não usar `alert`/`confirm` nativos para esse retorno.

**AC:** não há retorno automático; erro não descarta dados; diálogos funcionam
por teclado e toque.

**Verificar:** testar sucesso, participante sem situação, dado inválido, OK e
manutenção dos filtros.

**Commit:** `feat(presencas): Exibe retorno de confirmação`.

## Fase 5 — Validação e deploy

### [ ] 5.1 [RF-R1-01 a RF-R1-09] Validar revisão

**Modificar:** os documentos desta revisão somente para registrar ajustes de
validação necessários.

**Proteger:** não alterar inscrições públicas, atividades existentes ou
presenças válidas durante os testes.

**Escopo:** executar migrations locais, Pint, build, caches e smoke tests dos
dois perfis, do alias, da listagem, da inclusão tardia e dos diálogos.

**Verificar:**

```bash
cd apps/web
./vendor/bin/pint --dirty
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**AC:** critérios da revisão validados e artefatos prontos para deploy manual
na Hostinger.

**Commit:** `chore(presencas): Valida revisão de presença`.

## Cobertura

| Requisito | Tasks |
|---|---|
| RF-R1-01 | 1.1, 1.2, 1.3, 5.1 |
| RF-R1-02 | 1.1, 1.2, 5.1 |
| RF-R1-03 | 1.2, 5.1 |
| RF-R1-04 | 2.1, 5.1 |
| RF-R1-05 | 2.1, 5.1 |
| RF-R1-06 | 3.1, 3.2, 5.1 |
| RF-R1-07 | 3.1, 3.2, 5.1 |
| RF-R1-08 | 4.1, 5.1 |
| RF-R1-09 | 4.1, 5.1 |
