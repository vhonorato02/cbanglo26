<?php
/** Auditoria: $resultado */
?>
<div class="admin-page-head">
  <h1>Auditoria <span class="count-pill"><?= (int) $resultado['total'] ?></span></h1>
</div>

<?php if ($resultado['rows'] === []): ?>
<p class="empty-note">Nenhuma ação registrada ainda.</p>
<?php else: ?>
<div class="tabela-wrap">
  <table class="tabela">
    <thead>
      <tr><th>Data</th><th>Usuário</th><th>Ação</th><th>Detalhes</th></tr>
    </thead>
    <tbody>
      <?php foreach ($resultado['rows'] as $log): ?>
      <tr>
        <td data-th="Data"><?= e(data_br($log['criado_em'], true)) ?></td>
        <td data-th="Usuário"><?= e($log['admin_nome'] ?? 'Sistema') ?></td>
        <td data-th="Ação"><code><?= e($log['acao']) ?></code></td>
        <td data-th="Detalhes"><?= e($log['detalhes']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php if ($resultado['pages'] > 1): ?>
<nav class="paginacao" aria-label="Paginação">
  <?php for ($p = 1; $p <= $resultado['pages']; $p++): ?>
    <?php if ($p === $resultado['page']): ?>
    <span class="pag-item is-current" aria-current="page"><?= $p ?></span>
    <?php else: ?>
    <a class="pag-item" href="?pagina=<?= $p ?>"><?= $p ?></a>
    <?php endif; ?>
  <?php endfor; ?>
</nav>
<?php endif; ?>
<?php endif; ?>
