# Concurso de Bolsas 2026

Sistema PHP para inscrição online, comprovante e painel administrativo do Concurso de Bolsas 2026.

## Requisitos

- PHP 7.1 ou superior; na Hostinger, use PHP 8.2 ou 8.3
- MySQL 5.7/MariaDB 10.3 em produção, ou SQLite para preview local
- Apache com `.htaccess` em hospedagem compartilhada
- Node.js apenas para build dos assets CSS/JS

## Preview Local Sem XAMPP

Crie o banco SQLite de desenvolvimento:

```bash
php tests/seed-sqlite.php database/dev.sqlite
```

Suba o servidor embutido do PHP:

```bash
php -S 127.0.0.1:8096 -t public tests/server-router.php
```

Abra `http://127.0.0.1:8096/`.

Acesso inicial do painel:

- Usuário: `admin`
- Senha: `cbanglo26##`

## Datas Da Prova

- Anglo Pinda: 26 de setembro ou 17 de outubro, às 9h
- Colégio Fênix, Colégio Drummond e Anglo Cruzeiro: 17 de outubro, às 9h

O formulário valida a data conforme a unidade escolhida.

## E-mail

O envio de confirmações usa SMTP com SSL implícito na porta 465. O arquivo `.env.example` já contém o host, o remetente e as portas corretas; somente as senhas devem ser preenchidas no `.env`, que não é versionado.

O POP3 com SSL na porta 995 serve apenas para configurar a caixa em um cliente de e-mail. A aplicação envia confirmações, mas não lê nem remove mensagens da caixa de entrada.

## Deploy Na Hostinger

Siga o checklist completo em [`DEPLOY-HOSTINGER.md`](DEPLOY-HOSTINGER.md). O resumo é:

1. Rode o build dos assets:

```bash
npm install
npm run build
```

2. Crie um banco vazio e importe `database/schema.sql` pelo phpMyAdmin.

3. Configure `.env` a partir de `.env.example`, com `APP_URL`, `APP_KEY`, `DB_DRIVER=mysql` e as credenciais reais do banco e do e-mail.

4. Envie para a hospedagem:

- `app/`
- `bootstrap/`
- `config/`
- `database/`
- `public/`
- `resources/`
- `routes/`
- `storage/`
- `.htaccess`

Não envie `tests/`, `node_modules/`, `.git/` nem o `.env` usado no preview local. Depois do primeiro login, altere a senha inicial em **Usuários**.

Com acesso SSH, valide a hospedagem antes de abrir as inscrições:

```bash
php tools/preflight.php
```

## Testes

```bash
php tests/run.php
php tests/http_smoke.php http://127.0.0.1:8096
```

## Segurança

- CSRF nos formulários
- Senhas com `password_hash`
- Prepared statements
- Rate limit para login e inscrição
- Cabeçalhos HTTP de segurança
- Registro de consentimento e auditoria administrativa
