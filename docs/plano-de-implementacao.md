# Plano de implementação — SNCTZO 2026

## 1. Objetivo

Este documento organiza a implementação do MVP de inscrições em tarefas pequenas, verificáveis e apropriadas para execução por agentes. Cada tarefa concluída deve produzir um único commit coerente.

O plano não substitui as especificações. Antes de executar uma tarefa, o agente deve consultar:

1. [Formulário de inscrição](./formulario-de-inscricao.md) — conteúdo, navegação e comportamento do formulário;
2. [Modelo de dados](./modelo-de-dados.md) — entidades, relacionamentos e constraints;
3. [Decisões estruturais](./decisoes-estruturais.md) — stack, hospedagem, segurança e limites operacionais.

Em caso de divergência:

- regras funcionais seguem `formulario-de-inscricao.md`;
- persistência segue `modelo-de-dados.md`;
- infraestrutura segue `decisoes-estruturais.md`;
- este documento define apenas ordem, dependências e verificação.

## 2. Requisitos rastreáveis

| ID | Requisito |
|---|---|
| RF-001 | Restringir o formulário pela janela configurável de inscrições |
| RF-002 | Selecionar ou cadastrar instituição e curso principal |
| RF-003 | Localizar ou cadastrar professor por e-mail único |
| RF-004 | Selecionar ou cadastrar participantes alunos e professores |
| RF-005 | Cadastrar atividade, dias, resumo e observações |
| RF-006 | Exigir todos os aceites e registrar versão e instante dos termos |
| RF-007 | Persistir a inscrição de forma transacional e idempotente |
| RF-008 | Exibir confirmação de sucesso sem protocolo público |
| RF-009 | Aplicar CSRF, honeypot, rate limit e validação no backend |
| RF-010 | Enviar por SMTP uma cópia completa ao professor responsável |

## 3. Contrato de execução para agentes

- Executar as fases na ordem apresentada.
- Executar as tarefas de cada fase na ordem numérica, salvo indicação explícita de paralelismo.
- Ler as três especificações antes de alterar código.
- Inspecionar o estado atual do repositório antes de cada tarefa.
- Não ampliar o escopo para backoffice, presença offline ou múltiplos eventos.
- Não adicionar framework JavaScript.
- Não introduzir Docker, filas, Redis, workers, CI/CD ou serviços externos sem nova decisão.
- Não incluir segredos, credenciais ou `.env` em commits.
- Não alterar registros existentes pelo formulário público.
- Não implementar o e-mail antes da Fase 6.
- Usar PT-BR em código de domínio, documentação, interface e mensagens.
- Usar Conventional Commits.
- Atualizar o checkbox da tarefa no mesmo commit que a conclui.
- Se uma decisão precisar mudar, interromper a tarefa e atualizar primeiro as especificações.

### Definição de pronto de cada tarefa

Uma tarefa só está pronta quando:

1. o escopo descrito foi concluído sem itens adicionais;
2. os comandos de verificação passaram;
3. o diff não contém segredos nem arquivos alheios à tarefa;
4. o Laravel Pint passou nos arquivos PHP alterados;
5. o comportamento relevante foi verificado manualmente;
6. o commit contém somente essa tarefa.

## 4. Gates gerais

### Gate 0 — Ambiente local

Executar antes da primeira alteração:

```bash
php -v
composer --version
node --version
npm --version
mysql --version
```

Critérios:

- PHP 8.4;
- Composer 2;
- Node e npm disponíveis apenas para build local;
- MariaDB ou MySQL local acessível;
- extensões PHP exigidas pelo Laravel habilitadas.

Se o ambiente não cumprir esses requisitos, interromper a implementação e corrigir o ambiente sem alterar o projeto.

## 5. Fase 1 — Fundação

Objetivo: criar uma aplicação Laravel mínima, reproduzível e executável localmente.

### [x] 1.1 Inicializar a aplicação Laravel

**Requisitos:** fundação de RF-001 a RF-010
**Dependências:** Gate 0

**Escopo:**

- Criar Laravel 13 em `apps/web`.
- Usar PHP 8.4 e Composer.
- Manter o projeto sem starter kit de autenticação.
- Preservar `docs/` na raiz do monorepo.
- Ajustar o `.gitignore` da raiz para arquivos locais e segredos.

**Caminhos principais:**

