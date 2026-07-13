<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Session;
use App\Core\View;
use App\Models\AuditLog;

final class AuthController
{
    public function loginForm(): void
    {
        Session::start();
        if (Auth::check()) {
            redirect('admin');
        }
        View::show('admin/login', [
            'csrf' => Csrf::token(),
            'erro' => Session::pull('login_erro'),
            'ok' => Session::pull('login_ok'),
        ]);
    }

    public function login(): void
    {
        Session::start();
        if (!Csrf::validateRequest()) {
            Session::set('login_erro', 'Sessão expirada. Tente novamente.');
            redirect('admin/login');
        }
        $email = (string) ($_POST['email'] ?? '');
        $senha = (string) ($_POST['senha'] ?? '');

        $resultado = Auth::attempt($email, $senha);
        if ($resultado === 'locked') {
            Session::set('login_erro', 'Acesso temporariamente bloqueado por excesso de tentativas. Aguarde ' . Auth::LOCKOUT_MINUTES . ' minutos.');
            redirect('admin/login');
        }
        if ($resultado === 'invalid') {
            Session::set('login_erro', 'Usuário ou senha incorretos.');
            redirect('admin/login');
        }
        AuditLog::registrar(Auth::id(), 'auth.login', 'Login realizado');
        redirect('admin');
    }

    public function logout(): void
    {
        Session::start();
        if (Auth::check() && Csrf::validateRequest()) {
            AuditLog::registrar(Auth::id(), 'auth.logout', 'Logout realizado');
            Auth::logout();
        }
        redirect('admin/login');
    }
}
