<?php
/** Detalhe da inscrição: $inscricao, $observacoes, $historico, $escolas, $series, $statusLista, $csrf, $flash, $erros */
$erros = $erros ?? [];
$nascimentoBr = data_br($inscricao['aluno_nascimento']);
$dataProvaTexto = !empty($inscricao['data_prova']) ? \App\Core\Provas::rotuloSelecionada($inscricao['data_prova']) : 'Sem data';
?>
<div class="admin-page-head">
  <div>
    <a class="btn-link" href="<?= e(url('admin/inscricoes')) ?>">&larr; Inscrições</a>
    <h1>Inscrição <?= e($inscricao['protocolo']) ?></h1>
    <p class="page-meta">Registrada em <?= e(data_br($inscricao['criado_em'], true)) ?> ·
      Prova: <?= e($dataProvaTexto) ?> · Termo <?= e($inscricao['consent_versao']) ?> aceito em <?= e(data_br($inscricao['consent_data'], true)) ?></p>
  </div>
  <button type="button" class="btn btn-ghost-dark btn-sm no-print" data-imprimir>Imprimir</button>
</div>

<?php if (!empty($flash)): ?>
<div class="alert alert-<?= $flash['tipo'] === 'ok' ? 'ok' : 'erro' ?>" role="status"><?= e($flash['msg']) ?></div>
<?php endif; ?>

