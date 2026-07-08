/* ============================================================
   Concurso de Bolsas — JavaScript do painel administrativo
   ============================================================ */

// Menu lateral no celular
(function () {
  const toggle = document.querySelector('.admin-menu-toggle');
  const sidebar = document.getElementById('admin-sidebar');
  if (!toggle || !sidebar) return;
  toggle.addEventListener('click', () => {
    const open = sidebar.classList.toggle('is-open');
    toggle.setAttribute('aria-expanded', String(open));
  });
  document.addEventListener('click', (e) => {
    if (sidebar.classList.contains('is-open') &&
        !sidebar.contains(e.target) && !toggle.contains(e.target)) {
      sidebar.classList.remove('is-open');
      toggle.setAttribute('aria-expanded', 'false');
    }
  });
})();

// Confirmação para ações destrutivas
document.querySelectorAll('form[data-confirmar]').forEach((form) => {
  form.addEventListener('submit', (e) => {
    if (!window.confirm(form.dataset.confirmar)) e.preventDefault();
  });
});

// Impressão
document.querySelectorAll('[data-imprimir]').forEach((btn) => {
  btn.addEventListener('click', () => window.print());
});

// Bloqueia duplo envio em todos os formulários do painel
document.querySelectorAll('form').forEach((form) => {
  form.addEventListener('submit', () => {
    const botao = form.querySelector('button[type=submit]');
    if (botao) setTimeout(() => { botao.disabled = true; }, 0);
  });
});
