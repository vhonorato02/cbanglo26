<?php

declare(strict_types=1);

use App\Core\Router;

$config = require dirname(__DIR__) . '/bootstrap/app.php';

require BASE_PATH . '/app/Core/Helpers.php';

// Cabeçalhos de segurança
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
header(
    "Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; " .
    "script-src 'self'; font-src 'self'; connect-src 'self'; frame-ancestors 'self'; " .
    "base-uri 'self'; form-action 'self'"
);
if (\App\Core\Session::isHttps()) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

$router = new Router();
require BASE_PATH . '/routes/web.php';

// Remove o prefixo do subdiretório quando o app roda fora da raiz do domínio.
// Só se aplica quando o SCRIPT_NAME termina em index.php (Apache); o servidor
// embutido do PHP usa o próprio caminho da rota como SCRIPT_NAME.
$uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
$scriptName = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '/';
$scriptName = str_replace('\\', '/', $scriptName);
if (str_ends_with($scriptName, '/index.php')) {
    $scriptDir = rtrim(dirname($scriptName), '/');
    if ($scriptDir !== '' && str_starts_with($uri, $scriptDir . '/')) {
        $uri = substr($uri, strlen($scriptDir)) ?: '/';
    }
}

$router->dispatch(isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET', $uri);
