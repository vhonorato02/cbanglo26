<?php
/** Gerenciar FAQs: $faqs, $csrf, $flash */
?>
<div class="admin-page-head">
  <h1>Perguntas frequentes</h1>
</div>

<?php if (!empty($flash)): ?>
<div class="alert alert-<?= $flash['tipo'] === 'ok' ? 'ok' : 'erro' ?>" role="status"><?= e($flash['msg']) ?></div>
<?php endif; ?>

<div class="panel-grid panel-grid-cards">
  <?php foreach ($faqs as $faq): ?>
  <section class="panel">
    <h2>#<?= (int) $faq['ordem'] ?> <?= (int) $faq['ativo'] === 1 ? '' : '<span class="badge badge-erro">inativa</span>' ?></h2>
    <form method="post" action="<?= e(url('admin/faqs')) ?>">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
      <input type="hidden" name="id" value="<?= (int) $faq['id'] ?>">
      <div class="field"><label for="pergunta-<?= (int) $faq['id'] ?>">Pergunta</label>
        <input type="text" id="pergunta-<?= (int) $faq['id'] ?>" name="pergunta" value="<?= e($faq['pergunta']) ?>" required maxlength="255"></div>
      <div class="field"><label for="resposta-<?= (int) $faq['id'] ?>">Resposta</label>
        <textarea id="resposta-<?= (int) $faq['id'] ?>" name="resposta" rows="3" required maxlength="2000"><?= e($faq['resposta']) ?></textarea></div>
      <div class="field-row">
        <div class="field"><label for="ordem-<?= (int) $faq['id'] ?>">Ordem</label>
          <input type="number" id="ordem-<?= (int) $faq['id'] ?>" name="ordem" value="<?= (int) $faq['ordem'] ?>" min="0" max="99"></div>
        <div class="field"><label for="ativo-<?= (int) $faq['id'] ?>">Situação</label>
          <select id="ativo-<?= (int) $faq['id'] ?>" name="ativo">
            <option value="1" <?= (int) $faq['ativo'] === 1 ? 'selected' : '' ?>>Ativa</option>
            <option value="0" <?= (int) $faq['ativo'] === 0 ? 'selected' : '' ?>>Inativa</option>
          </select></div>
      </div>
      <button type="submit" class="btn btn-gold btn-sm">Salvar</button>
    </form>
    <form method="post" action="<?= e(url('admin/faqs/excluir')) ?>" data-confirmar="Excluir esta pergunta?">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
      <input type="hidden" name="id" value="<?= (int) $faq['id'] ?>">
      <button type="submit" class="btn-link btn-link-perigo">Excluir</button>
    </form>
  </section>
  <?php endforeach; ?>

  <section class="panel panel-nova">
    <h2>Adicionar pergunta</h2>
    <form method="post" action="<?= e(url('admin/faqs')) ?>">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
      <input type="hidden" name="id" value="0">
      <div class="field"><label for="pergunta-nova">Pergunta</label>
        <input type="text" id="pergunta-nova" name="pergunta" required maxlength="255"></div>
      <div class="field"><label for="resposta-nova">Resposta</label>
        <textarea id="resposta-nova" name="resposta" rows="3" required maxlength="2000"></textarea></div>
      <input type="hidden" name="ativo" value="1">
      <input type="hidden" name="ordem" value="9">
      <button type="submit" class="btn btn-gold btn-sm">Adicionar</button>
    </form>
  </section>
</div>
