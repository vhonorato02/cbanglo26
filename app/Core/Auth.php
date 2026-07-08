<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\AdminUser;
use App\Models\LoginAttempt;

/**
 * Autenticação administrativa por sessão com limite de tentativas.
 * Compatível com PHP 7.1+
 */
final class Auth
{
    const SESSION_KEY = 'admin_id';
    const MAX_ATTEMPTS = 5;
    const LOCKOUT_MINUTES = 15;

    public static function check()
    {
        Session::start();
        return is_int(Session::get(self::SESSION_KEY));
    }

    /** @return int|null */
    public static function id()
    {
        Session::start();
        $id = Session::get(self::SESSION_KEY);
        return is_int($id) ? $id : null;
    }

    /** @return array|null */
    public static function user()
    {
        $id = self::id();
        return $id !== null ? AdminUser::find($id) : null;
    }

    /**
     * Tenta autenticar. Retorna:
     *  'ok'        — sucesso;
     *  'locked'    — bloqueado por excesso de tentativas;
     *  'invalid'   — credenciais inválidas.
     */
    public static function attempt($email, $password)
    {
        Session::start();
        $email = mb_strtolower(Str::clean($email), 'UTF-8');
        $ipHash = client_ip_hash();

        if (LoginAttempt::isLocked($email, $ipHash, self::MAX_ATTEMPTS, self::LOCKOUT_MINUTES)) {
            return 'locked';
        }

        $user = AdminUser::findByEmail($email);
        $valid = $user !== null
            && (int) $user['ativo'] === 1
            && password_verify($password, $user['senha_hash']);

        LoginAttempt::record($email, $ipHash, $valid);

        if (!$valid) {
            return 'invalid';
        }

        Session::regenerate();
        Session::set(self::SESSION_KEY, (int) $user['id']);
        AdminUser::touchLogin((int) $user['id']);

        if (password_needs_rehash($user['senha_hash'], PASSWORD_DEFAULT)) {
            AdminUser::updatePassword((int) $user['id'], $password);
        }
        return 'ok';
    }

    public static function logout(): void
    {
        Session::start();
        Session::remove(self::SESSION_KEY);
        Session::destroy();
    }
}
