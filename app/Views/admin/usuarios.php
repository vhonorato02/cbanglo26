<?php
/** Usuários administrativos: $usuarios, $csrf, $flash */
?>
<div class="admin-page-head">
  <div>
    <h1>Usuários administrativos</h1>
    <p class="page-meta">Gerencie quem acessa o painel. O usuário pode ser um login curto, como <code>admin</code>, ou um e-mail.</p>
  </div>
</div>

<?php if (!empty($flash)): ?>
<div class="alert alert-<?= $flash['tipo'] === 'ok' ? 'ok' : 'erro' ?>" role="status"><?= e($flash['msg']) ?></div>
<?php endif; ?>

<div class="panel-grid panel-grid-cards">
  <?php foreach ($usuarios as $usuario): ?>
  <section class="panel">
    <h2><?= e($usuario['nome']) ?> <?= (int) $usuario['ativo'] === 1 ? '' : '<span class="badge badge-erro">inativo</span>' ?></h2>
    <p class="page-meta">Último acesso: <?= e(data_br($usuario['ultimo_login'] ?? null, true)) ?></p>
    <form method="post" action="<?= e(url('admin/usuarios')) ?>">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
      <input type="hidden" name="id" value="<?= (int) $usuario['id'] ?>">
      <div class="field"><label for="nome-<?= (int) $usuario['id'] ?>">Nome</label>
        <input type="text" id="nome-<?= (int) $usuario['id'] ?>" name="nome" value="<?= e($usuario['nome']) ?>" required maxlength="120"></div>
      <div class="field"><label for="email-<?= (int) $usuario['id'] ?>">Usuário de acesso</label>
        <input type="text" id="email-<?= (int) $usuario['id'] ?>" name="email" value="<?= e($usuario['email']) ?>" required maxlength="190" autocomplete="username"></div>
      <div class="field-row">
        <div class="field"><label for="senha-<?= (int) $usuario['id'] ?>">Nova senha (deixe em branco para manter)</label>
          <input type="password" id="senha-<?= (int) $usuario['id'] ?>" name="senha" minlength="10" autocomplete="new-password"></div>
        <div class="field"><label for="senha-confirmacao-<?= (int) $usuario['id'] ?>">Confirmar nova senha</label>
          <input type="password" id="senha-confirmacao-<?= (int) $usuario['id'] ?>" name="senha_confirmacao" minlength="10" autocomplete="new-password"></div>
      </div>
      <div class="field-row">
        <div class="field"><label for="ativo-<?= (int) $usuario['id'] ?>">Situação</label>
          <select id="ativo-<?= (int) $usuario['id'] ?>" name="ativo">
            <option value="1" <?= (int) $usuario['ativo'] === 1 ? 'selected' : '' ?>>Ativo</option>
            <option value="0" <?= (int) $usuario['ativo'] === 0 ? 'selected' : '' ?>>Inativo</option>
          </select></div>
      </div>
      <button type="submit" class="btn btn-gold btn-sm">Salvar</button>
    </form>
  </section>
  <?php endforeach; ?>

  <section class="panel panel-nova">
    <h2>Adicionar usuário</h2>
    <form method="post" action="<?= e(url('admin/usuarios')) ?>">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
      <input type="hidden" name="id" value="0">
      <div class="field"><label for="nome-novo">Nome</label>
        <input type="text" id="nome-novo" name="nome" required maxlength="120"></div>
      <div class="field"><label for="email-novo">Usuário de acesso</label>
        <input type="text" id="email-novo" name="email" required maxlength="190" autocomplete="username"></div>
      <div class="field"><label for="senha-novo">Senha (mín. 10 caracteres)</label>
        <input type="password" id="senha-novo" name="senha" required minlength="10" autocomplete="new-password"></div>
      <div class="field"><label for="senha-confirmacao-novo">Confirmar senha</label>
        <input type="password" id="senha-confirmacao-novo" name="senha_confirmacao" required minlength="10" autocomplete="new-password"></div>
      <input type="hidden" name="ativo" value="1">
      <button type="submit" class="btn btn-gold btn-sm">Adicionar</button>
    </form>
  </section>
</div>
