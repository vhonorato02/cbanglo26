<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * Conexão PDO única. Suporta MySQL/MariaDB em produção e
 * SQLite nos testes automatizados.
 * Compatível com PHP 7.1+
 */
final class Database
{
    /** @var PDO|null */
    private static $pdo = null;

    /** @var array|null */
    private static $config = null;

    /** @param array $config */
    public static function configure($config) {
        self::$config = $config;
        self::$pdo = null;
    }

    public static function pdo() {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }
        $cfg = isset(self::$config) ? self::$config : (require BASE_PATH . '/config/app.php')['db'];

        if ((isset($cfg['driver']) ? $cfg['driver'] : 'mysql') === 'sqlite') {
            $sqlitePath = isset($cfg['sqlite_path']) ? $cfg['sqlite_path'] : ':memory:';
            if ($sqlitePath !== ':memory:' && !self::isAbsolutePath($sqlitePath)) {
                $sqlitePath = BASE_PATH . '/' . ltrim(str_replace('\\', '/', $sqlitePath), '/');
            }
            $pdo = new PDO('sqlite:' . $sqlitePath);
            $pdo->exec('PRAGMA foreign_keys = ON');
        } else {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $cfg['host'],
                $cfg['port'],
                $cfg['database']
            );
            $pdo = new PDO($dsn, $cfg['username'], $cfg['password'], [
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            ]);
        }

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        return self::$pdo = $pdo;
    }

    /** Executa um callback dentro de uma transação. */
    public static function transaction($callback) {
        $pdo = self::pdo();
        $pdo->beginTransaction();
        try {
            $result = $callback($pdo);
            $pdo->commit();
            return $result;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public static function isSqlite() {
        return self::pdo()->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite';
    }

    /** Expressão de data/hora atual compatível com MySQL e SQLite. */
    public static function now() {
        return date('Y-m-d H:i:s');
    }

    private static function isAbsolutePath($path) {
        return preg_match('/^[A-Za-z]:[\\\\\/]/', $path) === 1
            || str_starts_with($path, '/')
            || str_starts_with($path, '\\\\');
    }
}
