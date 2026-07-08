<?php
/**
 * Layout público.
 * Variáveis esperadas: $content, $pageTitle?, $pageDescription?, $canonical?, $bodyClass?
 */
$config = $config ?? [];
$titulo = $pageTitle ?? (($config['campanha_nome'] ?? 'Concurso de Bolsas') . ' — Anglo Pinda, Colégio Fênix, Colégio Drummond e Anglo Cruzeiro');
$descricao = $pageDescription ?? ($config['campanha_descricao'] ?? 'Inscreva-se no Concurso de Bolsas.');
$canonicalUrl = $canonical ?? url($_SERVER['REQUEST_URI'] ? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/' : '/');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($titulo) ?></title>
<meta name="description" content="<?= e($descricao) ?>">
<link rel="canonical" href="<?= e($canonicalUrl) ?>">
<meta property="og:type" content="website">
<meta property="og:title" content="<?= e($titulo) ?>">
<meta property="og:description" content="<?= e($descricao) ?>">
<meta property="og:url" content="<?= e($canonicalUrl) ?>">
<meta property="og:image" content="<?= e(url('assets/img/og-image.jpg')) ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:locale" content="pt_BR">
<meta name="twitter:card" content="summary_large_image">
<link rel="icon" href="<?= e(url('favicon.svg')) ?>" type="image/svg+xml">
<link rel="preload" href="<?= e(url('assets/fonts/baloo2-800-latin.woff2')) ?>" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="<?= e(url('assets/fonts/nunitosans-400-latin.woff2')) ?>" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="<?= e(asset('assets/css/main.css')) ?>">
<?php if (!empty($structuredData)): ?>
<script type="application/ld+json"><?= $structuredData ?></script>
<?php endif; ?>
</head>
<body class="<?= e($bodyClass ?? '') ?>">
<a class="skip-link" href="#conteudo">Ir para o conteúdo</a>
<?= $content ?>
<script src="<?= e(asset('assets/js/main.js')) ?>" defer></script>
</body>
</html>
