<?php
/** Dashboard: $indicadores, $porEscola, $porSerie, $porStatus, $porProva, $calendarioProvas, $inscricoesAbertas, $recentes */
?>
<div class="admin-page-head">
  <div>
    <h1>Visão geral</h1>
    <p class="page-meta">Inscrições, distribuição por unidade e datas de prova.</p>
  </div>
  <span class="badge <?= $inscricoesAbertas ? 'badge-ok' : 'badge-erro' ?>">
    Inscrições <?= $inscricoesAbertas ? 'abertas' : 'fechadas' ?>
  </span>
</div>

<?php if (!empty($usaSenhaInicial)): ?>
<div class="alert alert-aviso" role="alert">
  Antes de publicar, altere a senha inicial em <a href="<?= e(url('admin/usuarios')) ?>">Usuários</a>.
</div>
<?php endif; ?>

<div class="stat-grid">
  <div class="stat-card">
    <p class="stat-num"><?= (int) $indicadores['total'] ?></p>
    <p class="stat-label">Inscrições no total</p>
  </div>
  <div class="stat-card">
    <p class="stat-num"><?= (int) $indicadores['hoje'] ?></p>
    <p class="stat-label">Hoje</p>
  </div>
  <div class="stat-card">
    <p class="stat-num"><?= (int) $indicadores['semana'] ?></p>
    <p class="stat-label">Últimos 7 dias</p>
  </div>
</div>

<div class="panel-grid panel-grid-2 dashboard-calendar">
  <section class="panel">
    <h2>Calendário oficial</h2>
    <ul class="calendar-list">
      <?php foreach (($calendarioProvas ?? []) as $item): ?>
      <li>
        <span><?= e($item['unidade']) ?></span>
        <strong><?= e($item['datas']) ?> · <?= e($item['hora']) ?></strong>
      </li>
      <?php endforeach; ?>
    </ul>
  </section>

  <section class="panel">
    <h2>Inscrições por data</h2>
    <?php if (($porProva ?? []) === []): ?>
    <p class="empty-note">Ainda não há inscrições por data.</p>
    <?php else: ?>
    <ul class="calendar-list calendar-list-count">
      <?php foreach ($porProva as $linha): ?>
      <li>
        <span><?= e(\App\Core\Provas::rotuloSelecionada($linha['nome'])) ?></span>
        <strong><?= (int) $linha['total'] ?></strong>
      </li>
      <?php endforeach; ?>
    </ul>
    <?php endif; ?>
  </section>
</div>

<div class="panel-grid">
  <?php
  $blocos = [
      'Por escola' => $porEscola,
      'Por série' => $porSerie,
      'Por status' => $porStatus,
  ];
  $maxTotais = 1;
  foreach ($blocos as $dados) {
      foreach ($dados as $linha) {
          $maxTotais = max($maxTotais, (int) $linha['total']);
      }
  }
  ?>
  <?php foreach ($blocos as $titulo => $dados): ?>
  <section class="panel">
    <h2><?= e($titulo) ?></h2>
    <ul class="bar-list">
      <?php foreach ($dados as $linha): ?>
      <li>
        <span class="bar-label"><?= e($linha['nome']) ?></span>
        <span class="bar-track"><span class="bar-fill" style="width: <?= max(2, round((int) $linha['total'] / $maxTotais * 100)) ?>%"></span></span>
        <span class="bar-num"><?= (int) $linha['total'] ?></span>
      </li>
      <?php endforeach; ?>
    </ul>
  </section>
  <?php endforeach; ?>
</div>

<section class="panel">
  <div class="panel-head">
    <h2>Inscrições recentes</h2>
    <a class="btn btn-gold btn-sm" href="<?= e(url('admin/inscricoes')) ?>">Ver todas</a>
  </div>
  <?php if ($recentes === []): ?>
  <p class="empty-note">Nenhuma inscrição registrada ainda.</p>
  <?php else: ?>
  <div class="tabela-wrap">
    <table class="tabela">
      <thead>
        <tr><th>Protocolo</th><th>Estudante</th><th>Série</th><th>Escola</th><th>Prova</th><th>Status</th><th>Data</th></tr>
      </thead>
      <tbody>
        <?php foreach ($recentes as $r): ?>
        <tr>
          <td data-th="Protocolo"><a href="<?= e(url('admin/inscricoes/' . $r['id'])) ?>"><?= e($r['protocolo']) ?></a></td>
          <td data-th="Estudante"><?= e($r['aluno_nome']) ?></td>
          <td data-th="Série"><?= e($r['serie_nome']) ?></td>
          <td data-th="Escola"><?= e($r['escola_nome']) ?></td>
          <td data-th="Prova"><?= e(\App\Core\Provas::rotuloSelecionada($r['data_prova'])) ?></td>
          <td data-th="Status"><span class="badge" style="--badge-cor: <?= e($r['status_cor']) ?>"><?= e($r['status_nome']) ?></span></td>
          <td data-th="Data"><?= e(data_br($r['criado_em'], true)) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</section>