- `apps/web/composer.json`
- `apps/web/composer.lock`
- `apps/web/artisan`
- `apps/web/app/`
- `apps/web/config/`
- `apps/web/resources/`
- `apps/web/routes/`
- `.gitignore`

**Verificar:**

```bash
cd apps/web
php artisan --version
php artisan about
```

**Commit sugerido:** `chore: Inicializa aplicação Laravel`

### [x] 1.2 Configurar idioma, fuso e ambiente

**Requisitos:** RF-001, RF-006
**Dependências:** 1.1

**Escopo:**

- Configurar locale PT-BR e fuso `America/Sao_Paulo`.
- Preparar conexão MySQL/MariaDB por variáveis de ambiente.
- Configurar logs locais e arquivos rotativos em produção.
- Adicionar ao `.env.example` somente chaves sem valores secretos.
- Prever abertura, encerramento, horários e versão dos termos por configuração.
- Manter inscrições abertas quando abertura e encerramento estiverem ausentes.

**Caminhos planejados:**

- `apps/web/.env.example`
- `apps/web/config/app.php`
- `apps/web/config/database.php`
- `apps/web/config/logging.php`
- `apps/web/config/snctzo.php`

**Verificar:**

```bash
cd apps/web
php artisan config:clear
php artisan about
```

**Commit sugerido:** `chore: Configura ambiente da aplicação`

### [x] 1.3 Preparar frontend sem framework

**Requisitos:** base visual de RF-002 a RF-008
**Dependências:** 1.1

**Escopo:**

- Manter Blade, CSS e JavaScript nativos.
- Usar Vite somente como ferramenta de build.
- Criar layout público básico e responsivo.
- Não implementar ainda as cinco etapas do formulário.

**Caminhos planejados:**

- `apps/web/resources/views/layouts/publico.blade.php`
- `apps/web/resources/css/app.css`
- `apps/web/resources/js/app.js`
- `apps/web/vite.config.js`

**Verificar:**

```bash
cd apps/web
npm install
npm run build
```

**Commit sugerido:** `chore: Prepara frontend público`

### Gate 1 — Fundação executável

- `php artisan about` sem erros;
- build dos assets concluído;
- página Blade básica carregando localmente;
- nenhum segredo versionado.

## 6. Fase 2 — Banco e domínio

Objetivo: materializar o modelo de dados e suas invariantes antes do formulário.

### [x] 2.1 Criar migrations de instituições e cursos

**Requisitos:** RF-002
**Dependências:** Gate 1

**Escopo:**

- Criar `instituicoes` e `cursos` conforme o modelo de dados.
- Aplicar chaves estrangeiras e unicidade case-insensitive e accent-insensitive.
- Bloquear exclusão de registros referenciados.

**Caminhos planejados:**

- `apps/web/database/migrations/*_create_instituicoes_table.php`
- `apps/web/database/migrations/*_create_cursos_table.php`

**Verificar:**

```bash
cd apps/web
php artisan migrate
php artisan migrate:rollback
php artisan migrate
```

**Commit sugerido:** `feat: Cria estrutura de instituições e cursos`

### [x] 2.2 Criar migrations de professores e alunos

**Requisitos:** RF-003, RF-004
**Dependências:** 2.1

**Escopo:**

- Criar `professores` com e-mail único.
- Criar `alunos` sem unicidade global por nome e curso.
- Aplicar chaves estrangeiras e índices.

**Caminhos planejados:**

- `apps/web/database/migrations/*_create_professores_table.php`
- `apps/web/database/migrations/*_create_alunos_table.php`

**Verificar:** repetir ciclo `migrate`, `rollback` e `migrate`.

**Commit sugerido:** `feat: Cria estrutura de participantes`

### [x] 2.3 Criar migrations de atividades e participações

**Requisitos:** RF-005, RF-006, RF-007, RF-010
**Dependências:** 2.2

**Escopo:**

- Criar `atividades` sem `instituicao_id`.
- Incluir curso, responsável, dias, textos, termos, token e controle do e-mail.
- Criar `atividade_aluno` e `atividade_professor`.
- Aplicar constraints de dias, idempotência e participação sem duplicidade.

**Caminhos planejados:**

- `apps/web/database/migrations/*_create_atividades_table.php`
- `apps/web/database/migrations/*_create_atividade_aluno_table.php`
- `apps/web/database/migrations/*_create_atividade_professor_table.php`

