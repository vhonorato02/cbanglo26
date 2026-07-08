<?php
/**
 * Comprovante de inscrição.
 * $inscricao, $autorizado (bool — mostra dados completos), $config, $csrf
 */
$campanha = $config['campanha_nome'] ?? 'Concurso de Bolsas';
$dataProva = $config['data_prova'] ?? '';
$horaProva = $config['hora_prova'] ?? '';
$primeiroNome = explode(' ', trim($inscricao['aluno_nome']))[0] ?? '';
?>
<header class="page-header page-header-print">
  <div class="container">
    <a class="btn-link no-print" href="<?= e(url('/')) ?>">&larr; Voltar para a página inicial</a>
    <h1>Comprovante de inscrição</h1>
    <p class="page-sub"><?= e($campanha) ?></p>
  </div>
</header>
<main id="conteudo" class="container container-narrow page-content">
  <div class="comprovante-card">
    <div class="comprovante-head">
      <p class="comprovante-status" style="--status-cor: <?= e($inscricao['status_cor']) ?>">
        <?= e($inscricao['status_nome']) ?>
      </p>
      <p class="comprovante-protocolo">Protocolo<br><strong><?= e($inscricao['protocolo']) ?></strong></p>
    </div>

    <dl class="resumo-lista">
      <?php if ($autorizado): ?>
      <div><dt>Estudante</dt><dd><?= e($inscricao['aluno_nome']) ?></dd></div>
      <div><dt>Data de nascimento</dt><dd><?= e(data_br($inscricao['aluno_nascimento'])) ?></dd></div>
      <?php else: ?>
      <div><dt>Estudante</dt><dd><?= e($primeiroNome) ?> (dados completos disponíveis na <a class="no-print-link" href="<?= e(url('consulta')) ?>">consulta com e-mail</a>)</dd></div>
      <?php endif; ?>
      <div><dt>Série pretendida</dt><dd><?= e($inscricao['serie_nome']) ?></dd></div>
      <div><dt>Escola escolhida</dt><dd><?= e($inscricao['escola_nome']) ?><?= ($inscricao['escola_cidade'] ?? '') !== '' ? ' — ' . e($inscricao['escola_cidade']) : '' ?></dd></div>
      <?php if ($autorizado): ?>
      <div><dt>Escola atual</dt><dd><?= e($inscricao['escola_atual']) ?></dd></div>
      <div><dt>Responsável</dt><dd><?= e($inscricao['responsavel_nome']) ?> (<?= e($inscricao['parentesco']) ?>)</dd></div>
      <div><dt>WhatsApp</dt><dd><?= e($inscricao['whatsapp']) ?></dd></div>
      <div><dt>E-mail</dt><dd><?= e($inscricao['email']) ?></dd></div>
      <div><dt>Cidade</dt><dd><?= e($inscricao['cidade']) ?></dd></div>
      <?php endif; ?>
      <div><dt>Inscrição realizada em</dt><dd><?= e(data_br($inscricao['criado_em'], true)) ?></dd></div>
      <?php if ($dataProva !== ''): ?>
      <div><dt>Data da prova</dt><dd><?= e(data_br($dataProva)) ?><?= $horaProva !== '' ? ' às ' . e(substr($horaProva, 0, 5)) . 'h' : '' ?></dd></div>
      <?php endif; ?>
    </dl>

    <p class="comprovante-aviso">Guarde este protocolo. Ele identifica a inscrição em qualquer contato com a escola.</p>

    <div class="sucesso-acoes no-print">
      <button type="button" class="btn btn-gold" data-imprimir>Imprimir / salvar PDF</button>
      <a class="btn btn-ghost-dark" href="<?= e(url('/')) ?>">Voltar ao site</a>
    </div>
  </div>
</main>
<footer class="site-footer site-footer-slim">
  <div class="container">
    <p class="footer-note"><?= e($campanha) ?> — Anglo Pinda · Colégio Fênix · Colégio Drummond · Anglo Cruzeiro</p>
  </div>
</footer>
