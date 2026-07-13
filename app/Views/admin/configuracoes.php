<?php
/** Configurações da campanha: $campos, $valores, $csrf, $flash */
$multilinha = ['campanha_descricao', 'mensagem_confirmacao', 'mensagem_encerrada', 'resultado_info'];
?>
<div class="admin-page-head">
  <div>
    <h1>Configurações da campanha</h1>
    <p class="page-meta">Textos públicos, período de inscrição e mensagens do fluxo. As datas por unidade são aplicadas automaticamente no formulário.</p>
  </div>
</div>

<?php if (!empty($flash)): ?>
<div class="alert alert-<?= $flash['tipo'] === 'ok' ? 'ok' : 'erro' ?>" role="status"><?= e($flash['msg']) ?></div>
<?php endif; ?>

<section class="panel">
  <form method="post" action="<?= e(url('admin/configuracoes')) ?>">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <?php foreach ($campos as $chave => $rotulo): ?>
    <div class="field">
      <label for="cfg-<?= e($chave) ?>"><?= e($rotulo) ?></label>
      <?php if (in_array($chave, $multilinha, true)): ?>
      <textarea id="cfg-<?= e($chave) ?>" name="<?= e($chave) ?>" rows="3" maxlength="2000"><?= e($valores[$chave] ?? '') ?></textarea>
      <?php elseif ($chave === 'inscricoes_abertas'): ?>
      <select id="cfg-<?= e($chave) ?>" name="<?= e($chave) ?>">
        <option value="1" <?= ($valores[$chave] ?? '1') === '1' ? 'selected' : '' ?>>Sim — inscrições abertas</option>
        <option value="0" <?= ($valores[$chave] ?? '1') === '0' ? 'selected' : '' ?>>Não — inscrições fechadas</option>
      </select>
      <?php else: ?>
      <input type="text" id="cfg-<?= e($chave) ?>" name="<?= e($chave) ?>" value="<?= e($valores[$chave] ?? '') ?>" maxlength="500">
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
    <button type="submit" class="btn btn-gold">Salvar configurações</button>
  </form>
</section>
