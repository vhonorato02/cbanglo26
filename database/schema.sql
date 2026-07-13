-- ============================================================
-- Concurso de Bolsas — Esquema do banco de dados
-- MySQL 5.7+ / MariaDB 10.3+ — charset utf8mb4
--
-- Importe este arquivo inteiro pelo phpMyAdmin (aba "Importar")
-- dentro de um banco vazio criado no hPanel. Ele cria as tabelas e os
-- dados iniciais da campanha.
-- ============================================================

SET NAMES utf8mb4;
SET time_zone = '-03:00';

-- ------------------------------------------------------------
-- Escolas participantes
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS escolas (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome VARCHAR(120) NOT NULL,
    cidade VARCHAR(120) NOT NULL DEFAULT '',
    logo VARCHAR(190) NOT NULL DEFAULT '',
    whatsapp VARCHAR(20) NOT NULL DEFAULT '',
    telefone VARCHAR(20) NOT NULL DEFAULT '',
    endereco VARCHAR(255) NOT NULL DEFAULT '',
    ordem SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em DATETIME NOT NULL,
    atualizado_em DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_escolas_ativo (ativo, ordem)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Séries / anos disponíveis
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS series (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome VARCHAR(120) NOT NULL,
    descricao VARCHAR(190) NOT NULL DEFAULT '',
    ordem SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em DATETIME NOT NULL,
    atualizado_em DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_series_ativo (ativo, ordem)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Status configuráveis das inscrições
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS inscricao_status (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    codigo VARCHAR(40) NOT NULL,
    nome VARCHAR(80) NOT NULL,
    cor VARCHAR(7) NOT NULL DEFAULT '#64748b',
    ordem SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY uq_status_codigo (codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Inscrições (estudante + responsável + consentimento LGPD)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS inscricoes (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    protocolo VARCHAR(20) NOT NULL,
    aluno_slug VARCHAR(190) NOT NULL,
    aluno_nome VARCHAR(150) NOT NULL,
    aluno_nascimento DATE NOT NULL,
    serie_id INT UNSIGNED NOT NULL,
    escola_id INT UNSIGNED NOT NULL,
    data_prova DATE NOT NULL,
    escola_atual VARCHAR(150) NOT NULL,
    responsavel_nome VARCHAR(150) NOT NULL,
    parentesco VARCHAR(60) NOT NULL,
    whatsapp VARCHAR(15) NOT NULL,
    email VARCHAR(190) NOT NULL,
    cidade VARCHAR(120) NOT NULL,
    consent_privacidade TINYINT(1) NOT NULL DEFAULT 0,
    consent_contato TINYINT(1) NOT NULL DEFAULT 0,
    consent_versao VARCHAR(20) NOT NULL DEFAULT 'v1',
    consent_data DATETIME NOT NULL,
    status_id INT UNSIGNED NOT NULL,
    ip_hash CHAR(64) NOT NULL DEFAULT '',
    user_agent VARCHAR(250) NOT NULL DEFAULT '',
    criado_em DATETIME NOT NULL,
    atualizado_em DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_inscricoes_protocolo (protocolo),
    UNIQUE KEY uq_inscricoes_aluno (aluno_slug),
    KEY idx_inscricoes_status (status_id),
    KEY idx_inscricoes_escola (escola_id),
    KEY idx_inscricoes_serie (serie_id),
    KEY idx_inscricoes_prova (data_prova),
    KEY idx_inscricoes_criado (criado_em),
    KEY idx_inscricoes_email (email),
    KEY idx_inscricoes_ip (ip_hash, criado_em),
    CONSTRAINT fk_inscricoes_serie FOREIGN KEY (serie_id) REFERENCES series (id),
    CONSTRAINT fk_inscricoes_escola FOREIGN KEY (escola_id) REFERENCES escolas (id),
    CONSTRAINT fk_inscricoes_status FOREIGN KEY (status_id) REFERENCES inscricao_status (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Observações internas por inscrição
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS inscricao_observacoes (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    inscricao_id INT UNSIGNED NOT NULL,
    admin_id INT UNSIGNED NULL,
    texto TEXT NOT NULL,
    criado_em DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_obs_inscricao (inscricao_id),
    CONSTRAINT fk_obs_inscricao FOREIGN KEY (inscricao_id) REFERENCES inscricoes (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Histórico de alterações das inscrições
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS inscricao_historico (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    inscricao_id INT UNSIGNED NOT NULL,
    admin_id INT UNSIGNED NULL,
    campo VARCHAR(60) NOT NULL,
    valor_anterior VARCHAR(500) NOT NULL DEFAULT '',
    valor_novo VARCHAR(500) NOT NULL DEFAULT '',
    criado_em DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_hist_inscricao (inscricao_id),
    CONSTRAINT fk_hist_inscricao FOREIGN KEY (inscricao_id) REFERENCES inscricoes (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Usuários administrativos
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS admin_usuarios (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL,
    senha_hash VARCHAR(255) NOT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    ultimo_login DATETIME NULL,
    criado_em DATETIME NOT NULL,
    atualizado_em DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_admin_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tentativas de login (limite e bloqueio temporário)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS login_tentativas (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    email VARCHAR(190) NOT NULL,
    ip_hash CHAR(64) NOT NULL,
    sucesso TINYINT(1) NOT NULL DEFAULT 0,
    criado_em DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_login_email (email, criado_em),
    KEY idx_login_ip (ip_hash, criado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Trilha de auditoria administrativa
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS admin_logs (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    admin_id INT UNSIGNED NULL,
    acao VARCHAR(100) NOT NULL,
    detalhes VARCHAR(2000) NOT NULL DEFAULT '',
    ip_hash CHAR(64) NOT NULL DEFAULT '',
    criado_em DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_logs_criado (criado_em),
    KEY idx_logs_admin (admin_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Log de e-mails (sucesso/falha — inscrição nunca depende disso)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS email_logs (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    inscricao_id INT UNSIGNED NULL,
    destinatario VARCHAR(190) NOT NULL,
    assunto VARCHAR(190) NOT NULL,
    enviado TINYINT(1) NOT NULL DEFAULT 0,
    erro VARCHAR(500) NOT NULL DEFAULT '',
    criado_em DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_email_inscricao (inscricao_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Configurações da campanha (chave/valor, editável no painel)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS configuracoes (
    chave VARCHAR(80) NOT NULL,
    valor TEXT NOT NULL,
    PRIMARY KEY (chave)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Perguntas frequentes
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS faqs (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    pergunta VARCHAR(255) NOT NULL,
    resposta TEXT NOT NULL,
    ordem SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em DATETIME NOT NULL,
    atualizado_em DATETIME NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DADOS INICIAIS (seed)
-- ============================================================

-- Escolas — ordem oficial da campanha
INSERT INTO escolas (nome, cidade, logo, whatsapp, telefone, ordem, ativo, criado_em, atualizado_em) VALUES
('Anglo Pinda', 'Pindamonhangaba', 'assets/img/logos/anglo-pinda.png', '5512991936523', '1236443266', 1, 1, NOW(), NOW()),
('Colégio Fênix', 'Guaratinguetá', 'assets/img/logos/colegio-fenix.png', '5512991169782', '1231253477', 2, 1, NOW(), NOW()),
('Colégio Drummond', 'Lorena', 'assets/img/logos/colegio-drummond.png', '5512991856338', '', 3, 1, NOW(), NOW()),
('Anglo Cruzeiro', 'Cruzeiro', 'assets/img/logos/anglo-cruzeiro.png', '5512991052675', '1231445144', 4, 1, NOW(), NOW());

-- Séries — conforme o material oficial (6º ano EF e 1ª série EM)
INSERT INTO series (nome, descricao, ordem, ativo, criado_em, atualizado_em) VALUES
('6º ano — Ensino Fundamental', 'Anos Finais do Ensino Fundamental', 1, 1, NOW(), NOW()),
('1ª série — Ensino Médio', 'Ingresso na 1ª série do Ensino Médio', 2, 1, NOW(), NOW());

-- Status das inscrições
INSERT INTO inscricao_status (codigo, nome, cor, ordem, ativo) VALUES
('recebida',     'Recebida',      '#0284c7', 1, 1),
('em_analise',   'Em análise',    '#7c3aed', 2, 1),
('confirmada',   'Confirmada',    '#16a34a', 3, 1),
('incompleta',   'Incompleta',    '#d97706', 4, 1),
('cancelada',    'Cancelada',     '#dc2626', 5, 1),
('compareceu',   'Compareceu',    '#0d9488', 6, 1),
('ausente',      'Ausente',       '#64748b', 7, 1),
('classificada', 'Classificada',  '#ca8a04', 8, 1);

-- Configurações da campanha
-- Os textos podem ser revisados no painel administrativo após a importação.
INSERT INTO configuracoes (chave, valor) VALUES
('campanha_nome', 'Concurso de Bolsas 2026'),
('campanha_chamada', 'Escolha a unidade, selecione uma data disponível e conclua a inscrição em poucos passos.'),
('campanha_descricao', 'Concurso de Bolsas para estudantes que vão ingressar no 6º ano do Ensino Fundamental ou na 1ª série do Ensino Médio. Anglo Pinda realiza prova em 26 de setembro ou 17 de outubro, às 9h; Fênix, Drummond e Anglo Cruzeiro realizam em 17 de outubro, às 9h.'),
('data_prova', '2026-10-17'),
('hora_prova', '09:00'),
('inscricoes_abertas', '1'),
('inscricoes_inicio', ''),
('inscricoes_fim', '2026-10-16'),
('inscricoes_limite', '0'),
('mensagem_confirmacao', 'Inscrição confirmada! Guarde o número de protocolo. Em breve a escola escolhida entrará em contato pelo WhatsApp com as orientações da prova.'),
('mensagem_encerrada', 'As inscrições do Concurso de Bolsas estão encerradas. Acompanhe as redes sociais das escolas para novas oportunidades.'),
('contato_whatsapp', ''),
('contato_email', ''),
('consent_versao', 'v1-2026'),
('resultado_info', '');

-- Usuário administrativo inicial
-- Login: admin / Senha: cbanglo26##
INSERT INTO admin_usuarios (nome, email, senha_hash, ativo, criado_em, atualizado_em) VALUES
('Administrador', 'admin', '$2y$10$LVG/67Lv.ldQKcuNcTiWfuMfUcGR6cWoMXNpmXYiMI7Q05n5ovbAK', 1, NOW(), NOW());

-- Perguntas frequentes iniciais (respostas baseadas somente no
-- material oficial; personalize no painel administrativo)
INSERT INTO faqs (pergunta, resposta, ordem, ativo, criado_em, atualizado_em) VALUES
('Quem pode participar do Concurso de Bolsas?', 'Estudantes que vão ingressar no 6º ano do Ensino Fundamental (Anos Finais) ou na 1ª série do Ensino Médio em uma das quatro escolas participantes: Anglo Pinda, Colégio Fênix, Colégio Drummond e Anglo Cruzeiro.', 1, 1, NOW(), NOW()),
('Quando será a prova?', 'Anglo Pinda terá prova em 26 de setembro ou 17 de outubro, às 9h. Colégio Fênix, Colégio Drummond e Anglo Cruzeiro terão prova em 17 de outubro, às 9h. O aluno escolhe a data de acordo com a disponibilidade da unidade.', 2, 1, NOW(), NOW()),
('Como faço a inscrição?', 'Preencha o formulário de inscrição nesta página. Ao final, você recebe um número de protocolo e um e-mail de confirmação. A inscrição é feita pelo responsável do estudante.', 3, 1, NOW(), NOW()),
('Posso me inscrever em mais de uma escola?', 'Cada estudante participa com uma única inscrição, na escola escolhida no formulário. Se precisar alterar a escolha, entre em contato com a escola.', 4, 1, NOW(), NOW()),
('Como recebo as orientações da prova?', 'Após a inscrição, a escola escolhida entra em contato pelo WhatsApp e pelo e-mail cadastrados com todas as orientações sobre a prova.', 5, 1, NOW(), NOW());
