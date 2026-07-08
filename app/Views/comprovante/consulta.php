<?php
/** Consulta de comprovante: protocolo + e-mail cadastrado. */
$campanha = $config['campanha_nome'] ?? 'Concurso de Bolsas';
?>
<header class="page-header">
  <div class="container">
    <a class="btn-link" href="<?= e(url('/')) ?>">&larr; Voltar para a página inicial</a>
    <h1>Consultar inscrição</h1>
    <p class="page-sub"><?= e($campanha) ?></p>
  </div>
</header>
<main id="conteudo" class="container container-narrow page-content">
  <div class="form-card">
    <?php if (!empty($erro)): ?>
    <div class="alert alert-erro" role="alert"><?= e($erro) ?></div>
    <?php endif; ?>
    <p>Informe o número de protocolo e o e-mail cadastrado na inscrição para
    visualizar e imprimir o comprovante completo.</p>
    <form method="post" action="<?= e(url('consulta')) ?>">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
      <div class="field">
        <label for="protocolo">Número de protocolo</label>
        <input type="text" id="protocolo" name="protocolo" placeholder="CB26-XXXX-XXXX"
               required maxlength="20" autocomplete="off">
      </div>
      <div class="field">
        <label for="email">E-mail cadastrado</label>
        <input type="email" id="email" name="email" required maxlength="190" autocomplete="email">
      </div>
      <div class="form-nav">
        <button type="submit" class="btn btn-gold btn-lg">Consultar</button>
      </div>
    </form>
  </div>
</main>
<footer class="site-footer site-footer-slim">
  <div class="container">
    <p class="footer-note"><?= e($campanha) ?> — Anglo Pinda · Colégio Fênix · Colégio Drummond · Anglo Cruzeiro</p>
  </div>
</footer>
