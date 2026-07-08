<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Entrar — Painel do Concurso de Bolsas</title>
<meta name="robots" content="noindex, nofollow">
<link rel="icon" href="<?= e(url('favicon.svg')) ?>" type="image/svg+xml">
<link rel="stylesheet" href="<?= e(asset('assets/css/admin.css')) ?>">
</head>
<body class="admin admin-auth">
<main class="auth-card">
  <p class="admin-brand admin-brand-center"><span class="brand-mark" aria-hidden="true">CB</span> Concurso de Bolsas</p>
  <h1>Painel administrativo</h1>
  <?php if (!empty($erro)): ?>
  <div class="alert alert-erro" role="alert"><?= e($erro) ?></div>
  <?php endif; ?>
  <?php if (!empty($ok)): ?>
  <div class="alert alert-ok" role="status"><?= e($ok) ?></div>
  <?php endif; ?>
  <form method="post" action="<?= e(url('admin/login')) ?>">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <div class="field">
      <label for="email">E-mail</label>
      <input type="email" id="email" name="email" required autocomplete="username" maxlength="190">
    </div>
    <div class="field">
      <label for="senha">Senha</label>
      <input type="password" id="senha" name="senha" required autocomplete="current-password">
    </div>
    <button type="submit" class="btn btn-gold btn-lg btn-block">Entrar</button>
  </form>
  <p class="auth-note">Esqueceu a senha? Peça a outro administrador para redefini-la
  em “Usuários” ou siga o procedimento de recuperação descrito no manual de implantação.</p>
</main>
</body>
</html>