**Verificar:** repetir ciclo completo de migrations.

**Commit sugerido:** `feat: Cria estrutura de atividades`

### [x] 2.4 Implementar models e relacionamentos

**Requisitos:** RF-002 a RF-007
**Dependências:** 2.3

**Escopo:**

- Criar models Eloquent.
- Declarar relacionamentos, casts e campos permitidos.
- Não colocar regras de fluxo em controllers ou models.

**Caminhos planejados:**

- `apps/web/app/Models/Instituicao.php`
- `apps/web/app/Models/Curso.php`
- `apps/web/app/Models/Professor.php`
- `apps/web/app/Models/Aluno.php`
- `apps/web/app/Models/Atividade.php`

**Verificar:** usar `php artisan tinker` para criar e consultar relacionamentos básicos.

**Commit sugerido:** `feat: Modela domínio de inscrições`

### [x] 2.5 Criar dados iniciais

**Requisitos:** RF-002
**Dependências:** 2.4

**Escopo:**

- Criar seed idempotente para FCBS e FCEE.
- Não inventar cursos, professores ou alunos.

**Caminhos planejados:**

- `apps/web/database/seeders/InstituicaoSeeder.php`
- `apps/web/database/seeders/DatabaseSeeder.php`

**Verificar:**

```bash
cd apps/web
php artisan migrate:fresh --seed
```

Executar o seed novamente e confirmar que não duplica instituições.

**Commit sugerido:** `feat: Adiciona instituições iniciais`

### Gate 2 — Modelo reproduzível

- `migrate:fresh --seed` concluído;
- FCBS e FCEE presentes uma única vez;
- relacionamentos consultáveis;
- constraints críticas confirmadas manualmente.

## 7. Fase 3 — Backend da inscrição

Objetivo: disponibilizar leitura, validação e persistência antes de completar a interface.

### [x] 3.1 Implementar disponibilidade das inscrições

**Requisitos:** RF-001
**Dependências:** Gate 2

**Escopo:**

- Ler abertura e encerramento da configuração.
- Considerar limites ausentes como abertos.
- Expor estado aberto, ainda não aberto ou encerrado.
- Validar novamente no POST final.

**Caminhos planejados:**

- `apps/web/app/Support/Inscricoes/PeriodoDeInscricao.php`
- `apps/web/config/snctzo.php`

**Verificar:** alterar valores locais e conferir todos os estados no fuso configurado.

**Commit sugerido:** `feat: Controla período de inscrições`

### [x] 3.2 Implementar catálogos do formulário

**Requisitos:** RF-002, RF-004
**Dependências:** 2.5

**Escopo:**

- Listar todas as instituições.
- Carregar cursos e alunos da instituição selecionada.
- Retornar somente os dados necessários ao formulário.
- Não permitir alteração de registros existentes.

**Caminhos planejados:**

- `apps/web/app/Http/Controllers/CatalogoInscricaoController.php`
- `apps/web/routes/web.php`

**Verificar:** validar manualmente respostas para instituição válida, inexistente e sem alunos.

**Commit sugerido:** `feat: Disponibiliza catálogos de inscrição`

### [x] 3.3 Implementar busca exata de professor

**Requisitos:** RF-003, RF-004, RF-009
**Dependências:** 2.4

**Escopo:**

- Normalizar e validar o e-mail.
- Buscar apenas por correspondência exata.
- Retornar nome e instituição quando encontrado.
- Aplicar limitação de requisições ao endpoint.
- Não pré-carregar professores no frontend.

**Caminhos planejados:**

- `apps/web/app/Http/Controllers/BuscarProfessorController.php`
- `apps/web/app/Http/Requests/BuscarProfessorRequest.php`
- `apps/web/routes/web.php`

**Verificar:** e-mail existente, inexistente, inválido e excesso de requisições.

**Commit sugerido:** `feat: Adiciona busca de professor por email`

### [x] 3.4 Implementar normalização e validação final

**Requisitos:** RF-002 a RF-007
**Dependências:** 3.1 a 3.3

**Escopo:**

- Validar todos os campos e aceites no backend.
- Normalizar nomes, e-mails e URLs.
- Validar instituição do responsável e dos alunos.
- Permitir professor participante externo.
- Bloquear participantes repetidos.
- Exigir ao menos um participante e um dia.
- Limitar resumo e observações a 5.000 caracteres.

