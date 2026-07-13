<?php

declare(strict_types=1);

/**
 * Cria um banco SQLite de desenvolvimento/teste com o seed completo.
 * Uso: php tests/seed-sqlite.php caminho/banco.sqlite [senha-admin]
 */

require dirname(__DIR__) . '/bootstrap/autoload.php';

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}
require BASE_PATH . '/app/Core/Helpers.php';

$destino = $argv[1] ?? BASE_PATH . '/database/dev.sqlite';
$senhaAdmin = $argv[2] ?? 'cbanglo26##';

@unlink($destino);

\App\Core\Database::configure(['driver' => 'sqlite', 'sqlite_path' => $destino]);
$pdo = \App\Core\Database::pdo();
$pdo->exec((string) file_get_contents(BASE_PATH . '/tests/schema-sqlite.sql'));

$agora = date('Y-m-d H:i:s');
$pdo->exec("INSERT INTO escolas (nome, cidade, logo, whatsapp, telefone, ordem, ativo, criado_em, atualizado_em) VALUES
    ('Anglo Pinda', 'Pindamonhangaba', 'assets/img/logos/anglo-pinda.png', '5512991936523', '1236443266', 1, 1, '{$agora}', '{$agora}'),
    ('Colégio Fênix', 'Guaratinguetá', 'assets/img/logos/colegio-fenix.png', '5512991169782', '1231253477', 2, 1, '{$agora}', '{$agora}'),
    ('Colégio Drummond', 'Lorena', 'assets/img/logos/colegio-drummond.png', '5512991856338', '', 3, 1, '{$agora}', '{$agora}'),
    ('Anglo Cruzeiro', 'Cruzeiro', 'assets/img/logos/anglo-cruzeiro.png', '5512991052675', '1231445144', 4, 1, '{$agora}', '{$agora}')");
$pdo->exec("INSERT INTO series (nome, descricao, ordem, ativo, criado_em, atualizado_em) VALUES
    ('6º ano — Ensino Fundamental', 'Anos Finais do Ensino Fundamental', 1, 1, '{$agora}', '{$agora}'),
    ('1ª série — Ensino Médio', 'Ingresso na 1ª série do Ensino Médio', 2, 1, '{$agora}', '{$agora}')");
$pdo->exec("INSERT INTO inscricao_status (codigo, nome, cor, ordem, ativo) VALUES
    ('recebida', 'Recebida', '#0284c7', 1, 1),
    ('em_analise', 'Em análise', '#7c3aed', 2, 1),
    ('confirmada', 'Confirmada', '#16a34a', 3, 1),
    ('incompleta', 'Incompleta', '#d97706', 4, 1),
    ('cancelada', 'Cancelada', '#dc2626', 5, 1),
    ('compareceu', 'Compareceu', '#0d9488', 6, 1),
    ('ausente', 'Ausente', '#64748b', 7, 1),
    ('classificada', 'Classificada', '#ca8a04', 8, 1)");
$pdo->exec("INSERT INTO configuracoes (chave, valor) VALUES
    ('campanha_nome', 'Concurso de Bolsas 2026'),
    ('campanha_chamada', 'Escolha a unidade, selecione uma data disponível e conclua a inscrição em poucos passos.'),
    ('campanha_descricao', 'Concurso de Bolsas para estudantes que vão ingressar no 6º ano do Ensino Fundamental ou na 1ª série do Ensino Médio.'),
    ('data_prova', '2026-10-17'),
    ('hora_prova', '09:00'),
    ('inscricoes_abertas', '1'),
    ('inscricoes_inicio', ''),
    ('inscricoes_fim', ''),
    ('inscricoes_limite', '0'),
    ('mensagem_confirmacao', 'Inscrição confirmada. Guarde o número de protocolo; a unidade escolhida entrará em contato com as orientações da prova.'),
    ('mensagem_encerrada', 'As inscrições estão encerradas.'),
    ('contato_whatsapp', ''),
    ('contato_email', ''),
    ('consent_versao', 'v1-2026'),
    ('resultado_info', '')");
$pdo->exec("INSERT INTO faqs (pergunta, resposta, ordem, ativo, criado_em, atualizado_em) VALUES
    ('Quem pode participar?', 'Estudantes que vão ingressar no 6º ano EF ou na 1ª série EM.', 1, 1, '{$agora}', '{$agora}'),
    ('Quando será a prova?', 'Anglo Pinda terá prova em 26 de setembro ou 17 de outubro, às 9h. Fênix, Drummond e Anglo Cruzeiro terão prova em 17 de outubro, às 9h.', 2, 1, '{$agora}', '{$agora}')");

$stmt = $pdo->prepare("INSERT INTO admin_usuarios (nome, email, senha_hash, ativo, criado_em, atualizado_em)
    VALUES ('Administrador', 'admin', :hash, 1, '{$agora}', '{$agora}')");
$stmt->execute([':hash' => password_hash($senhaAdmin, PASSWORD_DEFAULT)]);

\App\Core\Config::clearCache();

echo "Banco SQLite criado em {$destino} (admin: admin / {$senhaAdmin})\n";
