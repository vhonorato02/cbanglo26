<?php

declare(strict_types=1);

use App\Core\Csrf;
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
