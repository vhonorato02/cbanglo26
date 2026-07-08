# Concurso de Bolsas 2026

Sistema de inscrição online para concurso de bolsas de estudo. Desenvolvido com PHP moderno e banco de dados MySQL.

## 🎯 Funcionalidades

- ✅ Inscrição online com validação em tempo real
- ✅ Consulta de comprovante com protocolo ou e-mail
- ✅ Painel administrativo com dashboard
- ✅ Gerenciamento de escolas, séries e perguntas frequentes
- ✅ Auditoria de operações administrativas
- ✅ Envio de e-mails com notificação
- ✅ Segurança: proteção CSRF, rate limiting, validação de entrada
- ✅ Responsivo e acessível (WCAG 2.1 AA)

## 🛠 Requisitos

- **PHP**: 8.1 ou superior
- **MySQL**: 5.7 ou superior (5.8+ recomendado)
- **Servidor**: Apache com mod_rewrite ativado (ou equivalente)
- **Suporte a SSL/TLS**: HTTPS recomendado

## 📋 Instalação e Deploy

### 1. Preparação do Banco de Dados

Importar o schema SQL no seu servidor MySQL:

```bash
mysql -h seu_host -u seu_usuario -p seu_banco < database/schema.sql
```

Ou via painel de controle (cPanel, Plesk, phpMyAdmin):
1. Vá em Banco de Dados
2. Importe o arquivo `database/schema.sql`

### 2. Upload dos Arquivos

Via FTP, suba todos os arquivos para a raiz do site (`public_html` ou equivalente):

```
✅ Fazer upload:
- app/
- bootstrap/
- config/
- database/
- public/
- resources/
- routes/
- storage/
- package.json
- .htaccess (arquivo oculto — não esqueça!)

❌ Não fazer upload:
- tests/
- docs/
- tools/
- node_modules/
- .git/
- .github/
```

### 3. Configurar Permissões

Após o upload, configure as permissões das pastas:

```bash
chmod 755 storage
chmod 755 storage/cache
chmod 755 storage/logs
```

Ou via FTP (propriedades da pasta):
- `storage/` → 755 ou 775
- `storage/cache/` → 755 ou 775
- `storage/logs/` → 755 ou 775

### 4. Configurar Variáveis de Ambiente

Crie um arquivo `.env` na raiz do projeto (copie de `.env.example`):

```bash
# Copie o arquivo de exemplo
cp .env.example .env
```

Edite o `.env` e preencha:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seu-dominio.com.br
APP_KEY=sua-chave-aleatoria-aqui
APP_TIMEZONE=America/Sao_Paulo

DB_HOST=seu_host_mysql
DB_DATABASE=seu_banco
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha

SMTP_HOST=seu_smtp_host
SMTP_PORT=587
SMTP_USERNAME=seu_email@dominio.com
SMTP_PASSWORD=sua_senha_smtp
SMTP_ENCRYPTION=tls
```

### 5. Configuração Inicial

Acesse `https://seu-dominio.com.br/setup` para:
1. Criar o primeiro usuário administrativo
2. Configurar dados da campanha
3. Definir datas de prova e inscrição

> **Nota**: Defina `APP_SETUP_TOKEN` no `.env` com um token aleatório. Após a configuração, remova ou deixe em branco.

## 🔐 Segurança

### Proteções Implementadas

- ✅ Proteção CSRF em todos os formulários
- ✅ Validação e sanitização de entrada
- ✅ Proteção contra SQL Injection (prepared statements)
- ✅ Rate limiting para login e inscrição
- ✅ Headers de segurança HTTP (CSP, HSTS, X-Frame-Options)
- ✅ Senhas com hash bcrypt
- ✅ Bloqueio de acesso a pastas internas via `.htaccess`

### Checklist de Segurança Pré-Produção

- [ ] `APP_DEBUG=false` no `.env`
- [ ] `APP_KEY` alterado para valor aleatório
- [ ] `APP_SETUP_TOKEN` removido ou desativado
- [ ] Banco de dados com backup
- [ ] HTTPS ativado (certificado SSL/TLS válido)
- [ ] Arquivo `.env` não é acessível via web
- [ ] Pastas `storage/`, `config/`, `app/` protegidas por `.htaccess`

## 📧 Configuração de E-mail

### Com SMTP (Recomendado)

No `.env`, configure:

```env
SMTP_HOST=seu_servidor_smtp.com
SMTP_PORT=587
SMTP_USERNAME=seu_email@dominio.com
SMTP_PASSWORD=sua_senha
SMTP_ENCRYPTION=tls
```

### Sem SMTP

Deixe `SMTP_HOST` vazio. O sistema funcionará normalmente, mas e-mails de notificação não serão enviados.

## 🎨 Personalização

### Dados da Campanha

No painel administrativo, em **Configurações**:
- Nome da campanha
- Chamada principal
- Descrição
- Data e hora da prova
- Mensagem de encerramento
- Datas de início e fim das inscrições

### Escolas e Séries

Gerenciadas no painel em **Escolas** e **Séries**.

### Perguntas Frequentes (FAQ)

Gerenciadas em **Perguntas Frequentes** no painel administrativo.

## 🏗 Estrutura do Projeto

```
app/
  ├── Controllers/       # Controladores
  ├── Core/             # Classes principais (Auth, Router, DB, etc)
  ├── Models/           # Modelos de dados
  ├── Validation/       # Validadores
  └── Views/            # Templates PHP
bootstrap/              # Bootstrap da aplicação
config/                 # Configurações
database/               # Schema e migrations
public/                 # Assets e ponto de entrada (index.php)
resources/              # CSS e JS (originais)
routes/                 # Definição de rotas
storage/                # Cache e logs
tests/                  # Testes
```

## 🧪 Testes

```bash
# Rodar testes localmente
php tests/run.php
```

## 📞 Suporte

Para problemas de deployment:
1. Verifique se `.htaccess` foi enviado
2. Confirme se `storage/` tem permissão de escrita
3. Valide credenciais do banco de dados
4. Verifique logs em `storage/logs/php-error.log`

## 📄 Licença

Projeto proprietário para uso interno.

---

**Desenvolvido com ❤️ para o Concurso de Bolsas 2026**
