# Decisões estruturais do projeto

## 1. Produto e entregas

O sistema gerenciará inscrições de atividades para a SNCTZO. A primeira entrega cobre o formulário público, a validação e a persistência. O backoffice autenticado e o controle offline de presença serão desenvolvidos depois.

O módulo público terá uma única categoria de usuário: o professor responsável, considerado cadastrante e contato oficial. O formulário não exige autenticação.

## 2. Arquitetura da aplicação

- Monorepo.
- Aplicação principal em `apps/web`.
- Monólito modular em Laravel.
- Páginas renderizadas no servidor com Blade.
- HTML, CSS e JavaScript nativos.
- Nenhum framework JavaScript e nenhuma SPA.
- Nenhuma API pública separada no MVP.
- MySQL/MariaDB acessado pelo Eloquent ORM.
- Migrations obrigatórias e versionadas.

Estrutura planejada:

```text
/
├── apps/
│   └── web/                # Aplicação Laravel
├── docs/
├── .editorconfig
├── .gitignore
└── README.md
```

O módulo offline de presença poderá ocupar `apps/presenca` ou outro repositório, conforme a arquitetura definida no futuro.

## 3. Stack

| Componente | Decisão |
|---|---|
| Backend | PHP 8.4 e Laravel 13 |
| Dependências PHP | Composer |
| Frontend | Blade, HTML, CSS e JavaScript nativos |
| Banco | MariaDB 11.8.8 pelo driver MySQL/PDO |
| Idioma | Código, documentação, interface e mensagens em PT-BR |
| Formatação PHP | Laravel Pint |
| Commits | Conventional Commits |

Assets que exigirem preparação serão compilados antes da publicação. A produção não dependerá de Node.js em execução.

## 4. Hospedagem e domínio

- Provedor: Hostinger.
- Plano: Premium Web Hosting.
- Acesso SSH disponível.
- Integração com Git disponível.
- Document root configurável.
- SSL gerenciado disponível.
- Cron jobs disponíveis, mas fora da arquitetura inicial.

A aplicação será publicada em um **subdomínio de `sicsu.net`**, separado do site atual. Não será publicada em um caminho como `sicsu.net/inscricoes`.

O subdomínio terá:

- document root próprio em `domains/sicsu.net/public_html/snctzo`, contendo somente os arquivos públicos;
- aplicação Laravel clonada em `domains/sicsu.net/snctzo-app/apps/web`, fora de `public_html`;
- PHP 8.4;
- SSL próprio ou certificado que cubra o subdomínio;
- `.env` próprio;
- banco e usuário de banco próprios;
- logs próprios;
- deploy independente.

O site existente permanecerá isolado e poderá manter sua versão atual do PHP. A configuração global da hospedagem não será alterada antes da separação dos sites.

## 5. Ambientes

O projeto terá dois ambientes:

- **Local:** desenvolvimento, preparação de dependências, migrations e validação.
- **Produção:** aplicação publicada no subdomínio.

Não haverá staging, Docker ou CI/CD inicialmente.

## 6. Configuração e segredos

- Credenciais serão fornecidas apenas por variáveis de ambiente.
- O `.env` nunca será versionado.
- O repositório poderá conter `.env.example` sem valores secretos.
- Produção usará `APP_ENV=production` e `APP_DEBUG=false`.
- O fuso da aplicação será `America/Sao_Paulo`.
- A abertura e o encerramento das inscrições serão controlados por variáveis de ambiente.
- Sem as duas variáveis de prazo, o formulário permanecerá aberto.
- Horários ausentes não serão exibidos.

O arquivo local `php.sh` contém informações do ambiente e não deverá ser versionado.

## 7. Compatibilidade PHP

O relatório disponível foi gerado sob PHP 8.0.30 e confirmou as extensões necessárias:

- Ctype;
- cURL;
- DOM;
- Fileinfo;
- Mbstring;
- OpenSSL;
- PDO e `pdo_mysql`;
- Session;
- Tokenizer;
- XML;
- OPcache.

As extensões deverão ser verificadas novamente sob PHP 8.4 antes do deploy. Os limites observados foram 1.536 MB para memória, corpo POST e upload, com execução máxima de 360 segundos.

## 8. Deploy

O deploy usará Git e SSH. O procedimento deverá:

1. Publicar a versão aprovada.
2. Instalar dependências de produção com Composer.
3. Preparar os caches do Laravel.
4. Executar migrations.
5. Garantir escrita em `storage` e `bootstrap/cache`.
6. Publicar somente os arquivos de `apps/web/public` no document root do subdomínio por meio do wrapper versionado em `deploy/hostinger`.
7. Validar HTTPS e o envio de e-mail.

Se o PHP do terminal SSH diferir do PHP do subdomínio, os comandos usarão explicitamente o binário do PHP 8.4.

## 9. E-mail

