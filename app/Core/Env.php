<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Carregador simples de variáveis de ambiente a partir de um arquivo .env.
 * Compatível com PHP 7.1+
 */
final class Env
{
    /** @var array */
    private static $vars = [];

    public static function load($path) {
        if (!is_file($path)) {
            return;
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || self::str_starts_with($line, '#')) {
                continue;
            }
            $pos = strpos($line, '=');
            if ($pos === false) {
                continue;
            }
            $name = trim(substr($line, 0, $pos));
            $value = trim(substr($line, $pos + 1));
            // Remove aspas envolventes e comentário no fim da linha sem aspas
            if ($value !== '' && ($value[0] === '"' || $value[0] === "'")) {
                $quote = $value[0];
                $end = strrpos($value, $quote);
                $value = $end !== false && $end > 0 ? substr($value, 1, $end - 1) : substr($value, 1);
            } elseif (($hash = strpos($value, ' #')) !== false) {
                $value = rtrim(substr($value, 0, $hash));
            }
            self::$vars[$name] = $value;
        }
    }

    public static function get($key, $default = '') {
        if (array_key_exists($key, self::$vars)) {
            return self::$vars[$key];
        }
        $fromServer = getenv($key);
        return $fromServer !== false ? $fromServer : $default;
    }

    public static function bool($key, $default = false) {
        $value = strtolower(self::get($key, $default ? 'true' : 'false'));
        return in_array($value, ['1', 'true', 'yes', 'on'], true);
    }

    /** Somente para testes. */
    public static function set($key, $value) {
        self::$vars[$key] = $value;
    }

    private static function str_starts_with($haystack, $needle) {
        return strpos($haystack, $needle) === 0;
    }
}
