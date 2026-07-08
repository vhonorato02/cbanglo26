<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Configuração inicial — Concurso de Bolsas</title>
<meta name="robots" content="noindex, nofollow">
<link rel="icon" href="<?= e(url('favicon.svg')) ?>" type="image/svg+xml">
<link rel="stylesheet" href="<?= e(asset('assets/css/admin.css')) ?>">
</head>
<body class="admin admin-auth">
<main class="auth-card">
  <p class="admin-brand admin-brand-center"><span class="brand-mark" aria-hidden="true">CB</span> Concurso de Bolsas</p>
  <h1>Criar o primeiro administrador</h1>
  <?php if (!empty($erro)): ?>
  <div class="alert alert-erro" role="alert"><?= e($erro) ?></div>
  <?php endif; ?>
  <p class="auth-note">Esta página funciona uma única vez, com o token definido no
  arquivo <code>.env</code>. Após criar o administrador, remova o
  <code>APP_SETUP_TOKEN</code> do <code>.env</code>.</p>
  <form method="post" action="<?= e(url('setup')) ?>">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="token" value="<?= e($token) ?>">
    <div class="field">
      <label for="nome">Nome</label>
      <input type="text" id="nome" name="nome" required maxlength="120" autocomplete="name">
    </div>
    <div class="field">
      <label for="email">E-mail</label>
      <input type="email" id="email" name="email" required maxlength="190" autocomplete="username">
    </div>
    <div class="field">
      <label for="senha">Senha (mín. 10 caracteres)</label>
      <input type="password" id="senha" name="senha" required minlength="10" autocomplete="new-password">
    </div>
    <div class="field">
      <label for="senha_confirmacao">Confirmar senha</label>
      <input type="password" id="senha_confirmacao" name="senha_confirmacao" required minlength="10" autocomplete="new-password">
    </div>
    <button type="submit" class="btn btn-gold btn-lg btn-block">Criar administrador</button>
  </form>
</main>
</body>
</html>
