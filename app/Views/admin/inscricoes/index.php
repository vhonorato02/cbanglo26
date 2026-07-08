<?php
/** Lista de inscrições: $resultado, $filtros, $escolas, $series, $statusLista, $csrf, $flash */
$qs = static function (array $extra = []) use ($filtros): string {
    $params = array_filter(array_merge($filtros, $extra), fn ($v) => $v !== '' && $v !== 0);
    return http_build_query($params);
};
?>
<div class="admin-page-head">
  <h1>Inscrições <span class="count-pill"><?= (int) $resultado['total'] ?></span></h1>
  <a class="btn btn-gold btn-sm" href="<?= e(url('admin/inscricoes/exportar') . '?' . $qs()) ?>">Exportar CSV</a>
</div>

<?php if (!empty($flash)): ?>
<div class="alert alert-<?= $flash['tipo'] === 'ok' ? 'ok' : 'erro' ?>" role="status"><?= e($flash['msg']) ?></div>
<?php endif; ?>

<form class="filtros" method="get" action="<?= e(url('admin/inscricoes')) ?>">
  <div class="filtros-linha">
    <div class="field field-grow">
      <label for="busca">Buscar (nome, e-mail, WhatsApp, protocolo)</label>
      <input type="search" id="busca" name="busca" value="<?= e($filtros['busca']) ?>" maxlength="120">
    </div>
    <div class="field">
      <label for="escola_id">Escola</label>
      <select id="escola_id" name="escola_id">
        <option value="">Todas</option>
        <?php foreach ($escolas as $escola): ?>
        <option value="<?= (int) $escola['id'] ?>" <?= $filtros['escola_id'] === (int) $escola['id'] ? 'selected' : '' ?>><?= e($escola['nome']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field">
      <label for="serie_id">Série</label>
      <select id="serie_id" name="serie_id">
        <option value="">Todas</option>
        <?php foreach ($series as $serie): ?>
        <option value="<?= (int) $serie['id'] ?>" <?= $filtros['serie_id'] === (int) $serie['id'] ? 'selected' : '' ?>><?= e($serie['nome']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field">
      <label for="status_id">Status</label>
      <select id="status_id" name="status_id">
        <option value="">Todos</option>
        <?php foreach ($statusLista as $status): ?>
        <option value="<?= (int) $status['id'] ?>" <?= $filtros['status_id'] === (int) $status['id'] ? 'selected' : '' ?>><?= e($status['nome']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
  <div class="filtros-linha">
    <div class="field">
      <label for="de">De</label>
      <input type="date" id="de" name="de" value="<?= e($filtros['de']) ?>">
    </div>
    <div class="field">
      <label for="ate">Até</label>
      <input type="date" id="ate" name="ate" value="<?= e($filtros['ate']) ?>">
    </div>
    <div class="field">
      <label for="ordenar">Ordenar por</label>
      <select id="ordenar" name="ordenar">
        <option value="" <?= $filtros['ordenar'] === '' ? 'selected' : '' ?>>Mais recentes</option>
        <option value="antigas" <?= $filtros['ordenar'] === 'antigas' ? 'selected' : '' ?>>Mais antigas</option>
        <option value="nome" <?= $filtros['ordenar'] === 'nome' ? 'selected' : '' ?>>Nome (A–Z)</option>
        <option value="nome_desc" <?= $filtros['ordenar'] === 'nome_desc' ? 'selected' : '' ?>>Nome (Z–A)</option>
      </select>
    </div>
    <div class="filtros-acoes">
      <button type="submit" class="btn btn-gold btn-sm">Filtrar</button>
      <a class="btn-link" href="<?= e(url('admin/inscricoes')) ?>">Limpar</a>
    </div>
  </div>
</form>

<?php if ($resultado['rows'] === []): ?>
<p class="empty-note">Nenhuma inscrição encontrada com os filtros atuais.</p>
<?php else: ?>
<div class="tabela-wrap">
  <table class="tabela">
    <thead>
      <tr><th>Protocolo</th><th>Estudante</th><th>Série</th><th>Escola</th><th>Responsável</th><th>Status</th><th>Data</th><th><span class="visually-hidden">Ações</span></th></tr>
    </thead>
    <tbody>
      <?php foreach ($resultado['rows'] as $r): ?>
      <tr>
        <td data-th="Protocolo"><a href="<?= e(url('admin/inscricoes/' . $r['id'])) ?>"><?= e($r['protocolo']) ?></a></td>
        <td data-th="Estudante"><?= e($r['aluno_nome']) ?></td>
        <td data-th="Série"><?= e($r['serie_nome']) ?></td>
        <td data-th="Escola"><?= e($r['escola_nome']) ?></td>
        <td data-th="Responsável"><?= e($r['responsavel_nome']) ?><br><small><?= e($r['whatsapp']) ?></small></td>
        <td data-th="Status"><span class="badge" style="--badge-cor: <?= e($r['status_cor']) ?>"><?= e($r['status_nome']) ?></span></td>
        <td data-th="Data"><?= e(data_br($r['criado_em'], true)) ?></td>
        <td data-th="Ações"><a class="btn-link" href="<?= e(url('admin/inscricoes/' . $r['id'])) ?>">Detalhes</a></td>
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
    <?php elseif ($p <= 2 || $p > $resultado['pages'] - 2 || abs($p - $resultado['page']) <= 2): ?>
    <a class="pag-item" href="?<?= e($qs(['pagina' => $p])) ?>"><?= $p ?></a>
    <?php elseif (abs($p - $resultado['page']) === 3): ?>
    <span class="pag-item pag-dots">…</span>
    <?php endif; ?>
  <?php endfor; ?>
</nav>
<?php endif; ?>
<?php endif; ?>
