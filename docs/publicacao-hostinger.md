# Publicação na Hostinger

Este roteiro publica a aplicação no subdomínio `snctzo.sicsu.net` sem alterar o site de `sicsu.net`.

## Layout no servidor

```text
/home/u526894748/domains/sicsu.net/
├── public_html/                     # site existente; não alterar
│   └── snctzo/                      # document root de snctzo.sicsu.net
│       ├── index.php                # wrapper versionado em deploy/hostinger
│       ├── .htaccess                # PHP 8.4 e rewrite
│       └── build/                   # assets Vite compilados localmente
└── snctzo-app/                      # clone privado do repositório
    └── apps/web/                    # Laravel, .env, vendor e storage
```

O código Laravel fica fora de `public_html`. Portanto, `.env`, `vendor` e `storage` não são acessíveis pelo navegador.

## Pré-requisitos já concluídos

- Subdomínio `snctzo.sicsu.net` criado em `public_html/snctzo`.
- Banco e usuário `u526894748_snctzo` criados.
- PHP 8.4 e extensões necessárias disponíveis no SSH.

## Publicação inicial

1. Criar o clone privado em `~/domains/sicsu.net/snctzo-app` a partir da tag candidata aprovada.
2. Instalar dependências com `/opt/alt/php84/usr/bin/php`, Composer e `--no-dev`.
3. Criar `apps/web/.env` diretamente no servidor, com `APP_ENV=production`, `APP_DEBUG=false`, conexão exclusiva do banco e `APP_KEY` gerada no servidor.
4. Executar migrations, caches e permissões de escrita somente em `storage` e `bootstrap/cache`.
5. Compilar os assets localmente e copiar `public/build`, `deploy/hostinger/index.php` e `deploy/hostinger/.htaccess` para `public_html/snctzo` por SSH.
6. Remover somente o `default.php` que foi criado para o novo subdomínio após os arquivos públicos terem sido copiados.
7. Configurar ou emitir SSL para `snctzo.sicsu.net` e executar o smoke test.

## E-mail

`EMAIL_CONFIRMACAO_ATIVA=false` deve permanecer no `.env` de produção até haver uma caixa SMTP da Hostinger configurada e validada. Desse modo, nenhuma cópia da inscrição é enviada ou registrada em logs enquanto o e-mail estiver desativado.

Ao configurar o SMTP, preencher as variáveis `MAIL_*` exclusivamente no `.env` do servidor e mudar `EMAIL_CONFIRMACAO_ATIVA=true`.

## Restrições operacionais

- Não usar o auto-deploy Git do hPanel: ele pode executar `composer update`.
- Não executar `php` sem caminho absoluto: o padrão do SSH é PHP 8.0.
- Não colocar o clone, `.env`, `vendor` ou `storage` dentro de `public_html/snctzo`.
- Não modificar `public_html` nem arquivos do site raiz `sicsu.net`.