**Caminhos planejados:**

- `apps/web/app/Http/Requests/StoreAtividadeRequest.php`
- `apps/web/app/Support/Normalizacao/NormalizadorDeTexto.php`
- `apps/web/app/Support/Normalizacao/NormalizadorDeUrl.php`

**Verificar:** executar manualmente a matriz de erros descrita nas especificações.

**Commit sugerido:** `feat: Valida dados da inscrição`

### [x] 3.5 Implementar transação e idempotência

**Requisitos:** RF-002 a RF-008
**Dependências:** 3.4

**Escopo:**

- Resolver entidades existentes sem atualizá-las.
- Criar novas entidades quando permitido.
- Persistir a atividade e as participações em uma transação.
- Validar a instituição do responsável e dos alunos.
- Garantir idempotência pelo token de submissão.
- Retornar o mesmo sucesso para o reenvio do token já concluído.

**Caminhos planejados:**

- `apps/web/app/Actions/Inscricoes/CriarAtividade.php`
- `apps/web/app/Http/Controllers/InscricaoController.php`
- `apps/web/routes/web.php`

**Verificar:**

- inscrição válida;
- erro no meio da operação sem dados parciais;
- reenvio do mesmo token sem segunda atividade;
- conflito de e-mail e duplicidade de participantes.

**Commit sugerido:** `feat: Persiste inscrição de atividade`

### Gate 3 — Backend funcional

- inscrição completa persistida sem interface final;
- transação e idempotência verificadas;
- respostas de erro em PT-BR;
- registros existentes preservados.

## 8. Fase 4 — Formulário paginado

Objetivo: implementar a experiência pública completa sobre o backend validado.

### [x] 4.1 Criar estrutura das cinco etapas

**Requisitos:** RF-001, RF-002, RF-005, RF-006
**Dependências:** Gate 3

**Escopo:**

- Criar um único formulário HTML.
- Renderizar as cinco etapas e todos os textos aprovados.
- Omitir prazos e horários ausentes.
- Adicionar indicador de progresso e botões de navegação.

**Caminhos planejados:**

- `apps/web/resources/views/inscricoes/create.blade.php`
- `apps/web/resources/views/inscricoes/partials/`
- `apps/web/resources/css/inscricoes.css`

**Verificar:** conferir textos, ordem das etapas e comportamento sem JavaScript antes da dinâmica.

**Commit sugerido:** `feat: Cria formulário paginado`

### [x] 4.2 Implementar instituição, curso e responsável

**Requisitos:** RF-002, RF-003
**Dependências:** 4.1, 3.2, 3.3

**Escopo:**

- Listar instituições e permitir nova instituição.
- Bloquear dados de instituição existente.
- Carregar ou criar curso.
- Buscar professor responsável pelo e-mail.
- Bloquear os dados encontrados.

**Caminhos planejados:**

- `apps/web/resources/js/inscricoes/instituicao.js`
- `apps/web/resources/js/inscricoes/professor.js`
- partial da etapa 2.

**Verificar:** instituição e curso existentes, novos e professor de instituição incompatível.

**Commit sugerido:** `feat: Implementa identificação da atividade`

### [x] 4.3 Implementar participantes dinâmicos

**Requisitos:** RF-004
**Dependências:** 4.2

**Escopo:**

- Adicionar e remover participantes.
- Alternar campos de aluno e professor.
- Autocompletar alunos pré-carregados.
- Buscar professores por e-mail.
- Permitir professor externo.
- Bloquear dados existentes e duplicidades locais.

**Caminhos planejados:**

- `apps/web/resources/js/inscricoes/participantes.js`
- partial da etapa 3.

**Verificar:** todos os fluxos de aluno, professor, responsável participante e duplicidade.

**Commit sugerido:** `feat: Adiciona participantes dinâmicos`

### [ ] 4.4 Implementar navegação e validação por etapa

**Requisitos:** RF-002 a RF-006
**Dependências:** 4.3

**Escopo:**

- Avançar somente com campos obrigatórios válidos.
- Permitir retorno sem perder o estado em memória.
- Exibir erros junto aos campos.
- Exigir todos os aceites nas etapas 4 e 5.
- Manter submissão exclusiva na etapa final.

