<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Session;
use App\Core\Str;
use App\Core\View;
use App\Models\AdminUser;
use App\Models\AuditLog;

final class UsuariosController
{
    public function index(): void
    {
        View::show('admin/usuarios', [
            'user' => Auth::user(),
            'usuarios' => AdminUser::todos(),
            'csrf' => Csrf::token(),
            'flash' => Session::pull('admin_flash'),
        ], 'admin');
    }

    public function salvar(): void
    {
        if (!Csrf::validateRequest()) {
            Session::set('admin_flash', ['tipo' => 'erro', 'msg' => 'Sessão expirada. Tente novamente.']);
            redirect('admin/usuarios');
        }
        $id = (int) ($_POST['id'] ?? 0);
        $nome = Str::clean((string) ($_POST['nome'] ?? ''));
        $email = mb_strtolower(Str::clean((string) ($_POST['email'] ?? '')), 'UTF-8');
        $senha = (string) ($_POST['senha'] ?? '');
        $ativo = ($_POST['ativo'] ?? '') === '1' ? 1 : 0;

        if (mb_strlen($nome) < 3 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            Session::set('admin_flash', ['tipo' => 'erro', 'msg' => 'Informe nome e e-mail válidos.']);
            redirect('admin/usuarios');
        }

        $existente = AdminUser::findByEmail($email);
        if ($existente !== null && (int) $existente['id'] !== $id) {
            Session::set('admin_flash', ['tipo' => 'erro', 'msg' => 'Já existe um usuário com este e-mail.']);
            redirect('admin/usuarios');
        }

        if ($id > 0) {
            $usuario = AdminUser::find($id);
            if ($usuario === null) {
                Session::set('admin_flash', ['tipo' => 'erro', 'msg' => 'Usuário não encontrado.']);
                redirect('admin/usuarios');
            }
            // Impede o usuário de desativar a própria conta
            if ($id === Auth::id() && $ativo === 0) {
                Session::set('admin_flash', ['tipo' => 'erro', 'msg' => 'Você não pode desativar a própria conta.']);
                redirect('admin/usuarios');
            }
            AdminUser::updateDados($id, $nome, $email, $ativo);
            if ($senha !== '') {
                if (mb_strlen($senha) < 10) {
                    Session::set('admin_flash', ['tipo' => 'erro', 'msg' => 'A nova senha deve ter no mínimo 10 caracteres.']);
                    redirect('admin/usuarios');
                }
                AdminUser::updatePassword($id, $senha);
            }
            AuditLog::registrar(Auth::id(), 'usuario.editar', "Usuário #{$id} ({$email})");
        } else {
            if (mb_strlen($senha) < 10) {
                Session::set('admin_flash', ['tipo' => 'erro', 'msg' => 'A senha deve ter no mínimo 10 caracteres.']);
                redirect('admin/usuarios');
            }
            $id = AdminUser::create($nome, $email, $senha, $ativo);
            AuditLog::registrar(Auth::id(), 'usuario.criar', "Usuário #{$id} ({$email})");
        }
        Session::set('admin_flash', ['tipo' => 'ok', 'msg' => 'Usuário salvo.']);
        redirect('admin/usuarios');
    }
}
