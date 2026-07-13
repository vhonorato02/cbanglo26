<?php

declare(strict_types=1);

use App\Core\Csrf;
use App\Core\Installer;
use App\Core\Str;

T::test('protocolo tem formato CB26-XXXX-XXXX e é imprevisível', function () {
    $vistos = [];
    for ($i = 0; $i < 200; $i++) {
        $p = Str::protocolo();
        T::assert((bool) preg_match('/^CB26-[2-9A-HJKMNP-Z]{4}-[2-9A-HJKMNP-Z]{4}$/', $p), "formato inválido: {$p}");
        T::assert(!isset($vistos[$p]), 'protocolo repetido em 200 gerações');
        $vistos[$p] = true;
    }
});

T::test('slug normaliza acentos e caixa (chave de duplicidade)', function () {
    T::assertEquals('maria-clara-de-souza', Str::slug('  Maria  Clara de SOUZA '));
    T::assertEquals(Str::slug('João André'), Str::slug('JOAO ANDRE'));
});

T::test('digits extrai apenas números', function () {
    T::assertEquals('12988776655', Str::digits('(12) 98877-6655'));
});

T::test('escape de saída neutraliza XSS', function () {
    $malicioso = '<script>alert("x")</script>';
    $escapado = e($malicioso);
    T::assert(!str_contains($escapado, '<script>'), 'tag não escapada');
    T::assert(str_contains($escapado, '&lt;script&gt;'));
});

T::test('exportação CSV neutraliza fórmulas do Excel', function () {
    T::assertEquals("'=2+2", csv_cell('=2+2'));
    T::assertEquals("'@comando", csv_cell('@comando'));
    T::assertEquals('Maria Clara', csv_cell('Maria Clara'));
});

T::test('instalador separa SQL sem quebrar textos com ponto e vírgula', function () {
    $sql = "-- comentário; ignorado\nCREATE TABLE teste (id INT);\n"
        . "INSERT INTO teste VALUES ('texto; preservado'); /* bloco; ignorado */\n"
        . 'SELECT 1;';
    $statements = Installer::splitSql($sql);
    T::assertEquals(3, count($statements));
    T::assert(str_contains($statements[1], "'texto; preservado'"));

    $schema = (string) file_get_contents(BASE_PATH . '/database/schema.sql');
    $schemaStatements = Installer::splitSql($schema);
    T::assert(count($schemaStatements) >= 20, 'schema real não foi separado por completo');
    T::assert(str_contains(implode("\n", $schemaStatements), 'CREATE TABLE IF NOT EXISTS inscricoes'));
});

T::test('instalador atualiza env sem duplicar chaves', function () {
    $env = "APP_ENV=development\nDB_PASSWORD=antiga\n";
    $updated = Installer::withEnvValues($env, [
        'APP_ENV' => 'production',
        'DB_PASSWORD' => 'nova#senha',
        'DB_HOST' => 'localhost',
    ]);
    T::assertEquals(1, substr_count($updated, 'APP_ENV='));
    T::assert(str_contains($updated, 'APP_ENV=production'));
    T::assert(str_contains($updated, 'DB_PASSWORD=nova#senha'));
    T::assert(str_contains($updated, 'DB_HOST=localhost'));
});

T::test('CSRF: token válido passa, token errado e vazio falham', function () {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        @session_start();
    }
    $token = Csrf::token();
    T::assert(Csrf::validate($token), 'token legítimo rejeitado');
    T::assert(!Csrf::validate('token-falso'), 'token falso aceito');
    T::assert(!Csrf::validate(''), 'token vazio aceito');
    T::assert(!Csrf::validate(null), 'token nulo aceito');
});

T::test('hash de IP não expõe o IP em claro', function () {
    $_SERVER['REMOTE_ADDR'] = '203.0.113.7';
    $hash = client_ip_hash();
    T::assert(!str_contains($hash, '203.0.113.7'));
    T::assertEquals(64, strlen($hash), 'sha256 hex');
});
