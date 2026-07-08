<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Log em arquivo fora da pasta pública (storage/logs).
 * Não registra dados pessoais — apenas mensagens técnicas.
 * Compatível com PHP 7.1+
 */
final class Logger
{
    public static function info($message, $context = [])
    {
        self::write('INFO', $message, $context);
    }

    public static function warning($message, $context = [])
    {
        self::write('WARN', $message, $context);
    }

    public static function error($message, $context = [])
    {
        self::write('ERROR', $message, $context);
    }

    private static function write($level, $message, $context)
    {
        $dir = (defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 2)) . '/storage/logs';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $line = sprintf(
            "[%s] %s: %s%s\n",
            date('Y-m-d H:i:s'),
            $level,
            $message,
            $context !== [] ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : ''
        );
        @file_put_contents($dir . '/app-' . date('Y-m-d') . '.log', $line, FILE_APPEND | LOCK_EX);
    }
}