**Caminhos planejados:**

- `apps/web/resources/js/inscricoes/formulario.js`
- `apps/web/resources/views/inscricoes/create.blade.php`

**Verificar:** ida e volta entre todas as etapas, erros e estado preservado.

**Commit sugerido:** `feat: Controla navegação do formulário`

### [ ] 4.5 Integrar submissão e confirmação

**Requisitos:** RF-007, RF-008
**Dependências:** 4.4, 3.5

**Escopo:**

- Gerar e enviar o token de idempotência.
- Preservar dados quando o backend rejeitar a submissão.
- Impedir clique duplo durante o envio.
- Exibir somente “Inscrição enviada com sucesso” após confirmação.
- Não gerar protocolo nem edição pública.

**Caminhos planejados:**

- `apps/web/app/Http/Controllers/InscricaoController.php`
- `apps/web/resources/views/inscricoes/sucesso.blade.php`
- `apps/web/resources/js/inscricoes/formulario.js`

**Verificar:** sucesso, erro de validação, prazo encerrado e clique duplo.

**Commit sugerido:** `feat: Conclui submissão pública`

### Gate 4 — Fluxo público completo

- cinco etapas utilizáveis em celular e desktop;
- cadastro completo persistido;
- mensagens e textos conferidos contra a especificação;
- nenhuma dependência do e-mail.

## 9. Fase 5 — Segurança e estabilização

Objetivo: preparar o MVP para publicação sem SMTP.

### [ ] 5.1 Aplicar proteções contra abuso

**Requisitos:** RF-009
**Dependências:** Gate 4

**Escopo:**

- Confirmar CSRF em todos os POSTs.
- Adicionar honeypot sem serviço externo.
- Limitar submissões a 100 por IP por hora.
- Limitar busca de professor separadamente.
- Escapar todo conteúdo exibido.

**Caminhos planejados:**

- `apps/web/bootstrap/app.php`
- `apps/web/routes/web.php`
- requests e views do formulário.

**Verificar:** CSRF inválido, honeypot preenchido e limites excedidos.

**Commit sugerido:** `feat: Protege formulário contra abuso`

### [ ] 5.2 Ajustar responsividade e acessibilidade

**Requisitos:** RF-002 a RF-008
**Dependências:** 5.1

**Escopo:**

- Garantir labels, foco, teclado e mensagens associadas aos campos.
- Revisar contraste, tamanhos e ordem de leitura.
- Validar celular, tablet e desktop.
- Não alterar regras funcionais durante o ajuste visual.

**Caminhos planejados:** CSS, JavaScript e views do formulário.

**Verificar:** navegação completa por teclado e inspeção em larguras representativas.

**Commit sugerido:** `style: Ajusta interface responsiva`

### Gate 5 — MVP sem e-mail

Executar manualmente e registrar o resultado:

- instituição e curso existentes;
- instituição e curso novos;
- responsável existente e novo;
- responsável de instituição incompatível;
- alunos existentes e novos;
- professor participante interno e externo;
- participantes duplicados;
- nenhuma pessoa, nenhum dia ou aceite incompleto;
- resumo e observações no limite;
- prazo antes da abertura e depois do encerramento;
- clique duplo e reenvio;
- falha transacional;
- CSRF, honeypot e rate limit;
- celular e desktop.

Se o Gate 5 passar, a aplicação pode ser publicada sem e-mail.

## 10. Fase 6 — E-mail de confirmação

Objetivo: adicionar SMTP sem comprometer o cadastro já estabilizado.

### [ ] 6.1 Criar template da confirmação

**Requisitos:** RF-010
**Dependências:** Gate 5

**Escopo:**

- Criar Mailable e template em PT-BR.
- Incluir a cópia completa da inscrição e dos aceites.
- Não incluir segredos nem links administrativos.

**Caminhos planejados:**

- `apps/web/app/Mail/InscricaoConfirmada.php`
- `apps/web/resources/views/emails/inscricao-confirmada.blade.php`

**Verificar:** renderizar o e-mail localmente com uma atividade completa.

**Commit sugerido:** `feat: Cria email de confirmação`

### [ ] 6.2 Integrar envio SMTP após a transação

**Requisitos:** RF-010
**Dependências:** 6.1

**Escopo:**

