<?php

declare(strict_types=1);

/**
 * Bootstrap dos testes: banco SQLite em memória + seeds mínimos.
 */

use App\Core\Database;
use App\Core\Env;

require dirname(__DIR__) . '/bootstrap/autoload.php';

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

require BASE_PATH . '/app/Core/Helpers.php';

date_default_timezone_set('America/Sao_Paulo');
error_reporting(E_ALL);
ini_set('display_errors', '1');

Env::set('APP_KEY', 'chave-de-teste-nao-usar-em-producao');
Env::set('APP_URL', 'http://localhost:8085');

$_SERVER['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

/** Reinicia o banco de teste (SQLite em memória) com seeds. */
function test_reset_db(): void
{
    Database::configure([
        'driver' => 'sqlite',
        'sqlite_path' => ':memory:',
    ]);
    $pdo = Database::pdo();
    $pdo->exec((string) file_get_contents(BASE_PATH . '/tests/schema-sqlite.sql'));

    $agora = date('Y-m-d H:i:s');
    $pdo->exec("INSERT INTO escolas (nome, cidade, logo, ordem, ativo, criado_em, atualizado_em) VALUES
        ('Anglo Pinda', 'Pindamonhangaba', 'assets/img/logos/anglo-pinda.png', 1, 1, '{$agora}', '{$agora}'),
        ('Colégio Fênix', '', 'assets/img/logos/colegio-fenix.png', 2, 1, '{$agora}', '{$agora}'),
        ('Colégio Drummond', '', 'assets/img/logos/colegio-drummond.png', 3, 1, '{$agora}', '{$agora}'),
        ('Anglo Cruzeiro', 'Cruzeiro', 'assets/img/logos/anglo-cruzeiro.png', 4, 1, '{$agora}', '{$agora}')");
    $pdo->exec("INSERT INTO series (nome, descricao, ordem, ativo, criado_em, atualizado_em) VALUES
        ('6º ano — Ensino Fundamental', 'Anos Finais', 1, 1, '{$agora}', '{$agora}'),
        ('1ª série — Ensino Médio', '', 2, 1, '{$agora}', '{$agora}')");
    $pdo->exec("INSERT INTO inscricao_status (codigo, nome, cor, ordem, ativo) VALUES
        ('recebida', 'Recebida', '#0284c7', 1, 1),
        ('em_analise', 'Em análise', '#7c3aed', 2, 1),
        ('confirmada', 'Confirmada', '#16a34a', 3, 1),
        ('cancelada', 'Cancelada', '#dc2626', 5, 1)");
    $pdo->exec("INSERT INTO configuracoes (chave, valor) VALUES
        ('campanha_nome', 'Concurso de Bolsas 2026'),
        ('inscricoes_abertas', '1'),
        ('inscricoes_inicio', ''),
        ('inscricoes_fim', ''),
        ('inscricoes_limite', '0'),
        ('consent_versao', 'v1-teste')");

    \App\Core\Config::clearCache();
}

/** Dados válidos de inscrição para os testes. */
function test_inscricao_valida(array $override = []): array
{
    return array_merge([
        'aluno_nome' => 'Maria Clara de Souza',
        'aluno_nascimento' => '10/03/2014',
        'serie_id' => '1',
        'escola_id' => '1',
        'escola_atual' => 'EMEF Central',
        'responsavel_nome' => 'Ana Paula de Souza',
        'parentesco' => 'Mãe',
        'whatsapp' => '(12) 98877-6655',
        'email' => 'ana.souza@example.com',
        'cidade' => 'Pindamonhangaba',
        'consent_privacidade' => '1',
        'consent_contato' => '1',
    ], $override);
}
