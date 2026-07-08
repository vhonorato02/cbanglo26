<?php

declare(strict_types=1);

/**
 * Router para o servidor embutido do PHP (php -S) nos testes locais.
 * Serve arquivos estáticos de public/ e delega o resto ao front controller.
 */
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$file = __DIR__ . '/../public' . $path;
if ($path !== '/' && is_file($file)) {
    return false; // servidor embutido entrega o arquivo estático
}
require __DIR__ . '/../public/index.php';
