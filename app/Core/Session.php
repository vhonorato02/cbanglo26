<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Sessão com cookies endurecidos (HttpOnly, SameSite, Secure em HTTPS).
 * Compatível com PHP 7.1+
 */
final class Session
{
    public static function start()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        // Em CLI (testes) os cabeçalhos já foram enviados — pula a configuração
        if (headers_sent()) {
            @session_start();
            return;
        }
        $secure = self::isHttps();
        if (PHP_VERSION_ID >= 70300) {
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => $secure,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        } else {
            session_set_cookie_params(0, '/; samesite=Lax', '', $secure, true);
        }
        session_name('cbsession');
        session_start();
    }

    public static function isHttps() {
        if (!empty($_SERVER['HTTPS']) && isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            return true;
        }
        return (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : '') === 'https';
    }

    public static function regenerate() {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    public static function get($key, $default = null) {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }

    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public static function remove($key) {
        unset($_SESSION[$key]);
    }

    /** Lê e remove (flash message). */
    public static function pull($key, $default = null) {
        $value = isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
        unset($_SESSION[$key]);
        return $value;
    }

    public static function destroy() {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $p = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $p['path'],
                    $p['domain'],
                    $p['secure'],
                    $p['httponly']
                );
            }
            session_destroy();
        }
    }
}
