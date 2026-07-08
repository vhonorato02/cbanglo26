<?php
/** Gerenciar escolas: $escolas, $csrf, $flash */
?>
<div class="admin-page-head">
  <h1>Escolas participantes</h1>
</div>

<?php if (!empty($flash)): ?>
<div class="alert alert-<?= $flash['tipo'] === 'ok' ? 'ok' : 'erro' ?>" role="status"><?= e($flash['msg']) ?></div>
<?php endif; ?>

<div class="panel-grid panel-grid-cards">
  <?php foreach ($escolas as $escola): ?>
  <section class="panel">
    <h2><?= e($escola['nome']) ?> <?= (int) $escola['ativo'] === 1 ? '' : '<span class="badge badge-erro">inativa</span>' ?></h2>
    <form method="post" action="<?= e(url('admin/escolas')) ?>">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
      <input type="hidden" name="id" value="<?= (int) $escola['id'] ?>">
      <div class="field"><label for="nome-<?= (int) $escola['id'] ?>">Nome</label>
        <input type="text" id="nome-<?= (int) $escola['id'] ?>" name="nome" value="<?= e($escola['nome']) ?>" required maxlength="120"></div>
      <div class="field-row">
        <div class="field"><label for="cidade-<?= (int) $escola['id'] ?>">Cidade</label>
          <input type="text" id="cidade-<?= (int) $escola['id'] ?>" name="cidade" value="<?= e($escola['cidade']) ?>" maxlength="120"></div>
        <div class="field"><label for="ordem-<?= (int) $escola['id'] ?>">Ordem</label>
          <input type="number" id="ordem-<?= (int) $escola['id'] ?>" name="ordem" value="<?= (int) $escola['ordem'] ?>" min="0" max="99"></div>
      </div>
      <div class="field"><label for="logo-<?= (int) $escola['id'] ?>">Caminho do logo</label>
        <input type="text" id="logo-<?= (int) $escola['id'] ?>" name="logo" value="<?= e($escola['logo']) ?>" maxlength="190"></div>
      <div class="field-row">
        <div class="field"><label for="whatsapp-<?= (int) $escola['id'] ?>">WhatsApp (só números)</label>
          <input type="text" id="whatsapp-<?= (int) $escola['id'] ?>" name="whatsapp" value="<?= e($escola['whatsapp']) ?>" maxlength="15"></div>
        <div class="field"><label for="telefone-<?= (int) $escola['id'] ?>">Telefone (só números)</label>
          <input type="text" id="telefone-<?= (int) $escola['id'] ?>" name="telefone" value="<?= e($escola['telefone']) ?>" maxlength="15"></div>
      </div>
      <div class="field"><label for="endereco-<?= (int) $escola['id'] ?>">Endereço</label>
        <input type="text" id="endereco-<?= (int) $escola['id'] ?>" name="endereco" value="<?= e($escola['endereco']) ?>" maxlength="255"></div>
      <div class="field"><label for="ativo-<?= (int) $escola['id'] ?>">Situação</label>
        <select id="ativo-<?= (int) $escola['id'] ?>" name="ativo">
          <option value="1" <?= (int) $escola['ativo'] === 1 ? 'selected' : '' ?>>Ativa</option>
          <option value="0" <?= (int) $escola['ativo'] === 0 ? 'selected' : '' ?>>Inativa</option>
        </select></div>
      <button type="submit" class="btn btn-gold btn-sm">Salvar</button>
    </form>
  </section>
  <?php endforeach; ?>

  <section class="panel panel-nova">
    <h2>Adicionar escola</h2>
    <form method="post" action="<?= e(url('admin/escolas')) ?>">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
      <input type="hidden" name="id" value="0">
      <div class="field"><label for="nome-nova">Nome</label>
        <input type="text" id="nome-nova" name="nome" required maxlength="120"></div>
      <div class="field-row">
        <div class="field"><label for="cidade-nova">Cidade</label>
          <input type="text" id="cidade-nova" name="cidade" maxlength="120"></div>
        <div class="field"><label for="ordem-nova">Ordem</label>
          <input type="number" id="ordem-nova" name="ordem" value="9" min="0" max="99"></div>
      </div>
      <div class="field"><label for="logo-nova">Caminho do logo (ex.: assets/img/logos/escola.png)</label>
        <input type="text" id="logo-nova" name="logo" maxlength="190"></div>
      <input type="hidden" name="ativo" value="1">
      <button type="submit" class="btn btn-gold btn-sm">Adicionar</button>
    </form>
  </section>
</div>
