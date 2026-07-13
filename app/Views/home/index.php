<?php
/**
 * Landing page do Concurso de Bolsas.
 * Variáveis: $config, $escolas, $series, $faqs, $provasPorEscola,
 * $calendarioProvas, $inscricoesAbertas, $csrf, $formTs, $old, $formErrors
 */
$campanha = $config['campanha_nome'] ?? 'Concurso de Bolsas 2026';
$chamada = $config['campanha_chamada'] ?? '';
$descricao = $config['campanha_descricao'] ?? '';
$inscricoesFim = $config['inscricoes_fim'] ?? '';
$inscricoesTexto = $inscricoesFim !== '' ? 'Até ' . data_br($inscricoesFim) : 'Inscrição online';
$old = $old ?? [];
$formErrors = $formErrors ?? [];
$temErros = $formErrors !== [];
$provasPorEscola = $provasPorEscola ?? [];
$calendarioProvas = $calendarioProvas ?? [];
$provasJson = json_encode($provasPorEscola, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if ($provasJson === false) {
    $provasJson = '{}';
}
$contatosUnidades = [
    ['nome' => 'Anglo Pinda', 'whatsapp' => '5512991936523'],
    ['nome' => 'Colégio Fênix', 'whatsapp' => '5512991169782'],
    ['nome' => 'Colégio Drummond', 'whatsapp' => '5512991856338'],
    ['nome' => 'Anglo Cruzeiro', 'whatsapp' => '5512991052675'],
];
?>

<header class="site-header" id="topo">
  <div class="container header-inner">
    <a class="brand" href="#topo" aria-label="<?= e($campanha) ?> — voltar ao início">
      <span class="brand-mark" aria-hidden="true">26</span>
      <span class="brand-text">Bolsas<br>2026</span>
    </a>
    <nav class="site-nav" aria-label="Navegação principal">
      <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="menu-principal">
        <span class="nav-toggle-bar" aria-hidden="true"></span>
        <span class="visually-hidden">Abrir menu</span>
      </button>
      <ul id="menu-principal" class="nav-list">
        <li><a href="#inscricao">Inscrição</a></li>
        <li><a href="#contato-unidades">Contato</a></li>
        <?php if ($faqs !== []): ?>
        <li><a href="#faq">Dúvidas</a></li>
        <?php endif; ?>
        <li><a href="<?= e(url('consulta')) ?>">Consultar</a></li>
      </ul>
    </nav>
  </div>
</header>

<main id="conteudo">
  <section class="hero" aria-labelledby="hero-titulo">
    <picture class="hero-bg" aria-hidden="true">
      <source media="(max-width: 767px)" type="image/webp" srcset="<?= e(url('assets/img/hero-768.webp')) ?>">
      <source media="(max-width: 1365px)" type="image/webp" srcset="<?= e(url('assets/img/hero-1280.webp')) ?>">
      <source type="image/webp" srcset="<?= e(url('assets/img/hero-1920.webp')) ?>">
      <img src="<?= e(url('assets/img/hero-1920.jpg')) ?>" alt="" width="1920" height="1080" fetchpriority="high">
    </picture>
    <div class="hero-overlay" aria-hidden="true"></div>

    <div class="container hero-grid">
      <div class="hero-copy">
        <p class="hero-kicker"><?= $inscricoesAbertas ? 'Inscrições abertas' : 'Inscrições encerradas' ?></p>
        <h1 id="hero-titulo" class="hero-title"><?= e($campanha) ?></h1>
        <p class="hero-sub"><?= e($chamada !== '' ? $chamada : ($descricao !== '' ? $descricao : 'Garanta a participação do estudante na prova de bolsas.')) ?></p>

        <p class="hero-prova"><strong>Provas às 9h:</strong> Anglo Pinda em 26 de setembro ou 17 de outubro; Fênix, Drummond e Anglo Cruzeiro em 17 de outubro. <?= e($inscricoesTexto) ?>.</p>
        <div class="hero-schedule" aria-label="Datas das provas por unidade">
          <?php foreach ($calendarioProvas as $item): ?>
          <p>
            <span><?= e($item['unidade']) ?></span>
            <strong><?= e($item['datas']) ?> · <?= e($item['hora']) ?></strong>
          </p>
          <?php endforeach; ?>
        </div>
      </div>

      <aside class="signup-panel form-card" id="inscricao" aria-labelledby="inscricao-titulo">
        <div class="form-intro">
          <p class="section-kicker">Inscrição online</p>
          <h2 class="section-title" id="inscricao-titulo">Faça sua inscrição</h2>
          <?php if ($inscricoesAbertas): ?>
          <p class="form-shortcut">Já fez a inscrição? <a href="<?= e(url('consulta')) ?>">Consulte seu comprovante</a>.</p>
          <?php endif; ?>
        </div>

        <?php if (!$inscricoesAbertas): ?>
        <div class="alert alert-info" role="status">
          <?= e($config['mensagem_encerrada'] ?? 'As inscrições estão encerradas.') ?>
        </div>
        <?php else: ?>
        <div class="alert alert-erro <?= $temErros ? '' : 'hidden' ?>" id="form-alerta" role="alert" tabindex="-1">
          <?php if (isset($formErrors['_geral'])): ?>
            <?= e($formErrors['_geral']) ?>
          <?php elseif ($temErros): ?>
            Corrija os campos destacados abaixo e envie novamente.
          <?php else: ?>
            Corrija os campos destacados abaixo e envie novamente.
          <?php endif; ?>
        </div>

        <ol class="steps-indicator js-only" aria-hidden="true">
          <li class="step-dot is-active" data-step-dot="1"><span class="step-dot-num">1</span><span class="step-dot-label">Estudante</span></li>
          <li class="step-dot" data-step-dot="2"><span class="step-dot-num">2</span><span class="step-dot-label">Responsável</span></li>
          <li class="step-dot" data-step-dot="3"><span class="step-dot-num">3</span><span class="step-dot-label">Confirmar</span></li>
        </ol>

        <form id="form-inscricao" method="post" action="<?= e(url('inscricao')) ?>" novalidate>
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <input type="hidden" name="_ts" value="<?= e((string) $formTs) ?>">

          <div class="hp-field" aria-hidden="true">
            <label for="website">Não preencha este campo</label>
            <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
          </div>

          <fieldset class="form-step" data-step="1">
            <legend class="form-step-title">Dados do estudante</legend>

            <div class="field <?= isset($formErrors['aluno_nome']) ? 'has-error' : '' ?>">
              <label for="aluno_nome">Nome completo do estudante</label>
              <input type="text" id="aluno_nome" name="aluno_nome" autocomplete="name"
                     value="<?= e($old['aluno_nome'] ?? '') ?>" required
                     aria-describedby="erro-aluno_nome" maxlength="150">
              <p class="field-error" id="erro-aluno_nome"><?= e($formErrors['aluno_nome'] ?? '') ?></p>
            </div>

            <div class="field-row">
              <div class="field <?= isset($formErrors['aluno_nascimento']) ? 'has-error' : '' ?>">
                <label for="aluno_nascimento">Data de nascimento</label>
                <input type="text" id="aluno_nascimento" name="aluno_nascimento" inputmode="numeric"
                       placeholder="dd/mm/aaaa" value="<?= e($old['aluno_nascimento'] ?? '') ?>" required
                       aria-describedby="erro-aluno_nascimento" maxlength="10" data-mask="data">
                <p class="field-error" id="erro-aluno_nascimento"><?= e($formErrors['aluno_nascimento'] ?? '') ?></p>
              </div>
              <div class="field <?= isset($formErrors['serie_id']) ? 'has-error' : '' ?>">
                <label for="serie_id">Série pretendida</label>
                <select id="serie_id" name="serie_id" required aria-describedby="erro-serie_id">
                  <option value="">Selecione</option>
                  <?php foreach ($series as $serie): ?>
                  <option value="<?= (int) $serie['id'] ?>" <?= (string) ($old['serie_id'] ?? '') === (string) $serie['id'] ? 'selected' : '' ?>>
                    <?= e($serie['nome']) ?>
                  </option>
                  <?php endforeach; ?>
                </select>
                <p class="field-error" id="erro-serie_id"><?= e($formErrors['serie_id'] ?? '') ?></p>
              </div>
            </div>

            <div class="field-row">
              <div class="field <?= isset($formErrors['escola_id']) ? 'has-error' : '' ?>">
                <label for="escola_id">Unidade desejada</label>
                <select id="escola_id" name="escola_id" required aria-describedby="erro-escola_id">
                  <option value="">Selecione</option>
                  <?php foreach ($escolas as $escola): ?>
                  <option value="<?= (int) $escola['id'] ?>" <?= (string) ($old['escola_id'] ?? '') === (string) $escola['id'] ? 'selected' : '' ?>>
                    <?= e($escola['nome']) ?><?= ($escola['cidade'] ?? '') !== '' ? ' — ' . e($escola['cidade']) : '' ?>
                  </option>
                  <?php endforeach; ?>
                </select>
                <p class="field-error" id="erro-escola_id"><?= e($formErrors['escola_id'] ?? '') ?></p>
              </div>

              <div class="field <?= isset($formErrors['data_prova']) ? 'has-error' : '' ?>">
                <label for="data_prova">Data da prova</label>
                <select id="data_prova" name="data_prova" required
                        data-provas='<?= e($provasJson) ?>'
                        data-selected="<?= e($old['data_prova'] ?? '') ?>"
                        aria-describedby="hint-data_prova erro-data_prova">
                  <option value="">Selecione a unidade primeiro</option>
                  <?php foreach ($provasPorEscola as $escolaId => $grupo): ?>
                    <?php foreach ($grupo['datas'] as $opcao): ?>
                    <option value="<?= e($opcao['data']) ?>" data-escola-id="<?= e((string) $escolaId) ?>" <?= (string) ($old['data_prova'] ?? '') === (string) $opcao['data'] && (string) ($old['escola_id'] ?? '') === (string) $escolaId ? 'selected' : '' ?>>
                      <?= e($grupo['escola']) ?> · <?= e($opcao['label']) ?>
                    </option>
                    <?php endforeach; ?>
                  <?php endforeach; ?>
                </select>
                <p class="field-hint" id="hint-data_prova">As datas aparecem de acordo com a unidade escolhida.</p>
                <p class="field-error" id="erro-data_prova"><?= e($formErrors['data_prova'] ?? '') ?></p>
              </div>
            </div>

            <div class="field <?= isset($formErrors['escola_atual']) ? 'has-error' : '' ?>">
              <label for="escola_atual">Escola onde estuda atualmente</label>
              <input type="text" id="escola_atual" name="escola_atual"
                     value="<?= e($old['escola_atual'] ?? '') ?>" required
                     aria-describedby="erro-escola_atual" maxlength="150">
              <p class="field-error" id="erro-escola_atual"><?= e($formErrors['escola_atual'] ?? '') ?></p>
            </div>

            <div class="form-nav js-only">
              <button type="button" class="btn btn-gold" data-avancar>Continuar</button>
            </div>
          </fieldset>

          <fieldset class="form-step" data-step="2">
            <legend class="form-step-title">Dados do responsável</legend>

            <div class="field <?= isset($formErrors['responsavel_nome']) ? 'has-error' : '' ?>">
              <label for="responsavel_nome">Nome completo do responsável</label>
              <input type="text" id="responsavel_nome" name="responsavel_nome" autocomplete="name"
                     value="<?= e($old['responsavel_nome'] ?? '') ?>" required
                     aria-describedby="erro-responsavel_nome" maxlength="150">
              <p class="field-error" id="erro-responsavel_nome"><?= e($formErrors['responsavel_nome'] ?? '') ?></p>
            </div>

            <div class="field-row">
              <div class="field <?= isset($formErrors['parentesco']) ? 'has-error' : '' ?>">
                <label for="parentesco">Parentesco</label>
                <select id="parentesco" name="parentesco" required aria-describedby="erro-parentesco">
                  <option value="">Selecione</option>
                  <?php foreach (['Mãe', 'Pai', 'Avó', 'Avô', 'Tia', 'Tio', 'Responsável legal', 'Outro'] as $grau): ?>
                  <option value="<?= e($grau) ?>" <?= ($old['parentesco'] ?? '') === $grau ? 'selected' : '' ?>><?= e($grau) ?></option>
                  <?php endforeach; ?>
                </select>
                <p class="field-error" id="erro-parentesco"><?= e($formErrors['parentesco'] ?? '') ?></p>
              </div>
              <div class="field <?= isset($formErrors['whatsapp']) ? 'has-error' : '' ?>">
                <label for="whatsapp">WhatsApp</label>
                <input type="tel" id="whatsapp" name="whatsapp" inputmode="tel" autocomplete="tel-national"
                       placeholder="(12) 99999-9999" value="<?= e($old['whatsapp'] ?? '') ?>" required
                       aria-describedby="erro-whatsapp" maxlength="16" data-mask="telefone">
                <p class="field-error" id="erro-whatsapp"><?= e($formErrors['whatsapp'] ?? '') ?></p>
              </div>
            </div>

            <div class="field-row">
              <div class="field <?= isset($formErrors['email']) ? 'has-error' : '' ?>">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" autocomplete="email"
                       value="<?= e($old['email'] ?? '') ?>" required
                       aria-describedby="erro-email" maxlength="190">
                <p class="field-error" id="erro-email"><?= e($formErrors['email'] ?? '') ?></p>
              </div>
              <div class="field <?= isset($formErrors['cidade']) ? 'has-error' : '' ?>">
                <label for="cidade">Cidade</label>
                <input type="text" id="cidade" name="cidade" autocomplete="address-level2"
                       value="<?= e($old['cidade'] ?? '') ?>" required
                       aria-describedby="erro-cidade" maxlength="120">
                <p class="field-error" id="erro-cidade"><?= e($formErrors['cidade'] ?? '') ?></p>
              </div>
            </div>

            <div class="form-nav js-only">
              <button type="button" class="btn btn-ghost-dark" data-voltar>Voltar</button>
              <button type="button" class="btn btn-gold" data-avancar>Revisar</button>
            </div>
          </fieldset>

          <fieldset class="form-step" data-step="3">
            <legend class="form-step-title">Revise e confirme</legend>

            <div class="resumo js-only" id="resumo-inscricao" aria-live="polite">
              <h3 class="resumo-titulo">Resumo da inscrição</h3>
              <dl class="resumo-lista">
                <div><dt>Estudante</dt><dd data-resumo="aluno_nome">—</dd></div>
                <div><dt>Nascimento</dt><dd data-resumo="aluno_nascimento">—</dd></div>
                <div><dt>Série</dt><dd data-resumo="serie_id">—</dd></div>
                <div><dt>Unidade</dt><dd data-resumo="escola_id">—</dd></div>
                <div><dt>Prova</dt><dd data-resumo="data_prova">—</dd></div>
                <div><dt>Escola atual</dt><dd data-resumo="escola_atual">—</dd></div>
                <div><dt>Responsável</dt><dd data-resumo="responsavel_nome">—</dd></div>
                <div><dt>Parentesco</dt><dd data-resumo="parentesco">—</dd></div>
                <div><dt>WhatsApp</dt><dd data-resumo="whatsapp">—</dd></div>
                <div><dt>E-mail</dt><dd data-resumo="email">—</dd></div>
                <div><dt>Cidade</dt><dd data-resumo="cidade">—</dd></div>
              </dl>
              <button type="button" class="btn-link" data-ir-para="1">Corrigir dados</button>
            </div>

            <div class="field field-check <?= isset($formErrors['consent_privacidade']) ? 'has-error' : '' ?>">
              <label class="check">
                <input type="checkbox" name="consent_privacidade" value="1" required
                       <?= isset($old['consent_privacidade']) ? 'checked' : '' ?>
                       aria-describedby="erro-consent_privacidade">
                <span>Autorizo o uso dos dados informados para processar esta inscrição.</span>
              </label>
              <p class="field-error" id="erro-consent_privacidade"><?= e($formErrors['consent_privacidade'] ?? '') ?></p>
            </div>

            <div class="field field-check <?= isset($formErrors['consent_contato']) ? 'has-error' : '' ?>">
              <label class="check">
                <input type="checkbox" name="consent_contato" value="1" required
                       <?= isset($old['consent_contato']) ? 'checked' : '' ?>
                       aria-describedby="erro-consent_contato">
                <span>Autorizo o contato por WhatsApp e e-mail sobre o concurso.</span>
              </label>
              <p class="field-error" id="erro-consent_contato"><?= e($formErrors['consent_contato'] ?? '') ?></p>
            </div>

            <div class="form-nav">
              <button type="button" class="btn btn-ghost-dark js-only" data-voltar>Voltar</button>
              <button type="submit" class="btn btn-gold" id="btn-enviar">
                <span class="btn-label">Confirmar inscrição</span>
                <span class="btn-loading" aria-hidden="true"></span>
              </button>
            </div>
            <p class="form-hint">Você receberá um protocolo para consultar o comprovante.</p>
          </fieldset>
        </form>

        <div class="form-sucesso hidden" id="form-sucesso" role="status" aria-live="polite">
          <div class="sucesso-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m8.5 12.5 2.5 2.5 5-5.5"/></svg>
          </div>
          <h3>Inscrição confirmada!</h3>
          <p class="sucesso-msg"></p>
          <p class="sucesso-protocolo">Protocolo: <strong data-protocolo>—</strong></p>
          <div class="sucesso-acoes">
            <a class="btn btn-gold" href="#" data-comprovante>Ver comprovante</a>
          </div>
        </div>
        <?php endif; ?>
      </aside>
    </div>
  </section>

  <section class="section section-compact section-soft" id="contato-unidades" aria-labelledby="contato-unidades-titulo">
    <div class="container">
      <div class="section-heading">
        <h2 class="section-title" id="contato-unidades-titulo">Fale conosco</h2>
      </div>
      <div class="contact-list">
        <?php foreach ($contatosUnidades as $contato): ?>
        <a class="contact-card" href="https://wa.me/<?= e($contato['whatsapp']) ?>" rel="noopener" target="_blank" aria-label="Chamar <?= e($contato['nome']) ?> no WhatsApp">
          <span class="contact-icon" aria-hidden="true">
            <svg viewBox="0 0 32 32" width="22" height="22" fill="currentColor"><path d="M16.02 3.2A12.59 12.59 0 0 0 5.3 22.4L4 29l6.75-1.25A12.58 12.58 0 1 0 16.02 3.2Zm0 22.98c-1.9 0-3.75-.52-5.37-1.5l-.38-.22-4 .74.76-3.88-.25-.4a10.3 10.3 0 1 1 9.24 5.26Zm5.8-7.72c-.32-.16-1.88-.93-2.17-1.04-.3-.1-.5-.16-.72.16-.2.32-.82 1.04-1 1.25-.19.21-.37.24-.69.08-.32-.16-1.36-.5-2.59-1.59a9.69 9.69 0 0 1-1.78-2.2c-.19-.32-.02-.49.14-.65.14-.14.32-.37.48-.56.16-.18.21-.32.32-.53.1-.21.05-.4-.03-.56-.08-.16-.72-1.73-.99-2.37-.26-.62-.52-.54-.72-.55h-.61c-.21 0-.56.08-.85.4-.3.32-1.12 1.1-1.12 2.68s1.15 3.1 1.31 3.31c.16.21 2.26 3.45 5.48 4.84.77.33 1.37.53 1.84.68.77.24 1.47.21 2.03.13.62-.09 1.88-.77 2.15-1.51.26-.74.26-1.38.18-1.51-.08-.14-.29-.22-.61-.38Z"/></svg>
          </span>
          <span class="contact-name"><?= e($contato['nome']) ?></span>
          <span class="contact-cta">WhatsApp</span>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <?php if ($faqs !== []): ?>
  <section class="section section-compact" id="faq" aria-labelledby="faq-titulo">
    <div class="container container-narrow">
      <p class="section-kicker">Dúvidas</p>
      <h2 class="section-title" id="faq-titulo">Perguntas frequentes</h2>
      <div class="faq-list">
        <?php foreach ($faqs as $faq): ?>
        <details class="faq-item">
          <summary><?= e($faq['pergunta']) ?><span class="faq-icon" aria-hidden="true"></span></summary>
          <div class="faq-answer"><p><?= e($faq['resposta']) ?></p></div>
        </details>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>
</main>

<footer class="site-footer">
  <div class="container">
    <nav class="footer-nav" aria-label="Links do rodapé">
      <a href="#inscricao">Inscrição</a>
      <a href="#contato-unidades">Contato</a>
      <a href="<?= e(url('consulta')) ?>">Consultar comprovante</a>
    </nav>
    <p class="footer-note"><?= e($campanha) ?> · Anglo Pinda · Colégio Fênix · Colégio Drummond · Anglo Cruzeiro.</p>
  </div>
</footer>