- Configurar SMTP somente por variáveis de ambiente.
- Enviar depois do commit da transação.
- Preencher `email_confirmacao_enviado_em` no sucesso.
- Registrar falha sem desfazer nem ocultar a inscrição.
- Não mencionar o e-mail na mensagem pública de sucesso.

**Caminhos planejados:**

- `apps/web/config/mail.php`
- `apps/web/.env.example`
- action de inscrição ou serviço específico de notificação.

**Verificar:** envio bem-sucedido, credencial inválida e indisponibilidade SMTP.

**Commit sugerido:** `feat: Envia confirmação da inscrição`

### Gate 6 — Limite operacional do e-mail

- confirmar no hPanel a caixa Free Business Email;
- confirmar o limite vigente de 100 envios por 24 horas;
- validar porta, criptografia e remetente;
- garantir que a inscrição funciona quando a cota estiver esgotada.

## 11. Fase 7 — Publicação

Objetivo: colocar uma versão validada no subdomínio de `sicsu.net`.

### [ ] 7.1 Preparar artefato de produção

**Dependências:** Gate 5; Gate 6 é opcional para a primeira publicação

**Escopo:**

- Executar build de produção.
- Instalar dependências sem pacotes de desenvolvimento.
- preparar caches do Laravel;
- confirmar que nenhum segredo está versionado;
- criar tag da versão candidata.

**Verificar:** aplicação executável com `APP_ENV=production` e `APP_DEBUG=false`.

**Commit sugerido:** somente se houver ajustes de configuração versionável.

### [ ] 7.2 Configurar Hostinger

**Dependências:** 7.1

**Escopo operacional:**

- Criar o subdomínio de `sicsu.net`.
- Apontar o document root para `apps/web/public`.
- Selecionar PHP 8.4 e conferir extensões.
- Emitir ou associar SSL ao subdomínio.
- Criar banco e usuário exclusivos.
- Configurar `.env` diretamente na hospedagem.
- executar migrations e caches;
- ajustar permissões de `storage` e `bootstrap/cache`.

Esta tarefa altera a hospedagem e exige autorização explícita no momento da execução.

### Gate 7 — Smoke test de produção

- HTTPS válido;
- formulário abre e respeita a janela de inscrições;
- inscrição completa persiste;
- logs recebem erros sem expor dados sensíveis;
- site existente em `sicsu.net` permanece inalterado;
- SMTP funciona ou está deliberadamente desativado;
- tag da versão publicada registrada no Git.

## 12. Ordem de prioridade

| Prioridade | Entrega |
|---|---|
| P0 | Fases 1 e 2 — aplicação e banco |
| P0 | Fase 3 — backend transacional |
| P0 | Fase 4 — formulário completo |
| P0 | Fase 5 — segurança e estabilização |
| P1 | Fase 7 — publicação sem e-mail |
| P2 | Fase 6 — e-mail de confirmação |

Embora numerada antes da publicação, a Fase 6 é opcional para a primeira versão. Se a cota de desenvolvimento estiver próxima do limite, publicar após o Gate 5 e implementar SMTP depois.

## 13. Orientação para divisão entre agentes

- Não paralelizar as Fases 1 e 2: elas definem a estrutura compartilhada.
- Não permitir que dois agentes alterem simultaneamente `routes/web.php`, o formulário principal ou a action de persistência.
- Delegar somente tarefas com arquivos disjuntos e contratos já estabilizados.
- Um agente executor deve receber apenas uma tarefa numerada por vez.
- Um agente revisor deve validar o diff contra os RFs e os documentos de origem.
- Concluir e commitar uma tarefa antes de iniciar outra.
- Interromper a execução quando uma task revelar uma decisão não documentada.

Paralelismo seguro após o Gate 3:

- conteúdo e layout estático das etapas;
- revisão dos textos contra a especificação;
- preparação visual do e-mail, somente depois do Gate 5;
- elaboração do checklist operacional de deploy.

## 14. Fora do plano

- Backoffice e autenticação.
- Perfis e permissões.
- Controle offline de presença.
- Persistência de rascunhos.
- Edição pública após envio.
- Suporte a várias edições do evento.
- Busca protegida e paginada de alunos.
- Autorização específica para menores.
- Filas e reenvio automático de e-mails.
- Docker, staging, CI/CD e observabilidade externa.
- Testes automatizados nesta etapa.
