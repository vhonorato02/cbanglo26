<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Proteção CSRF por token de sessão com comparação em tempo constante.
 */
final class Csrf
{
    private const KEY = '_csrf_token';

    public static function token(): string
    {
        Session::start();
        $token = Session::get(self::KEY);
        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            Session::set(self::KEY, $token);
        }
        return $token;
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_csrf" value="' .
            htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function validate(?string $token): bool
    {
        Session::start();
        $stored = Session::get(self::KEY);
        return is_string($stored) && is_string($token) && $token !== ''
            && hash_equals($stored, $token);
    }

    /** Valida token vindo do POST ou do cabeçalho X-CSRF-Token. */
    public static function validateRequest(): bool
    {
        $token = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
        return self::validate(is_string($token) ? $token : null);
    }
}
