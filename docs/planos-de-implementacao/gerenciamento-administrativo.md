# Plano de implementação — gerenciamento administrativo

## 1. Objetivo

Este plano organiza a execução técnica do módulo administrativo da SNCTZO 2026. A especificação funcional, as rotas, os dados exibidos e o contrato da exportação CSV estão em [Gerenciamento administrativo](../admin-gerenciamento.md).

Antes de executar uma fase, consultar também:

1. [Modelo de dados](../modelo-de-dados.md);
2. [Decisões estruturais](../decisoes-estruturais.md);
3. [Publicação na Hostinger](../publicacao-hostinger.md), quando houver deploy.

## 2. Contrato de execução

- Executar as fases na ordem indicada.
- Manter o formulário em `/inscricoes` público e inalterado, salvo necessidade explícita.
- Não criar perfis de acesso, registro público de usuários ou recuperação de senha sem nova decisão.
- Não introduzir framework JavaScript, SPA, filas, workers ou serviços externos.
- Não versionar senhas, credenciais, `.env` ou exports gerados.
- Usar PT-BR e Conventional Commits.
- Atualizar o status da fase no mesmo commit da sua implementação.

## 3. Fases concluídas

### [x] Fase A — Fundação de autenticação

- Criar rotas, controller e views de login/logout.
- Proteger o módulo com sessão, CSRF, limitação de tentativas e ciclo seguro de sessão.
- Criar comando Artisan interativo para provisionar usuários administrativos.

**Verificação:** login válido e inválido, rota protegida e logout.

**Commit:** `feat(admin): Adiciona autenticação administrativa`

### [x] Fase B — Layout e navegação

- Criar layout administrativo responsivo.
- Adicionar identificação do sistema, menu “Atividades”, nome do usuário e logout.
- Redirecionar `/admin` para a listagem.

**Verificação:** navegação em desktop e mobile; menu e logout acessíveis.

**Commit:** `feat(admin): Cria layout do gerenciamento`

### [x] Fase C — Listagem paginada

- Criar controller e view da lista.
- Consultar atividades com curso e unidade acadêmica.
- Ordenar por criação e identificador, ambos crescentes.
- Paginar em 20 registros por página e exibir estado vazio.

**Verificação:** nenhuma atividade, uma atividade, 20 atividades e mais de 20 atividades; ordem crescente.

**Commit:** `feat(admin): Lista atividades cadastradas`

### [x] Fase D — Detalhe da atividade

- Criar controller e view de detalhe.
- Carregar dados gerais, responsável, curso, unidade acadêmica e participantes.
- Tratar campos opcionais e atividade inexistente.

**Verificação:** atividade completa, atividade sem links/observações, participantes e URL inexistente.

**Commit:** `feat(admin): Exibe detalhes da atividade`

### [~] Fase E — Qualidade e publicação

- Executar Pint, build de assets e caches do Laravel.
- Validar autenticação, listagem, paginação, detalhe e logout.
- Publicar a tag aprovada e criar usuários administrativos no ambiente de destino.

**Critério de aceite:** `/inscricoes` continua público e funcional; todo acesso a `/admin` exige sessão autenticada.

## 4. Próxima fase

### [ ] Fase F — Exportação CSV de atividades

O detalhamento técnico, critérios de aceite e verificação desta fase ficam no [Plano de exportação CSV de atividades](./exportacao-csv-atividades.md).
