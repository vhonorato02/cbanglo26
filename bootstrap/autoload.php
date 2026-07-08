<?php

declare(strict_types=1);

/**
 * Autoloader PSR-4 mínimo para o namespace App\.
 * Dispensa o Composer em produção (hospedagem compartilhada sem SSH).
 * Se o vendor/autoload.php do Composer existir, ele também é carregado.
 * Compatível com PHP 7.1+
 */

// Polyfill para str_starts_with (PHP 8.0+)
if (!function_exists('str_starts_with')) {
    function str_starts_with($haystack, $needle) {
        return strpos($haystack, $needle) === 0;
    }
}

spl_autoload_register(static function ($class) {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = dirname(__DIR__) . '/app/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

$composer = dirname(__DIR__) . '/vendor/autoload.php';
if (is_file($composer)) {
    require $composer;
}
