/* ============================================================
   Concurso de Bolsas — JavaScript público
   Menu, FAQ, máscaras, formulário em etapas e envio assíncrono.
   Tudo com progressive enhancement: sem JS o formulário continua
   funcionando via POST tradicional.
   ============================================================ */

document.body.classList.add('js');

/* ---------- Menu móvel ---------- */
(function () {
  const toggle = document.querySelector('.nav-toggle');
  const list = document.getElementById('menu-principal');
  if (!toggle || !list) return;
  toggle.addEventListener('click', () => {
    const open = list.classList.toggle('is-open');
    toggle.setAttribute('aria-expanded', String(open));
  });
  list.addEventListener('click', (e) => {
    if (e.target.closest('a')) {
      list.classList.remove('is-open');
      toggle.setAttribute('aria-expanded', 'false');
    }
  });
})();

/* ---------- Botões de impressão ---------- */
document.querySelectorAll('[data-imprimir]').forEach((btn) => {
  btn.addEventListener('click', () => window.print());
});

/* ---------- Máscaras ---------- */
function mascaraData(valor) {
  const d = valor.replace(/\D/g, '').slice(0, 8);
  if (d.length <= 2) return d;
  if (d.length <= 4) return d.slice(0, 2) + '/' + d.slice(2);
  return d.slice(0, 2) + '/' + d.slice(2, 4) + '/' + d.slice(4);
}
function mascaraTelefone(valor) {
  const d = valor.replace(/\D/g, '').slice(0, 11);
  if (d.length === 0) return '';
  if (d.length <= 2) return '(' + d;
  if (d.length <= 6) return '(' + d.slice(0, 2) + ') ' + d.slice(2);
  if (d.length <= 10) return '(' + d.slice(0, 2) + ') ' + d.slice(2, 6) + '-' + d.slice(6);
  return '(' + d.slice(0, 2) + ') ' + d.slice(2, 7) + '-' + d.slice(7);
}
document.querySelectorAll('[data-mask]').forEach((input) => {
  const tipo = input.getAttribute('data-mask');
  const fn = tipo === 'data' ? mascaraData : mascaraTelefone;
  input.addEventListener('input', () => {
    const pos = input.selectionStart;
    const antes = input.value.length;
    input.value = fn(input.value);
    const depois = input.value.length;
    if (pos !== null) {
      const novo = pos + (depois - antes);
      input.setSelectionRange(Math.max(0, novo), Math.max(0, novo));
    }
  });
});

/* ---------- Validação de campos ---------- */
const validadores = {
  aluno_nome: (v) => (v.trim().length >= 5 && v.trim().includes(' ')) || 'Informe nome e sobrenome.',
  aluno_nascimento: (v) => {
    const m = v.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
    if (!m) return 'Use o formato dd/mm/aaaa.';
    const [, dd, mm, aaaa] = m.map(Number);
    const data = new Date(aaaa, mm - 1, dd);
    if (data.getFullYear() !== aaaa || data.getMonth() !== mm - 1 || data.getDate() !== dd) {
      return 'Esta data não existe no calendário.';
    }
    const hoje = new Date();
    if (data >= hoje || aaaa < hoje.getFullYear() - 25) return 'Confira a data informada.';
    return true;
  },
  serie_id: (v) => v !== '' || 'Escolha a série pretendida.',
  escola_id: (v) => v !== '' || 'Escolha a unidade desejada.',
  escola_atual: (v) => v.trim().length >= 2 || 'Informe a escola atual.',
  responsavel_nome: (v) => (v.trim().length >= 5 && v.trim().includes(' ')) || 'Informe nome e sobrenome.',
  parentesco: (v) => v !== '' || 'Informe o grau de parentesco.',
  whatsapp: (v) => {
    const d = v.replace(/\D/g, '');
    if (d.length < 10 || d.length > 11) return 'Informe DDD + número.';
    if (d.length === 11 && d[2] !== '9') return 'Número de celular inválido.';
    return true;
  },
  email: (v) => /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v.trim()) || 'E-mail inválido.',
  cidade: (v) => v.trim().length >= 2 || 'Informe a cidade.',
};

function campoWrapper(el) {
  return el.closest('.field, .field-radio-group');
}

function validarCampo(el) {
  const nome = el.name;
  const wrapper = campoWrapper(el);
  if (!wrapper) return true;

  let valido = true;
  let mensagem = '';

  if (el.type === 'radio') {
    const grupo = el.closest('form').querySelectorAll(`input[name="${nome}"]`);
    valido = Array.from(grupo).some((r) => r.checked);
    mensagem = 'Escolha uma unidade.';
  } else if (el.type === 'checkbox') {
    valido = el.checked;
    mensagem = 'É necessário marcar esta opção para continuar.';
  } else if (validadores[nome]) {
    const resultado = validadores[nome](el.value);
    valido = resultado === true;
    mensagem = typeof resultado === 'string' ? resultado : '';
  } else if (el.required) {
    valido = el.value.trim() !== '';
    mensagem = 'Campo obrigatório.';
  }

  const erroEl = wrapper.querySelector('.field-error');
  if (valido) {
    wrapper.classList.remove('has-error');
    if (erroEl) erroEl.textContent = '';
  } else {
    wrapper.classList.add('has-error');
    if (erroEl) erroEl.textContent = mensagem;
  }
  return valido;
}

