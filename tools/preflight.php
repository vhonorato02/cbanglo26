<?php

declare(strict_types=1);

use App\Core\Database;
use App\Core\Env;

define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/bootstrap/autoload.php';

Env::load(BASE_PATH . '/.env');
$config = require BASE_PATH . '/config/app.php';
$errors = 0;
$warnings = 0;

function result($ok, $label, $detail = '')
{
    global $errors;
    echo ($ok ? '[OK]   ' : '[ERRO] ') . $label;
    if ($detail !== '') {
        echo ' - ' . $detail;
    }
    echo PHP_EOL;
    if (!$ok) {
        $errors++;
    }
}

function warning_result($ok, $label, $detail = '')
{
    global $warnings;
    if ($ok) {
        echo '[OK]   ' . $label . PHP_EOL;
        return;
    }
    echo '[AVISO] ' . $label . ($detail !== '' ? ' - ' . $detail : '') . PHP_EOL;
    $warnings++;
}

echo "Preflight de produção - Concurso de Bolsas" . PHP_EOL;
echo str_repeat('=', 43) . PHP_EOL;

result(version_compare(PHP_VERSION, '8.2.0', '>='), 'PHP 8.2 ou superior', PHP_VERSION);
foreach (['pdo', 'pdo_mysql', 'mbstring', 'openssl'] as $extension) {
    result(extension_loaded($extension), 'Extensão ' . $extension);
}

result($config['env'] === 'production', 'APP_ENV=production', (string) $config['env']);
result($config['debug'] === false, 'APP_DEBUG=false');
result(
    strpos((string) $config['url'], 'https://') === 0 && strpos((string) $config['url'], 'seu-dominio') === false,
    'APP_URL pública com HTTPS',
    (string) $config['url']
);
result(strlen((string) $config['key']) >= 32, 'APP_KEY com pelo menos 32 caracteres');

$db = $config['db'];
result($db['driver'] === 'mysql', 'DB_DRIVER=mysql', (string) $db['driver']);
foreach (['host', 'database', 'username', 'password'] as $field) {
    result((string) $db[$field] !== '', 'Banco: ' . $field . ' preenchido');
}

foreach (['storage/cache', 'storage/logs'] as $directory) {
    $path = BASE_PATH . '/' . $directory;
    result(is_dir($path) && is_writable($path), $directory . ' gravável');
}

$mail = $config['mail'];
result((string) $mail['smtp_host'] !== '', 'SMTP configurado');
result((int) $mail['smtp_port'] === 465, 'SMTP na porta 465');
result((string) $mail['encryption'] === 'ssl', 'SMTP com SSL');
result((string) $mail['smtp_user'] !== '' && (string) $mail['smtp_pass'] !== '', 'Credenciais SMTP preenchidas');

warning_result(Env::get('APP_SETUP_TOKEN', '') === '', 'APP_SETUP_TOKEN desativado', 'deixe vazio após a instalação');

if ($errors === 0) {
    try {
        Database::configure($db);
        $pdo = Database::pdo();
        foreach (['escolas', 'series', 'inscricao_status', 'inscricoes', 'admin_usuarios'] as $table) {
            $pdo->query('SELECT 1 FROM ' . $table . ' LIMIT 1');
        }
        $admins = (int) $pdo->query('SELECT COUNT(*) FROM admin_usuarios WHERE ativo = 1')->fetchColumn();
        result($admins > 0, 'Banco importado e administrador ativo');
    } catch (Throwable $e) {
        result(false, 'Conexão e estrutura do banco', $e->getMessage());
    }
}

echo str_repeat('-', 43) . PHP_EOL;
if ($errors > 0) {
    echo $errors . ' erro(s) impedem a publicação.' . PHP_EOL;
    exit(1);
}
echo 'Ambiente pronto' . ($warnings > 0 ? ' com ' . $warnings . ' aviso(s).' : '.') . PHP_EOL;
