<?php

declare(strict_types=1);

use App\Controllers\Admin\AuthController;
use App\Controllers\Admin\CatalogoController;
use App\Controllers\Admin\ConfigController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\InscricoesController;
use App\Controllers\Admin\LogsController;
use App\Controllers\Admin\UsuariosController;
use App\Controllers\ComprovanteController;
use App\Controllers\HomeController;
use App\Controllers\InstallController;
use App\Controllers\InscricaoController;
use App\Controllers\SetupController;
use App\Core\Auth;
use App\Core\Router;
use App\Core\Session;
use App\Core\View;

/** @var Router $router */

$exigirAdmin = static function (): bool {
    Session::start();
    if (!Auth::check()) {
        redirect('admin/login');
    }
    return true;
};

// ---------- Público ----------
$router->get('/', [HomeController::class, 'index']);
$router->post('/inscricao', [InscricaoController::class, 'store']);
$router->get('/comprovante/{protocolo}', [ComprovanteController::class, 'show']);
$router->get('/consulta', [ComprovanteController::class, 'consulta']);
$router->post('/consulta', [ComprovanteController::class, 'consultar']);

// ---------- Instalação pelo navegador (desativada após o primeiro uso) ----------
$router->get('/instalar', [InstallController::class, 'form']);
$router->post('/instalar', [InstallController::class, 'store']);

// ---------- Setup (primeiro administrador) ----------
$router->get('/setup', [SetupController::class, 'form']);
$router->post('/setup', [SetupController::class, 'store']);

// ---------- Autenticação administrativa ----------
$router->get('/admin/login', [AuthController::class, 'loginForm']);
$router->post('/admin/login', [AuthController::class, 'login']);
$router->post('/admin/logout', [AuthController::class, 'logout']);

// ---------- Painel (protegido) ----------
$router->get('/admin', [DashboardController::class, 'index'], [$exigirAdmin]);
$router->get('/admin/inscricoes', [InscricoesController::class, 'index'], [$exigirAdmin]);
$router->get('/admin/inscricoes/exportar', [InscricoesController::class, 'exportar'], [$exigirAdmin]);
$router->get('/admin/inscricoes/{id}', [InscricoesController::class, 'show'], [$exigirAdmin]);
$router->post('/admin/inscricoes/{id}', [InscricoesController::class, 'update'], [$exigirAdmin]);
$router->post('/admin/inscricoes/{id}/observar', [InscricoesController::class, 'observar'], [$exigirAdmin]);
$router->post('/admin/inscricoes/{id}/excluir', [InscricoesController::class, 'excluir'], [$exigirAdmin]);
$router->get('/admin/configuracoes', [ConfigController::class, 'index'], [$exigirAdmin]);
$router->post('/admin/configuracoes', [ConfigController::class, 'salvar'], [$exigirAdmin]);
$router->get('/admin/escolas', [CatalogoController::class, 'escolas'], [$exigirAdmin]);
$router->post('/admin/escolas', [CatalogoController::class, 'salvarEscola'], [$exigirAdmin]);
$router->get('/admin/series', [CatalogoController::class, 'series'], [$exigirAdmin]);
$router->post('/admin/series', [CatalogoController::class, 'salvarSerie'], [$exigirAdmin]);
$router->get('/admin/faqs', [CatalogoController::class, 'faqs'], [$exigirAdmin]);
$router->post('/admin/faqs', [CatalogoController::class, 'salvarFaq'], [$exigirAdmin]);
$router->post('/admin/faqs/excluir', [CatalogoController::class, 'excluirFaq'], [$exigirAdmin]);
$router->get('/admin/usuarios', [UsuariosController::class, 'index'], [$exigirAdmin]);
$router->post('/admin/usuarios', [UsuariosController::class, 'salvar'], [$exigirAdmin]);
$router->get('/admin/logs', [LogsController::class, 'index'], [$exigirAdmin]);

$router->setNotFound(static function (): void {
    View::show('errors/404');
});
