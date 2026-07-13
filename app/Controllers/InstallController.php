<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Env;
use App\Core\Installer;
use App\Core\Session;
use App\Core\Str;
use App\Core\View;

final class InstallController
{
    public function form(): void
    {
        Session::start();
        if (!$this->autorizado()) {
            $this->notFound();
            return;
        }
        View::show('install/index', [
            'csrf' => Csrf::token(),
            'token' => (string) ($_GET['token'] ?? ''),
            'erro' => Session::pull('install_erro'),
        ]);
    }

    public function store(): void
    {
        Session::start();
        if (!$this->autorizado()) {
            $this->notFound();
            return;
        }
        $token = (string) ($_POST['token'] ?? '');
        if (!Csrf::validateRequest()) {
            Session::set('install_erro', 'A página expirou. Tente novamente.');
            redirect('instalar?token=' . urlencode($token));
        }

        $db = [
            'host' => Str::clean((string) ($_POST['db_host'] ?? 'localhost')),
            'port' => Str::digits((string) ($_POST['db_port'] ?? '3306')),
            'database' => Str::clean((string) ($_POST['db_database'] ?? '')),
            'username' => Str::clean((string) ($_POST['db_username'] ?? '')),
            'password' => (string) ($_POST['db_password'] ?? ''),
        ];
        if ($db['host'] === '' || $db['database'] === '' || $db['username'] === '' || $db['password'] === '') {
            Session::set('install_erro', 'Preencha os quatro dados exibidos no hPanel.');
            redirect('instalar?token=' . urlencode($token));
        }
        if ($db['port'] === '') {
            $db['port'] = '3306';
        }

        try {
            Installer::install($db);
        } catch (\Throwable $e) {
            Session::set('install_erro', $this->mensagemSegura($e));
            redirect('instalar?token=' . urlencode($token));
        }

        Session::set('login_ok', 'Instalação concluída. Entre com o usuário admin e troque a senha inicial em Usuários.');
        redirect('admin/login');
    }

    private function autorizado(): bool
    {
        if (is_file(BASE_PATH . '/storage/installed.lock')) {
            return false;
        }
        $esperado = Env::get('APP_SETUP_TOKEN', '');
        $recebido = (string) ($_GET['token'] ?? ($_POST['token'] ?? ''));
        return $esperado !== '' && $recebido !== '' && hash_equals($esperado, $recebido);
    }

    private function mensagemSegura(\Throwable $e): string
    {
        if ($e instanceof \PDOException) {
            return 'Não foi possível entrar no banco. Confira nome, usuário e senha exatamente como aparecem no hPanel.';
        }
        return $e->getMessage();
    }

    private function notFound(): void
    {
        http_response_code(404);
        View::show('errors/404');
    }
}
