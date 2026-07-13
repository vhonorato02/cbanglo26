<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

final class Installer
{
    /** @param array<string, string> $db */
    public static function install(array $db)
    {
        if (!extension_loaded('pdo_mysql')) {
            throw new \RuntimeException('A extensão pdo_mysql não está ativa na hospedagem.');
        }

        $envPath = BASE_PATH . '/.env';
        $env = is_file($envPath) ? file_get_contents($envPath) : file_get_contents(BASE_PATH . '/.env.example');
        if ($env === false) {
            throw new \RuntimeException('Não foi possível ler a configuração do site.');
        }
        $env = self::withEnvValues($env, [
            'APP_ENV' => 'production',
            'APP_DEBUG' => 'false',
            'DB_DRIVER' => 'mysql',
            'DB_HOST' => $db['host'],
            'DB_PORT' => $db['port'],
            'DB_DATABASE' => $db['database'],
            'DB_USERNAME' => $db['username'],
            'DB_PASSWORD' => $db['password'],
            'APP_SETUP_TOKEN' => '',
        ]);
        self::checkWritable(BASE_PATH . '/.env.installing', $env);
        self::checkWritable(BASE_PATH . '/storage/installing.lock', date('c') . PHP_EOL);

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $db['host'],
            $db['port'],
            $db['database']
        );
        $pdo = new PDO($dsn, $db['username'], $db['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci',
        ]);

        $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        if ($tables !== []) {
            throw new \RuntimeException('O banco escolhido precisa estar vazio. Crie outro banco no hPanel e tente novamente.');
        }

        $schemaPath = BASE_PATH . '/database/schema.sql';
        $schema = file_get_contents($schemaPath);
        if ($schema === false) {
            throw new \RuntimeException('O arquivo de instalação do banco não foi encontrado.');
        }
        foreach (self::splitSql($schema) as $statement) {
            $pdo->exec($statement);
        }

        if (file_put_contents($envPath, $env, LOCK_EX) === false) {
            throw new \RuntimeException('O servidor não permitiu atualizar o arquivo .env.');
        }

        $lock = BASE_PATH . '/storage/installed.lock';
        if (file_put_contents($lock, date('c') . PHP_EOL, LOCK_EX) === false) {
            throw new \RuntimeException('Não foi possível finalizar o bloqueio do instalador.');
        }
    }

    private static function checkWritable($path, $content)
    {
        if (file_put_contents($path, $content, LOCK_EX) === false) {
            throw new \RuntimeException('A hospedagem não permitiu gravar os arquivos de configuração.');
        }
        @unlink($path);
    }

    /** @return array<int, string> */
    public static function splitSql($sql)
    {
        $statements = [];
        $buffer = '';
        $quote = null;
        $lineComment = false;
        $blockComment = false;
        $length = strlen((string) $sql);

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];
            $next = $i + 1 < $length ? $sql[$i + 1] : '';

            if ($lineComment) {
                if ($char === "\n") {
                    $lineComment = false;
                    $buffer .= $char;
                }
                continue;
            }
            if ($blockComment) {
                if ($char === '*' && $next === '/') {
                    $blockComment = false;
                    $i++;
                }
                continue;
            }
            if ($quote !== null) {
                $buffer .= $char;
                if ($char === '\\' && $next !== '') {
                    $buffer .= $next;
                    $i++;
                    continue;
                }
                if ($char === $quote) {
                    if ($next === $quote) {
                        $buffer .= $next;
                        $i++;
                    } else {
                        $quote = null;
                    }
                }
                continue;
            }
            if ($char === '-' && $next === '-') {
                $lineComment = true;
                $i++;
                continue;
            }
            if ($char === '#') {
                $lineComment = true;
                continue;
            }
            if ($char === '/' && $next === '*') {
                $blockComment = true;
                $i++;
                continue;
            }
            if ($char === "'" || $char === '"' || $char === '`') {
                $quote = $char;
                $buffer .= $char;
                continue;
            }
            if ($char === ';') {
                $statement = trim($buffer);
                if ($statement !== '') {
                    $statements[] = $statement;
                }
                $buffer = '';
                continue;
            }
            $buffer .= $char;
        }

        $statement = trim($buffer);
        if ($statement !== '') {
            $statements[] = $statement;
        }
        return $statements;
    }

    /** @param array<string, string> $values */
    public static function withEnvValues($content, array $values)
    {
        $content = rtrim((string) $content) . PHP_EOL;
        foreach ($values as $key => $value) {
            if (preg_match('/[\r\n]/', $value)) {
                throw new \InvalidArgumentException('Valor inválido para ' . $key . '.');
            }
            $line = $key . '=' . self::encodeEnvValue($value);
            $pattern = '/^' . preg_quote($key, '/') . '=.*$/m';
            if (preg_match($pattern, $content)) {
                $content = (string) preg_replace($pattern, $line, $content, 1);
            } else {
                $content .= $line . PHP_EOL;
            }
        }
        return $content;
    }

    private static function encodeEnvValue($value)
    {
        if ($value === '' || preg_match('/^[A-Za-z0-9_@.:\/+,&%#!$^*()=?~-]+$/', $value)) {
            return $value;
        }
        if (strpos($value, '"') === false) {
            return '"' . $value . '"';
        }
        if (strpos($value, "'") === false) {
            return "'" . $value . "'";
        }
        return $value;
    }
}
