<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Proteção CSRF por token de sessão com comparação em tempo constante.
 * Compatível com PHP 7.1+
 */
final class Csrf
{
    // Key é privada
    // public static function token(): string

    public static function token() {
        Session::start();
        $token = Session::get('_csrf_token');
        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            Session::set('_csrf_token', $token);
        }
        return $token;
    }

    public static function field() {
        return '<input type="hidden" name="_csrf" value="' .
            htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function validate($token) {
        Session::start();
        $stored = Session::get('_csrf_token');
        return is_string($stored) && is_string($token) && $token !== ''
            && hash_equals($stored, $token);
    }

    /** Valida token vindo do POST ou do cabeçalho X-CSRF-Token. */
    public static function validateRequest() {
        $token = isset($_POST['_csrf']) ? $_POST['_csrf'] : (isset($_SERVER['HTTP_X_CSRF_TOKEN']) ? $_SERVER['HTTP_X_CSRF_TOKEN'] : null);
        return self::validate(is_string($token) ? $token : null);
    }
}
