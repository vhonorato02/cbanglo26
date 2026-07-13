<?php
/**
 * Layout do painel administrativo.
 * $content, $user, $pageTitle?
 * Compatível com PHP 7.1+
 */
use App\Core\Csrf;

// Polyfills para string functions (PHP 8.0+)
if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle) {
        return strpos($haystack, $needle) !== false;
    }
}
if (!function_exists('str_ends_with')) {
    function str_ends_with($haystack, $needle) {
        return $needle === '' || substr_compare($haystack, $needle, -strlen($needle)) === 0;
    }
}

$uri = parse_url(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/', PHP_URL_PATH) ?? '/';
$ativo = function ($prefix) use ($uri) {
    return (str_contains($uri, $prefix) ? 'is-active' : '');
};
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle ?? 'Painel — Concurso de Bolsas') ?></title>
<meta name="robots" content="noindex, nofollow">
<link rel="icon" href="<?= e(url('favicon.svg')) ?>" type="image/svg+xml">
<link rel="stylesheet" href="<?= e(asset('assets/css/admin.css')) ?>">
</head>
<body class="admin">
<a class="skip-link" href="#conteudo">Ir para o conteúdo</a>
<div class="admin-shell">
  <aside class="admin-sidebar" id="admin-sidebar">
    <div class="admin-brand">
      <span class="brand-mark" aria-hidden="true">26</span>
      <span>Concurso<br>de Bolsas</span>
    </div>
    <nav aria-label="Menu do painel">
      <ul class="admin-nav">
        <li><a class="<?= $uri === '/admin' || str_ends_with($uri, '/admin') ? 'is-active' : '' ?>" href="<?= e(url('admin')) ?>">Visão geral</a></li>
        <li><a class="<?= $ativo('/admin/inscricoes') ?>" href="<?= e(url('admin/inscricoes')) ?>">Inscrições</a></li>
        <li><a class="<?= $ativo('/admin/configuracoes') ?>" href="<?= e(url('admin/configuracoes')) ?>">Configurações</a></li>
        <li><a class="<?= $ativo('/admin/escolas') ?>" href="<?= e(url('admin/escolas')) ?>">Escolas</a></li>
        <li><a class="<?= $ativo('/admin/series') ?>" href="<?= e(url('admin/series')) ?>">Séries</a></li>
        <li><a class="<?= $ativo('/admin/faqs') ?>" href="<?= e(url('admin/faqs')) ?>">Perguntas frequentes</a></li>
        <li><a class="<?= $ativo('/admin/usuarios') ?>" href="<?= e(url('admin/usuarios')) ?>">Usuários</a></li>
        <li><a class="<?= $ativo('/admin/logs') ?>" href="<?= e(url('admin/logs')) ?>">Auditoria</a></li>
      </ul>
    </nav>
    <div class="admin-user">
      <p><?= e($user['nome'] ?? '') ?></p>
      <form method="post" action="<?= e(url('admin/logout')) ?>">
        <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
        <button type="submit" class="btn-link">Sair</button>
      </form>
    </div>
  </aside>
  <div class="admin-main">
    <header class="admin-topbar">
      <button class="admin-menu-toggle" type="button" aria-label="Abrir menu" aria-expanded="false" aria-controls="admin-sidebar">
        <span aria-hidden="true">Menu</span>
      </button>
      <a class="btn-link" href="<?= e(url('/')) ?>" target="_blank" rel="noopener">Ver site</a>
    </header>
    <main id="conteudo" class="admin-content">
      <?= $content ?>
    </main>
  </div>
</div>
<script src="<?= e(asset('assets/js/admin.js')) ?>" defer></script>
</body>
</html>
