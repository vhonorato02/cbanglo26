-- Esquema SQLite equivalente ao MySQL (usado somente nos testes).
PRAGMA foreign_keys = ON;

CREATE TABLE escolas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT NOT NULL,
    cidade TEXT NOT NULL DEFAULT '',
    logo TEXT NOT NULL DEFAULT '',
    whatsapp TEXT NOT NULL DEFAULT '',
    telefone TEXT NOT NULL DEFAULT '',
    endereco TEXT NOT NULL DEFAULT '',
    ordem INTEGER NOT NULL DEFAULT 0,
    ativo INTEGER NOT NULL DEFAULT 1,
    criado_em TEXT NOT NULL,
    atualizado_em TEXT NOT NULL
);

CREATE TABLE series (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT NOT NULL,
    descricao TEXT NOT NULL DEFAULT '',
    ordem INTEGER NOT NULL DEFAULT 0,
    ativo INTEGER NOT NULL DEFAULT 1,
    criado_em TEXT NOT NULL,
    atualizado_em TEXT NOT NULL
);

CREATE TABLE inscricao_status (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    codigo TEXT NOT NULL UNIQUE,
    nome TEXT NOT NULL,
    cor TEXT NOT NULL DEFAULT '#64748b',
    ordem INTEGER NOT NULL DEFAULT 0,
    ativo INTEGER NOT NULL DEFAULT 1
);

CREATE TABLE inscricoes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    protocolo TEXT NOT NULL UNIQUE,
    aluno_slug TEXT NOT NULL UNIQUE,
    aluno_nome TEXT NOT NULL,
    aluno_nascimento TEXT NOT NULL,
    serie_id INTEGER NOT NULL REFERENCES series (id),
    escola_id INTEGER NOT NULL REFERENCES escolas (id),
    escola_atual TEXT NOT NULL,
    responsavel_nome TEXT NOT NULL,
    parentesco TEXT NOT NULL,
    whatsapp TEXT NOT NULL,
    email TEXT NOT NULL,
    cidade TEXT NOT NULL,
    consent_privacidade INTEGER NOT NULL DEFAULT 0,
    consent_contato INTEGER NOT NULL DEFAULT 0,
    consent_versao TEXT NOT NULL DEFAULT 'v1',
    consent_data TEXT NOT NULL,
    status_id INTEGER NOT NULL REFERENCES inscricao_status (id),
    ip_hash TEXT NOT NULL DEFAULT '',
    user_agent TEXT NOT NULL DEFAULT '',
    criado_em TEXT NOT NULL,
    atualizado_em TEXT NOT NULL
);
CREATE INDEX idx_inscricoes_status ON inscricoes (status_id);
CREATE INDEX idx_inscricoes_criado ON inscricoes (criado_em);
CREATE INDEX idx_inscricoes_ip ON inscricoes (ip_hash, criado_em);

CREATE TABLE inscricao_observacoes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    inscricao_id INTEGER NOT NULL REFERENCES inscricoes (id) ON DELETE CASCADE,
    admin_id INTEGER,
    texto TEXT NOT NULL,
    criado_em TEXT NOT NULL
);

CREATE TABLE inscricao_historico (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    inscricao_id INTEGER NOT NULL REFERENCES inscricoes (id) ON DELETE CASCADE,
    admin_id INTEGER,
    campo TEXT NOT NULL,
    valor_anterior TEXT NOT NULL DEFAULT '',
    valor_novo TEXT NOT NULL DEFAULT '',
    criado_em TEXT NOT NULL
);

CREATE TABLE admin_usuarios (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    senha_hash TEXT NOT NULL,
    ativo INTEGER NOT NULL DEFAULT 1,
    ultimo_login TEXT,
    criado_em TEXT NOT NULL,
    atualizado_em TEXT NOT NULL
);

CREATE TABLE login_tentativas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email TEXT NOT NULL,
    ip_hash TEXT NOT NULL,
    sucesso INTEGER NOT NULL DEFAULT 0,
    criado_em TEXT NOT NULL
);

CREATE TABLE admin_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    admin_id INTEGER,
    acao TEXT NOT NULL,
    detalhes TEXT NOT NULL DEFAULT '',
    ip_hash TEXT NOT NULL DEFAULT '',
    criado_em TEXT NOT NULL
);

CREATE TABLE email_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    inscricao_id INTEGER,
    destinatario TEXT NOT NULL,
    assunto TEXT NOT NULL,
    enviado INTEGER NOT NULL DEFAULT 0,
    erro TEXT NOT NULL DEFAULT '',
    criado_em TEXT NOT NULL
);

CREATE TABLE configuracoes (
    chave TEXT PRIMARY KEY,
    valor TEXT NOT NULL
);

CREATE TABLE faqs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    pergunta TEXT NOT NULL,
    resposta TEXT NOT NULL,
    ordem INTEGER NOT NULL DEFAULT 0,
    ativo INTEGER NOT NULL DEFAULT 1,
    criado_em TEXT NOT NULL,
    atualizado_em TEXT NOT NULL
);
