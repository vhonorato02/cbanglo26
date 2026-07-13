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

## Deploy Na Hostinger Sem SSH

O pacote de publicação inclui os assets compilados, o `.env` de produção e o instalador do banco. Para gerá-lo:

```powershell
powershell -ExecutionPolicy Bypass -File tools/build-hostinger-package.ps1
```

Depois, siga [`DEPLOY-HOSTINGER.md`](DEPLOY-HOSTINGER.md). Basta criar um banco vazio no hPanel, extrair o ZIP em `public_html` e abrir o endereço privado indicado em `COMO-PUBLICAR.txt`. Não é necessário SSH nem phpMyAdmin.

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