<div class="panel-grid panel-grid-2">
  <section class="panel">
    <h2>Dados da inscrição</h2>
    <form method="post" action="<?= e(url('admin/inscricoes/' . $inscricao['id'])) ?>">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">

      <div class="field <?= isset($erros['aluno_nome']) ? 'has-error' : '' ?>">
        <label for="aluno_nome">Nome do estudante</label>
        <input type="text" id="aluno_nome" name="aluno_nome" value="<?= e($inscricao['aluno_nome']) ?>" required maxlength="150">
        <?php if (isset($erros['aluno_nome'])): ?><p class="field-error"><?= e($erros['aluno_nome']) ?></p><?php endif; ?>
      </div>

      <div class="field-row">
        <div class="field <?= isset($erros['aluno_nascimento']) ? 'has-error' : '' ?>">
          <label for="aluno_nascimento">Nascimento (dd/mm/aaaa)</label>
          <input type="text" id="aluno_nascimento" name="aluno_nascimento" value="<?= e($nascimentoBr) ?>" required maxlength="10">
          <?php if (isset($erros['aluno_nascimento'])): ?><p class="field-error"><?= e($erros['aluno_nascimento']) ?></p><?php endif; ?>
        </div>
        <div class="field">
          <label for="serie_id">Série</label>
          <select id="serie_id" name="serie_id" required>
            <?php foreach ($series as $serie): ?>
            <option value="<?= (int) $serie['id'] ?>" <?= (int) $inscricao['serie_id'] === (int) $serie['id'] ? 'selected' : '' ?>><?= e($serie['nome']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="field-row">
        <div class="field">
          <label for="escola_id">Escola escolhida</label>
          <select id="escola_id" name="escola_id" required>
            <?php foreach ($escolas as $escola): ?>
            <option value="<?= (int) $escola['id'] ?>" <?= (int) $inscricao['escola_id'] === (int) $escola['id'] ? 'selected' : '' ?>><?= e($escola['nome']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field <?= isset($erros['escola_atual']) ? 'has-error' : '' ?>">
          <label for="escola_atual">Escola atual</label>
          <input type="text" id="escola_atual" name="escola_atual" value="<?= e($inscricao['escola_atual']) ?>" required maxlength="150">
        </div>
      </div>

      <div class="field <?= isset($erros['data_prova']) ? 'has-error' : '' ?>">
        <label for="data_prova">Data da prova</label>
        <select id="data_prova" name="data_prova" required>
          <?php foreach (($provasPorEscola ?? []) as $escolaId => $grupo): ?>
            <?php foreach ($grupo['datas'] as $opcao): ?>
            <option value="<?= e($opcao['data']) ?>" <?= (string) $inscricao['data_prova'] === (string) $opcao['data'] && (string) $inscricao['escola_id'] === (string) $escolaId ? 'selected' : '' ?>>
              <?= e($grupo['escola']) ?> · <?= e($opcao['label']) ?>
            </option>
            <?php endforeach; ?>
          <?php endforeach; ?>
        </select>
        <?php if (isset($erros['data_prova'])): ?><p class="field-error"><?= e($erros['data_prova']) ?></p><?php endif; ?>
      </div>

      <div class="field <?= isset($erros['responsavel_nome']) ? 'has-error' : '' ?>">
        <label for="responsavel_nome">Responsável</label>
        <input type="text" id="responsavel_nome" name="responsavel_nome" value="<?= e($inscricao['responsavel_nome']) ?>" required maxlength="150">
      </div>

      <div class="field-row">
        <div class="field <?= isset($erros['parentesco']) ? 'has-error' : '' ?>">
          <label for="parentesco">Parentesco</label>
          <input type="text" id="parentesco" name="parentesco" value="<?= e($inscricao['parentesco']) ?>" required maxlength="60">
        </div>
        <div class="field <?= isset($erros['whatsapp']) ? 'has-error' : '' ?>">
          <label for="whatsapp">WhatsApp</label>
          <input type="tel" id="whatsapp" name="whatsapp" value="<?= e($inscricao['whatsapp']) ?>" required maxlength="16">
          <?php if (isset($erros['whatsapp'])): ?><p class="field-error"><?= e($erros['whatsapp']) ?></p><?php endif; ?>
        </div>
      </div>

      <div class="field-row">
        <div class="field <?= isset($erros['email']) ? 'has-error' : '' ?>">
          <label for="email">E-mail</label>
          <input type="email" id="email" name="email" value="<?= e($inscricao['email']) ?>" required maxlength="190">
          <?php if (isset($erros['email'])): ?><p class="field-error"><?= e($erros['email']) ?></p><?php endif; ?>
        </div>
        <div class="field <?= isset($erros['cidade']) ? 'has-error' : '' ?>">
          <label for="cidade">Cidade</label>
          <input type="text" id="cidade" name="cidade" value="<?= e($inscricao['cidade']) ?>" required maxlength="120">
        </div>
      </div>

      <div class="field">
        <label for="status_id">Status</label>
        <select id="status_id" name="status_id">
          <?php foreach ($statusLista as $status): ?>
          <option value="<?= (int) $status['id'] ?>" <?= (int) $inscricao['status_id'] === (int) $status['id'] ? 'selected' : '' ?>><?= e($status['nome']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <button type="submit" class="btn btn-gold">Salvar alterações</button>
    </form>
  </section>

  <div>
    <section class="panel">
      <h2>Observações internas</h2>
      <form method="post" action="<?= e(url('admin/inscricoes/' . $inscricao['id'] . '/observar')) ?>">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <div class="field">
          <label for="texto">Nova observação</label>
          <textarea id="texto" name="texto" rows="3" maxlength="2000" required></textarea>
        </div>
        <button type="submit" class="btn btn-ghost-dark btn-sm">Adicionar</button>
      </form>
      <?php if ($observacoes === []): ?>
      <p class="empty-note">Nenhuma observação registrada.</p>
      <?php else: ?>
      <ul class="obs-lista">
        <?php foreach ($observacoes as $obs): ?>
        <li>
          <p><?= nl2br(e($obs['texto'])) ?></p>
          <small><?= e($obs['admin_nome'] ?? 'Sistema') ?> — <?= e(data_br($obs['criado_em'], true)) ?></small>
        </li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>
    </section>

    <section class="panel">
      <h2>Histórico de alterações</h2>
      <?php if ($historico === []): ?>
      <p class="empty-note">Nenhuma alteração registrada.</p>
      <?php else: ?>
      <ul class="hist-lista">
        <?php foreach ($historico as $h): ?>
        <li>
          <strong><?= e($h['campo']) ?></strong>:
          <span class="hist-antes"><?= e($h['valor_anterior']) ?></span> →
          <span class="hist-depois"><?= e($h['valor_novo']) ?></span>
          <small><?= e($h['admin_nome'] ?? 'Sistema') ?> — <?= e(data_br($h['criado_em'], true)) ?></small>
        </li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>
    </section>

    <section class="panel panel-perigo">
      <h2>Zona de risco (LGPD)</h2>
      <p class="empty-note">A exclusão remove definitivamente todos os dados desta inscrição
      (atende a pedidos de exclusão do titular). Esta ação não pode ser desfeita.</p>
      <form method="post" action="<?= e(url('admin/inscricoes/' . $inscricao['id'] . '/excluir')) ?>"
            data-confirmar="Excluir DEFINITIVAMENTE a inscrição <?= e($inscricao['protocolo']) ?>? Esta ação não pode ser desfeita.">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <button type="submit" class="btn btn-perigo btn-sm">Excluir inscrição</button>
      </form>
    </section>
  </div>
</div>