/* ---------- Formulário em etapas ---------- */
(function () {
  const form = document.getElementById('form-inscricao');
  if (!form) return;

  const etapas = Array.from(form.querySelectorAll('.form-step'));
  const dots = document.querySelectorAll('[data-step-dot]');
  const alerta = document.getElementById('form-alerta');
  const sucesso = document.getElementById('form-sucesso');
  const indicador = document.querySelector('.steps-indicator');
  let atual = 1;

  function mostrarEtapa(n, focar = true) {
    atual = n;
    etapas.forEach((et) => {
      et.classList.toggle('is-active', Number(et.dataset.step) === n);
    });
    dots.forEach((dot) => {
      const num = Number(dot.dataset.stepDot);
      dot.classList.toggle('is-active', num === n);
      dot.classList.toggle('is-done', num < n);
    });
    if (focar) {
      const etapa = etapas.find((et) => Number(et.dataset.step) === n);
      const primeiro = etapa && etapa.querySelector('input:not([type=hidden]), select, textarea, button');
      if (primeiro) primeiro.focus({ preventScroll: false });
      const cartao = form.closest('.form-card');
      if (cartao) cartao.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  }

  function validarEtapa(n) {
    const etapa = etapas.find((et) => Number(et.dataset.step) === n);
    if (!etapa) return true;
    const campos = etapa.querySelectorAll('input:not([type=hidden]):not(#website), select, textarea');
    let primeiroErro = null;
    const radiosVistos = new Set();
    campos.forEach((el) => {
      if (el.type === 'radio') {
        if (radiosVistos.has(el.name)) return;
        radiosVistos.add(el.name);
      }
      if (!validarCampo(el) && !primeiroErro) primeiroErro = el;
    });
    if (primeiroErro) {
      primeiroErro.focus();
      return false;
    }
    return true;
  }

  function preencherResumo() {
    const dados = new FormData(form);
    document.querySelectorAll('[data-resumo]').forEach((dd) => {
      const campo = dd.dataset.resumo;
      let valor = (dados.get(campo) || '').toString();
      if (campo === 'serie_id' || campo === 'escola_id') {
        const el = form.querySelector(`[name="${campo}"]`);
        if (el && el.tagName === 'SELECT') {
          valor = el.selectedIndex > 0 ? el.options[el.selectedIndex].text.trim() : '';
        } else {
          const marcado = form.querySelector(`input[name="${campo}"]:checked`);
          const rotulo = marcado && marcado.closest('.radio-card')?.querySelector('strong');
          valor = rotulo ? rotulo.textContent.trim() : '';
        }
      }
      dd.textContent = valor || '—';
    });
  }

  mostrarEtapa(1, false);

  form.addEventListener('click', (e) => {
    const avancar = e.target.closest('[data-avancar]');
    const voltar = e.target.closest('[data-voltar]');
    const irPara = e.target.closest('[data-ir-para]');
    if (avancar) {
      if (validarEtapa(atual)) {
        if (atual + 1 === 3) preencherResumo();
        mostrarEtapa(Math.min(3, atual + 1));
      }
    } else if (voltar) {
      mostrarEtapa(Math.max(1, atual - 1));
    } else if (irPara) {
      mostrarEtapa(Number(irPara.dataset.irPara) || 1);
    }
  });

  // Validação em tempo real ao sair do campo
  form.addEventListener('blur', (e) => {
    const el = e.target;
    if (el.matches('input:not([type=hidden]):not(#website), select, textarea')) {
      if (el.value !== '' || el.type === 'checkbox') validarCampo(el);
    }
  }, true);
  form.addEventListener('change', (e) => {
    if (e.target.matches('input[type=radio], input[type=checkbox], select')) validarCampo(e.target);
  });

  // Envio assíncrono com fallback tradicional
  let enviando = false;
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (enviando) return;
    if (!validarEtapa(1)) { mostrarEtapa(1); return; }
    if (!validarEtapa(2)) { mostrarEtapa(2); return; }
    if (!validarEtapa(3)) return;

    const botao = document.getElementById('btn-enviar');
    enviando = true;
    botao.disabled = true;
    botao.classList.add('is-loading');
    alerta.classList.add('hidden');

    try {
      const resposta = await fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      });
      const json = await resposta.json();

      if (json.ok) {
        form.classList.add('hidden');
        if (indicador) indicador.classList.add('hidden');
        sucesso.classList.remove('hidden');
        sucesso.querySelector('[data-protocolo]').textContent = json.protocolo;
        sucesso.querySelector('.sucesso-msg').textContent = json.mensagem || '';
        sucesso.querySelector('[data-comprovante]').href = json.comprovante;
        sucesso.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
      }

      // Erros de validação do servidor
      const erros = json.errors || {};
      let primeiraEtapaComErro = 0;
      Object.entries(erros).forEach(([campo, msg]) => {
        if (campo === '_geral') return;
        const el = form.querySelector(`[name="${campo}"]`);
        if (!el) return;
        const wrapper = campoWrapper(el);
        if (wrapper) {
          wrapper.classList.add('has-error');
          const erroEl = wrapper.querySelector('.field-error');
          if (erroEl) erroEl.textContent = msg;
        }
        const etapa = el.closest('.form-step');
        const numEtapa = etapa ? Number(etapa.dataset.step) : 1;
        if (!primeiraEtapaComErro || numEtapa < primeiraEtapaComErro) primeiraEtapaComErro = numEtapa;
      });

      alerta.textContent = erros._geral || 'Corrija os campos destacados e envie novamente.';
      alerta.classList.remove('hidden');
      if (primeiraEtapaComErro) mostrarEtapa(primeiraEtapaComErro, false);
      alerta.focus();
    } catch (_) {
      alerta.textContent = 'Falha de conexão. Verifique a internet e tente novamente.';
      alerta.classList.remove('hidden');
      alerta.focus();
    } finally {
      enviando = false;
      botao.disabled = false;
      botao.classList.remove('is-loading');
    }
  });
})();
