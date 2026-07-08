<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * Conexão PDO única. Suporta MySQL/MariaDB em produção e
 * SQLite nos testes automatizados.
 */
final class Database
{
    private static ?PDO $pdo = null;

    /** @var array<string, mixed>|null */
    private static ?array $config = null;

    /** @param array<string, mixed> $config */
    public static function configure(array $config): void
    {
        self::$config = $config;
        self::$pdo = null;
    }

    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }
        $cfg = self::$config ?? (require BASE_PATH . '/config/app.php')['db'];

        if (($cfg['driver'] ?? 'mysql') === 'sqlite') {
            $pdo = new PDO('sqlite:' . ($cfg['sqlite_path'] ?? ':memory:'));
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
    public static function transaction(callable $callback): mixed
    {
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

    public static function isSqlite(): bool
    {
        return self::pdo()->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite';
    }

    /** Expressão de data/hora atual compatível com MySQL e SQLite. */
    public static function now(): string
    {
        return date('Y-m-d H:i:s');
    }
}