- Provedor: Hostinger Free Business Email.
- Transporte: SMTP autenticado pelo sistema de e-mail do Laravel.
- Servidor Hostinger Email: `smtp.hostinger.com`.
- Porta preferencial: 465 com SSL; alternativa 587 com STARTTLS.
- Credenciais: exclusivamente no `.env`.
- Destinatário: professor responsável.
- Conteúdo: cópia completa da inscrição e dos termos aceitos.
- Momento: envio síncrono depois da confirmação da transação do banco.
- Falha: não desfaz a inscrição; registra erro no log e mantém `email_confirmacao_enviado_em` vazio.
- Ativação: `EMAIL_CONFIRMACAO_ATIVA=false` por padrão. O envio só é habilitado depois da configuração e validação do SMTP de produção; enquanto desabilitado, não envia mensagem, não atualiza o instante de confirmação e não registra conteúdo da inscrição.

O Free Business Email limita cada caixa postal a **100 mensagens enviadas em uma janela móvel de 24 horas**. O limite pode mudar e deverá ser confirmado no hPanel antes da publicação. Como cada inscrição gera uma mensagem, o envio poderá falhar depois que a cota for atingida; o cadastro continuará funcionando.

O envio interno do servidor por `mail()` ou Sendmail também possui limite de 10 mensagens por minuto e 100 por dia, mas não será usado. SMTP foi escolhido por oferecer autenticação e entrega mais confiável.

O e-mail será implementado por último. A aplicação poderá ser publicada sem essa funcionalidade se a cota de desenvolvimento terminar antes dessa etapa.

## 10. Logs

- Desenvolvimento: saída adequada ao processo local.
- Produção: arquivos rotativos em `storage/logs`.
- Dados pessoais e segredos não deverão aparecer nos logs.
- Não haverá plataforma externa de observabilidade no MVP.

## 11. Segurança

- HTTPS obrigatório.
- Document root restrito a `apps/web/public`.
- Proteção CSRF.
- Escape de conteúdo nas páginas.
- Validação completa no backend.
- Honeypot contra robôs.
- Limite de 100 submissões por IP por hora.
- Token de idempotência por preenchimento.
- Registros existentes bloqueados contra edição pública.
- `APP_KEY` exclusiva de produção.
- Erros detalhados desabilitados em produção.

Professores serão pesquisados por e-mail exato no backend. Alunos da instituição serão pré-carregados no frontend para autocomplete. A exposição pública de nomes e cursos dos alunos foi aceita temporariamente e registrada como débito de segurança.

## 12. Qualidade e processo

- Não serão escritos testes automatizados nesta etapa para reduzir consumo de tempo e tokens.
- Os fluxos principais deverão passar por validação manual antes da publicação.
- Não haverá regras adicionais para branches.
- Não haverá documentação de API.
- Não haverá registros ADR separados.

## 13. Decisões de modelagem

- “Atividade” substitui “projeto” como termo oficial.
- FCBS e FCEE são instituições independentes.
- A atividade guarda apenas o curso principal; a instituição é derivada do curso.
- O professor responsável pertence à instituição da atividade.
- Professores participantes podem pertencer a outras instituições.
- Alunos participantes podem pertencer a outros cursos, mas somente dentro da instituição da atividade.
- Professor é identificado de forma única pelo e-mail.
- Aluno não possui chave forte; duplicidades globais são um risco aceito.
- Instituições e cursos têm unicidade sem diferenciar maiúsculas ou acentos.
- A atividade armazena a versão e o instante dos termos aceitos, não cada checkbox.
- O modelo não possui entidade `evento` no MVP.

## 14. Fora do MVP e débitos registrados

- Backoffice autenticado.
- Perfis e permissões avançados.
- Módulo offline de presença.
- Persistência de rascunhos.
- Edição ou cancelamento público após envio.
- Entidade para eventos e várias edições.
- Administração de prazos e horários.
- Busca paginada e protegida de alunos no backend.
- Tratamento específico de autorizações para menores.
- Reenvio administrativo de e-mails.
- Filas, workers e processos persistentes.
- Observabilidade externa.
- Docker, staging e CI/CD.
- Testes automatizados.

## 15. Referências operacionais

- [Versões do Laravel e requisitos de PHP](https://laravel.com/docs/13.x/releases)
- [Configuração SMTP da Hostinger](https://support.hostinger.com/en/articles/1575756-how-to-get-email-account-configuration-details-for-hostinger-email)
- [Limites do Hostinger Email](https://www.hostinger.com/support/4625828-parameters-and-limits-of-hostinger-email/)
- [Limites da hospedagem Hostinger](https://www.hostinger.com/support/6976044-parameters-and-limits-of-hosting-plans-in-hostinger/)
- [Configuração de PHP por subdomínio](https://www.hostinger.com/support/4047803-how-to-change-the-php-version-for-subfolders-or-subdomains-in-hostinger/)
