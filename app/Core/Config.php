<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * Configurações da campanha persistidas no banco (tabela configuracoes),
 * com cache em arquivo para evitar consultas repetidas.
 */
final class Config
{
    /** @var array<string, string>|null */
    private static ?array $cache = null;

    private static function cacheFile(): string
    {
        return BASE_PATH . '/storage/cache/config.json';
    }

    /** @return array<string, string> */
    public static function all(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }
        $file = self::cacheFile();
        if (is_file($file)) {
            $data = json_decode((string) file_get_contents($file), true);
            if (is_array($data)) {
                return self::$cache = $data;
            }
        }
        return self::$cache = self::reload();
    }

    /** @return array<string, string> */
    public static function reload(): array
    {
        $rows = Database::pdo()
            ->query('SELECT chave, valor FROM configuracoes')
            ->fetchAll(PDO::FETCH_KEY_PAIR);
        self::$cache = array_map(strval(...), $rows);
        $dir = dirname(self::cacheFile());
        if (is_dir($dir) && is_writable($dir)) {
            @file_put_contents(
                self::cacheFile(),
                json_encode(self::$cache, JSON_UNESCAPED_UNICODE),
                LOCK_EX
            );
        }
        return self::$cache;
    }

    public static function get(string $key, string $default = ''): string
    {
        return self::all()[$key] ?? $default;
    }

    public static function set(string $key, string $value): void
    {
        $pdo = Database::pdo();
        if (Database::isSqlite()) {
            $sql = 'INSERT INTO configuracoes (chave, valor) VALUES (:c, :v)
                    ON CONFLICT(chave) DO UPDATE SET valor = :v2';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':c' => $key, ':v' => $value, ':v2' => $value]);
        } else {
            $sql = 'INSERT INTO configuracoes (chave, valor) VALUES (:c, :v)
                    ON DUPLICATE KEY UPDATE valor = :v2';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':c' => $key, ':v' => $value, ':v2' => $value]);
        }
        self::reload();
    }

    /** Inscrições abertas? Considera flag manual e o período configurado. */
    public static function inscricoesAbertas(): bool
    {
        if (self::get('inscricoes_abertas', '1') !== '1') {
            return false;
        }
        $agora = date('Y-m-d H:i:s');
        $inicio = self::get('inscricoes_inicio');
        $fim = self::get('inscricoes_fim');
        if ($inicio !== '' && $agora < $inicio . ' 00:00:00') {
            return false;
        }
        if ($fim !== '' && $agora > $fim . ' 23:59:59') {
            return false;
        }
        $limite = (int) self::get('inscricoes_limite', '0');
        if ($limite > 0) {
            $total = (int) Database::pdo()
                ->query('SELECT COUNT(*) FROM inscricoes')
                ->fetchColumn();
            if ($total >= $limite) {
                return false;
            }
        }
        return true;
    }

    public static function clearCache(): void
    {
        self::$cache = null;
        @unlink(self::cacheFile());
    }
}
