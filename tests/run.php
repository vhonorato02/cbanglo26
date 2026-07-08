<?php

declare(strict_types=1);

/**
 * Runner de testes minimalista (sem dependências externas).
 * Uso: php tests/run.php [filtro]
 */

require __DIR__ . '/bootstrap.php';

final class T
{
    public static int $passed = 0;
    public static int $failed = 0;
    /** @var array<int, string> */
    public static array $failures = [];
    public static string $current = '';

    public static function test(string $nome, callable $fn): void
    {
        $filtro = $GLOBALS['argv'][1] ?? '';
        if ($filtro !== '' && stripos($nome, $filtro) === false) {
            return;
        }
        self::$current = $nome;
        try {
            test_reset_db();
            $fn();
            self::$passed++;
            echo "  \033[32mPASS\033[0m {$nome}\n";
        } catch (\Throwable $e) {
            self::$failed++;
            $msg = $e->getMessage() . ' (' . basename($e->getFile()) . ':' . $e->getLine() . ')';
            self::$failures[] = "{$nome}: {$msg}";
            echo "  \033[31mFAIL\033[0m {$nome}\n       {$msg}\n";
        }
    }

    public static function assert(bool $cond, string $msg = 'asserção falhou'): void
    {
        if (!$cond) {
            throw new \RuntimeException($msg);
        }
    }

    public static function assertEquals(mixed $expected, mixed $actual, string $msg = ''): void
    {
        if ($expected != $actual) {
            $e = var_export($expected, true);
            $a = var_export($actual, true);
            throw new \RuntimeException(($msg !== '' ? $msg . ' — ' : '') . "esperado {$e}, obtido {$a}");
        }
    }

    public static function assertThrows(string $classe, callable $fn, string $msg = ''): void
    {
        try {
            $fn();
        } catch (\Throwable $e) {
            if ($e instanceof $classe) {
                return;
            }
            throw new \RuntimeException("esperava {$classe}, veio " . get_class($e) . ': ' . $e->getMessage());
        }
        throw new \RuntimeException(($msg !== '' ? $msg . ' — ' : '') . "esperava exceção {$classe}, nenhuma lançada");
    }
}

echo "\nConcurso de Bolsas — suíte de testes\n====================================\n";

foreach (glob(__DIR__ . '/cases/*.php') as $arquivo) {
    echo "\n" . basename($arquivo, '.php') . ":\n";
    require $arquivo;
}

echo "\n====================================\n";
echo 'Total: ' . (T::$passed + T::$failed) . ' | Passaram: ' . T::$passed . ' | Falharam: ' . T::$failed . "\n\n";

exit(T::$failed > 0 ? 1 : 0);
