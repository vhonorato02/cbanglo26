<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Ativar o site | Concurso de Bolsas</title>
<link rel="icon" href="<?= e(url('favicon.svg')) ?>" type="image/svg+xml">
<link rel="stylesheet" href="<?= e(asset('assets/css/admin.css')) ?>">
</head>
<body class="admin admin-auth">
<main class="auth-card">
  <p class="admin-brand admin-brand-center"><span class="brand-mark" aria-hidden="true">CB</span> Concurso de Bolsas</p>
  <h1>Ativar o site</h1>
  <p class="auth-note">Cole abaixo os dados do banco criado no hPanel. O site monta todas as tabelas automaticamente.</p>
  <?php if (!empty($erro)): ?>
  <div class="alert alert-erro" role="alert"><?= e($erro) ?></div>
  <?php endif; ?>
  <form method="post" action="<?= e(url('instalar')) ?>">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="token" value="<?= e($token) ?>">
    <div class="field">
      <label for="db_host">Servidor MySQL</label>
      <input type="text" id="db_host" name="db_host" value="localhost" required autocomplete="off">
    </div>
    <div class="field">
      <label for="db_database">Nome do banco</label>
      <input type="text" id="db_database" name="db_database" required autocomplete="off">
    </div>
    <div class="field">
      <label for="db_username">Usuário do banco</label>
      <input type="text" id="db_username" name="db_username" required autocomplete="username">
    </div>
    <div class="field">
      <label for="db_password">Senha do banco</label>
      <input type="password" id="db_password" name="db_password" required autocomplete="current-password">
    </div>
    <input type="hidden" name="db_port" value="3306">
    <button type="submit" class="btn btn-gold btn-lg btn-block">Ativar agora</button>
  </form>
</main>
</body>
</html>
