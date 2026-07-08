<?php

declare(strict_types=1);

use App\Core\Env;
use App\Core\Logger;

require __DIR__ . '/autoload.php';

define('BASE_PATH', dirname(__DIR__));

Env::load(BASE_PATH . '/.env');

$config = require BASE_PATH . '/config/app.php';

date_default_timezone_set($config['timezone']);
mb_internal_encoding('UTF-8');

error_reporting(E_ALL);
ini_set('display_errors', $config['debug'] ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', BASE_PATH . '/storage/logs/php-error.log');

set_exception_handler(static function (Throwable $e) use ($config): void {
    Logger::error('Exceção não tratada: ' . $e->getMessage(), [
        'file' => $e->getFile() . ':' . $e->getLine(),
    ]);
    http_response_code(500);
    if ($config['debug']) {
        echo '<pre>' . htmlspecialchars((string) $e, ENT_QUOTES, 'UTF-8') . '</pre>';
        return;
    }
    $errorPage = BASE_PATH . '/app/Views/errors/500.php';
    if (is_file($errorPage)) {
        require $errorPage;
    } else {
        echo 'Ocorreu um erro inesperado. Tente novamente em instantes.';
    }
});

return $config;
