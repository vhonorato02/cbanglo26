<?php
/** Gerenciar séries: $series, $csrf, $flash */
?>
<div class="admin-page-head">
  <h1>Séries disponíveis</h1>
</div>

<?php if (!empty($flash)): ?>
<div class="alert alert-<?= $flash['tipo'] === 'ok' ? 'ok' : 'erro' ?>" role="status"><?= e($flash['msg']) ?></div>
<?php endif; ?>

<div class="panel-grid panel-grid-cards">
  <?php foreach ($series as $serie): ?>
  <section class="panel">
    <h2><?= e($serie['nome']) ?> <?= (int) $serie['ativo'] === 1 ? '' : '<span class="badge badge-erro">inativa</span>' ?></h2>
    <form method="post" action="<?= e(url('admin/series')) ?>">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
      <input type="hidden" name="id" value="<?= (int) $serie['id'] ?>">
      <div class="field"><label for="nome-<?= (int) $serie['id'] ?>">Nome</label>
        <input type="text" id="nome-<?= (int) $serie['id'] ?>" name="nome" value="<?= e($serie['nome']) ?>" required maxlength="120"></div>
      <div class="field"><label for="descricao-<?= (int) $serie['id'] ?>">Descrição</label>
        <input type="text" id="descricao-<?= (int) $serie['id'] ?>" name="descricao" value="<?= e($serie['descricao']) ?>" maxlength="190"></div>
      <div class="field-row">
        <div class="field"><label for="ordem-<?= (int) $serie['id'] ?>">Ordem</label>
          <input type="number" id="ordem-<?= (int) $serie['id'] ?>" name="ordem" value="<?= (int) $serie['ordem'] ?>" min="0" max="99"></div>
        <div class="field"><label for="ativo-<?= (int) $serie['id'] ?>">Situação</label>
          <select id="ativo-<?= (int) $serie['id'] ?>" name="ativo">
            <option value="1" <?= (int) $serie['ativo'] === 1 ? 'selected' : '' ?>>Ativa</option>
            <option value="0" <?= (int) $serie['ativo'] === 0 ? 'selected' : '' ?>>Inativa</option>
          </select></div>
      </div>
      <button type="submit" class="btn btn-gold btn-sm">Salvar</button>
    </form>
  </section>
  <?php endforeach; ?>

  <section class="panel panel-nova">
    <h2>Adicionar série</h2>
    <form method="post" action="<?= e(url('admin/series')) ?>">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
      <input type="hidden" name="id" value="0">
      <div class="field"><label for="nome-nova">Nome</label>
        <input type="text" id="nome-nova" name="nome" required maxlength="120"></div>
      <div class="field"><label for="descricao-nova">Descrição</label>
        <input type="text" id="descricao-nova" name="descricao" maxlength="190"></div>
      <input type="hidden" name="ativo" value="1">
      <input type="hidden" name="ordem" value="9">
      <button type="submit" class="btn btn-gold btn-sm">Adicionar</button>
    </form>
  </section>
</div>
