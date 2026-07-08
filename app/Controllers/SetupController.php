<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Env;
use App\Core\Session;
use App\Core\Str;
use App\Core\View;
use App\Models\AdminUser;
use App\Models\AuditLog;

/**
 * Criação do primeiro administrador, sem SSH:
 * acesse /setup?token=SEU_APP_SETUP_TOKEN (definido no .env).
 * A rota é desativada quando já existe um admin ou o token está vazio.
 */
final class SetupController
{
    public function form(): void
    {
        Session::start();
        if (!$this->autorizado()) {
            http_response_code(404);
            View::show('errors/404');
            return;
        }
        View::show('admin/setup', [
            'csrf' => Csrf::token(),
            'token' => (string) ($_GET['token'] ?? ''),
            'erro' => Session::pull('setup_erro'),
        ]);
    }

    public function store(): void
    {
        Session::start();
        if (!$this->autorizado()) {
            http_response_code(404);
            View::show('errors/404');
            return;
        }
        if (!Csrf::validateRequest()) {
            Session::set('setup_erro', 'Sessão expirada. Tente novamente.');
            redirect('setup?token=' . urlencode((string) ($_POST['token'] ?? '')));
        }

        $token = (string) ($_POST['token'] ?? '');
        $nome = Str::clean((string) ($_POST['nome'] ?? ''));
        $email = mb_strtolower(Str::clean((string) ($_POST['email'] ?? '')), 'UTF-8');
        $senha = (string) ($_POST['senha'] ?? '');
        $confirmacao = (string) ($_POST['senha_confirmacao'] ?? '');

        $erro = null;
        if (mb_strlen($nome) < 3) {
            $erro = 'Informe o nome do administrador.';
        } elseif (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $erro = 'E-mail inválido.';
        } elseif (mb_strlen($senha) < 10) {
            $erro = 'A senha deve ter no mínimo 10 caracteres.';
        } elseif ($senha !== $confirmacao) {
            $erro = 'A confirmação de senha não confere.';
        }
        if ($erro !== null) {
            Session::set('setup_erro', $erro);
            redirect('setup?token=' . urlencode($token));
        }

        $id = AdminUser::create($nome, $email, $senha);
        AuditLog::registrar($id, 'setup.primeiro_admin', "Primeiro administrador criado: {$email}");
        Session::set('login_ok', 'Administrador criado com sucesso. Faça login. Importante: remova o APP_SETUP_TOKEN do arquivo .env.');
        redirect('admin/login');
    }

    private function autorizado(): bool
    {
        $tokenConfigurado = Env::get('APP_SETUP_TOKEN', '');
        if ($tokenConfigurado === '' || AdminUser::count() > 0) {
            return false;
        }
        $tokenRecebido = (string) ($_GET['token'] ?? ($_POST['token'] ?? ''));
        return $tokenRecebido !== '' && hash_equals($tokenConfigurado, $tokenRecebido);
    }
}
