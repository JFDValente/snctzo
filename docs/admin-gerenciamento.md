# Gerenciamento administrativo — atividades

## 1. Objetivo e escopo

Este documento define a primeira entrega do gerenciamento autenticado da SNCTZO 2026. O módulo permitirá consultar as atividades submetidas pelo formulário público, sem alterar ou excluir dados.

O módulo será servido no mesmo subdomínio da aplicação:

- entrada administrativa: `https://snctzo.sicsu.net/admin`;
- listagem: `https://snctzo.sicsu.net/admin/atividades`;
- detalhe: `https://snctzo.sicsu.net/admin/atividades/{id}`.

O escopo desta entrega é intencionalmente somente leitura. Edição, exclusão, filtros, busca, exportação, perfis de acesso e administração do catálogo ficam fora desta versão.

## 2. Decisões confirmadas

| Tema | Decisão |
|---|---|
| Stack | Laravel 13, Blade, CSS e JavaScript nativos, com MariaDB existente |
| Autenticação | E-mail e senha, por sessão nativa do Laravel |
| Autorização | Todo usuário autenticado acessa todos os recursos administrativos |
| Cadastro público de usuários | Não haverá |
| Recuperação de senha | Não haverá nesta etapa |
| Usuário inicial | José — `jfdvalente@gmail.com` |
| Senha inicial | Será criada diretamente no ambiente de destino; não será documentada nem versionada |
| Data da inscrição | Formato `dd/mm/aaaa às HH:mm`, no fuso `America/Sao_Paulo` |
| Professor responsável | É exibido separadamente; só aparece como participante se estiver efetivamente vinculado à atividade como tal |

## 3. Controle de acesso

### 3.1 Rotas

| Método | Rota | Nome sugerido | Regra |
|---|---|---|---|
| `GET` | `/admin/login` | `admin.login` | Exibe o login para visitante |
| `POST` | `/admin/login` | `admin.login.autenticar` | Autentica por e-mail e senha |
| `POST` | `/admin/logout` | `admin.logout` | Encerra a sessão do usuário autenticado |
| `GET` | `/admin` | `admin.inicio` | Redireciona para a listagem |
| `GET` | `/admin/atividades` | `admin.atividades.index` | Lista atividades |
| `GET` | `/admin/atividades/{atividade}` | `admin.atividades.show` | Mostra uma atividade |

As três últimas rotas ficarão dentro do middleware `auth`. Visitantes que tentarem acessá-las serão redirecionados ao login.

### 3.2 Regras de segurança

- Usar o guard de sessão e o model `User` padrão já existentes no Laravel.
- Senhas devem continuar armazenadas exclusivamente com hash; nunca em texto puro.
- Aplicar CSRF ao login e ao logout.
- Limitar tentativas de login por e-mail e IP, com mensagem genérica para não revelar se um e-mail existe.
- Regenerar o identificador da sessão após login e invalidar sessão e token CSRF no logout.
- Não criar tela de registro, alteração de senha ou recuperação de senha nesta entrega.
- Criar o primeiro usuário administrativo por comando Artisan interativo, solicitando a senha no terminal. O comando poderá também impedir criação acidental de e-mail duplicado.

## 4. Estrutura visual e navegação

O gerenciamento terá layout Blade próprio, responsivo e independente do layout do formulário público.

Em telas largas, haverá barra lateral ou cabeçalho persistente com:

- identificação “Gerenciamento SNCTZO 2026”;
- menu “Atividades”;
- nome do usuário autenticado;
- ação “Sair”.

Em telas menores, a navegação será recolhida sem introduzir framework JavaScript. Nesta primeira entrega, “Atividades” será o único item do menu.

## 5. Listagem de atividades

### 5.1 Conteúdo da tela

A rota `/admin/atividades` apresentará uma tabela com uma linha por atividade e somente estas colunas:

| Coluna | Fonte |
|---|---|
| Nome da atividade | `atividades.nome` |
| Unidade acadêmica | instituição associada ao curso principal da atividade |
| Curso | `cursos.nome` |
| Data de inscrição | `atividades.created_at`, formatado conforme a seção 2 |

O nome da atividade, ou uma ação discreta “Visualizar”, levará para `/admin/atividades/{id}`.

### 5.2 Consulta e paginação

- Ordenar por `atividades.created_at` em ordem crescente.
- Usar `atividades.id` crescente como desempate, evitando troca de posição entre páginas.
- Exibir 20 atividades por página.
- Usar paginação do Laravel e preservar o estado da página na navegação.
- Carregar relações necessárias de forma antecipada para evitar consultas repetidas por linha.
- Quando não houver inscrições, exibir estado vazio claro, sem tabela vazia.

Não haverá filtros, busca, ordenação pela interface, edição ou exclusão nesta fase.

## 6. Visualização da atividade

A rota `/admin/atividades/{id}` exibirá os dados compactamente, agrupados em blocos. A página será apenas de leitura e retornará `404` para identificador inexistente.

