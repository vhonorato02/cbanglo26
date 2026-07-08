<header class="page-header">
  <div class="container">
    <a class="btn-link" href="<?= e(url('/')) ?>">&larr; Voltar para a página inicial</a>
    <h1>Inscrição não encontrada</h1>
  </div>
</header>
<main id="conteudo" class="container container-narrow page-content">
  <div class="form-card">
    <p>Não encontramos nenhuma inscrição com o protocolo
    <strong><?= e($protocolo ?? '') ?></strong>.</p>
    <p>Confira se digitou o código corretamente ou <a href="<?= e(url('consulta')) ?>">faça a consulta pelo protocolo e e-mail</a>.</p>
  </div>
</main>
