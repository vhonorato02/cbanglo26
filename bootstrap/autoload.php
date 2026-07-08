<?php

declare(strict_types=1);

/**
 * Autoloader PSR-4 mínimo para o namespace App\.
 * Dispensa o Composer em produção (hospedagem compartilhada sem SSH).
 * Se o vendor/autoload.php do Composer existir, ele também é carregado.
 */
spl_autoload_register(static function (string $class): void {
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