### 6.1 Dados gerais

- nome da atividade;
- unidade acadêmica;
- curso principal;
- data e hora da inscrição;
- professor responsável: nome e e-mail;
- participação em 20/10 e 21/10;
- resumo;
- observações, quando existentes;
- Instagram, Facebook, site e outros links, quando existentes.

### 6.2 Participantes

Os participantes serão exibidos em tabela compacta, separando o tipo na própria coluna:

| Tipo | Nome completo | Curso ou unidade acadêmica |
|---|---|---|
| Aluno | nome do aluno | curso do aluno |
| Professor | nome do professor | unidade acadêmica do professor |

O professor responsável será mostrado no bloco próprio. Ele não deve ser duplicado na tabela de participantes, exceto quando houver vínculo explícito em `atividade_professor`.

### 6.3 Dados deliberadamente omitidos

Não exibir os checkboxes individuais de aceite nem a versão/instante dos termos. Também não exibir tokens técnicos de submissão, marcas internas de envio de e-mail ou identificadores do banco.

## 7. Impacto no modelo de dados

Não é necessária alteração estrutural no banco para este módulo. As tabelas `users`, `atividades`, `cursos`, `instituicoes`, `professores`, `alunos`, `atividade_professor` e `atividade_aluno` já contêm os dados necessários.

A criação do usuário inicial usará a tabela `users` existente. Se for criado um comando Artisan, ele não exigirá migration.

## 8. Plano de implementação

### [x] Fase A — Fundação de autenticação

**Objetivo:** restringir o módulo administrativo a usuários autorizados.

- Criar rotas, controller e views de login/logout.
- Configurar redirecionamento de visitantes ao login.
- Aplicar limitação de tentativas, CSRF e ciclo seguro de sessão.
- Criar comando Artisan interativo para provisionar usuários administrativos.
- Provisionar José (`jfdvalente@gmail.com`) em ambiente local e produção, com senha digitada diretamente no terminal.

**Verificação manual:** login válido, login inválido, bloqueio temporário por repetição, acesso direto a rota protegida e logout.

**Commit sugerido:** `feat(admin): Adiciona autenticação administrativa`

### [x] Fase B — Layout e navegação

**Objetivo:** estabelecer o shell visual do gerenciamento.

- Criar layout administrativo responsivo.
- Adicionar identificação do sistema, menu “Atividades”, nome do usuário e logout.
- Criar redirecionamento de `/admin` para a listagem.

**Verificação manual:** navegação em tela desktop e mobile; menu e logout acessíveis.

**Commit sugerido:** `feat(admin): Cria layout do gerenciamento`

### [x] Fase C — Listagem paginada

**Objetivo:** consultar inscrições de forma eficiente e previsível.

- Criar controller e view da lista.
- Consultar atividades com curso e unidade acadêmica.
- Ordenar por data de criação e identificador, ambos crescentes.
- Paginar em 20 registros por página.
- Criar estados de lista vazia e navegação de páginas.

**Verificação manual:** nenhuma atividade, uma atividade, 20 atividades e mais de 20 atividades; confirmação da ordem ascendente.

**Commit sugerido:** `feat(admin): Lista atividades cadastradas`

### [x] Fase D — Detalhe da atividade

**Objetivo:** permitir a consulta integral da submissão, sem os aceites.

- Criar controller e view de detalhe.
- Carregar dados gerais, responsável, curso, unidade acadêmica e participantes.
- Apresentar links somente quando preenchidos.
- Tratar identificador inexistente com página 404 padrão.
- Adicionar navegação de retorno à listagem.

**Verificação manual:** atividade com todos os campos, atividade sem observação/links, alunos, professores participantes e URL inexistente.

**Commit sugerido:** `feat(admin): Exibe detalhes da atividade`

### [~] Fase E — Qualidade e publicação

**Objetivo:** validar e publicar sem afetar o formulário público.

- Executar Pint nos arquivos PHP alterados.
- Executar build de assets e caches do Laravel.
- Validar manualmente autenticação, listagem, paginação, detalhe e logout.
- Atualizar `docs/publicacao-hostinger.md` caso o processo ganhe o comando de criação de usuário.
- Publicar uma tag de versão e executar o procedimento padrão de deploy.

**Critério de aceite:** o formulário em `/inscricoes` permanece público e funcional; `/admin` e todas as rotas administrativas exigem sessão autenticada.

As validações locais de sintaxe, Pint, build, cache, proteção de rota, login, listagem vazia, detalhe e logout foram executadas. A publicação em produção e a criação do usuário administrativo na Hostinger permanecem pendentes.

## 9. Fora do escopo desta entrega

- Perfis, papéis e permissões;
- criação, edição ou exclusão de atividades;
- busca, filtros, exportação ou relatórios;
- gestão de instituições, cursos, professores e alunos;
- redefinição de senha por e-mail;
- auditoria de acessos ou trilha de alterações;
- dashboard com indicadores;
- módulo de presença offline.
