# Publicação Na Hostinger

## 1. Preparar O hPanel

1. Em **Sites > Gerenciar > Configuração do PHP**, selecione PHP 8.2 ou 8.3.
2. Confirme as extensões `pdo_mysql`, `mbstring` e `openssl`.
3. Crie um banco MySQL vazio e anote nome, usuário e senha.
4. Abra o phpMyAdmin desse banco e importe `database/schema.sql` uma única vez.

## 2. Enviar Os Arquivos

Envie o conteúdo do projeto para `public_html`, mantendo `.htaccess` na raiz e a pasta `public` dentro dela. O `.htaccess` encaminha as URLs para `public/` e bloqueia o acesso às pastas internas.

Podem ser omitidos do upload: `.git`, `.github`, `node_modules`, `tests`, `docs` e `.claude`.

## 3. Criar O `.env`

Copie `.env.example` para `.env` dentro de `public_html` e ajuste:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://SEU-DOMINIO
APP_KEY=COLE-UMA-CHAVE-ALEATORIA-DE-64-CARACTERES

DB_DRIVER=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=NOME-DO-BANCO
DB_USERNAME=USUARIO-DO-BANCO
DB_PASSWORD=SENHA-DO-BANCO
```

Mantenha no mesmo arquivo a configuração SMTP já indicada em `.env.example` e preencha `SMTP_PASSWORD`. O `.env` não deve ser enviado ao GitHub nem ficar acessível publicamente.

Para gerar `APP_KEY` pelo terminal:

```bash
php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
```

## 4. Permissões E Validação

1. Garanta permissão de escrita do PHP em `storage/cache` e `storage/logs` (`755` normalmente basta na Hostinger).
2. Com SSH, rode `php tools/preflight.php`.
3. Abra a página inicial, faça uma inscrição real de teste e confira o e-mail e o comprovante.
4. Entre em `/admin` com `admin` e a senha inicial informada para o projeto.
5. Em **Usuários**, troque a senha inicial antes de divulgar a página.
6. Teste **Inscrições**, filtros, detalhes e **Exportar CSV**.

Se as rotas internas retornarem 404, confirme que os dois arquivos `.htaccess` foram enviados. Se aparecer erro de banco, confira os dados em `.env` e se `database/schema.sql` foi importado no banco vazio correto.
