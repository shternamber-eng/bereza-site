/* ============================================================
   main.js — WordPress версія БЕРЕЗА
   Тікер, форма підписки через AJAX, пошук
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {
  initTicker();
  initSubscribeForm();
  initSearch();
  initNavToggle();
});

// ── Мобільне меню ─────────────────────────────────────────────────────────────
function initNavToggle() {
  const toggle = document.querySelector('.js-nav-toggle');
  const menu   = document.querySelector('.nav-inner ul');
  if (!toggle || !menu) return;

  toggle.addEventListener('click', () => {
    const isOpen = menu.classList.toggle('is-open');
    toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
  });

  menu.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', () => {
      menu.classList.remove('is-open');
      toggle.setAttribute('aria-expanded', 'false');
    });
  });
}

// ── Тікер ────────────────────────────────────────────────────────────────────
function initTicker() {
  const track = document.querySelector('.ticker-track');
  if (!track) return;
  // Дублюємо вміст для безперервного прокручування
  track.innerHTML += track.innerHTML;
}

// ── Форма підписки через WordPress AJAX ──────────────────────────────────────
function initSubscribeForm() {
  const form = document.querySelector('.js-subscribe-form');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const emailInput = form.querySelector('input[type="email"]');
    const btn        = form.querySelector('button[type="submit"]');
    const msg        = form.closest('div').querySelector('.sub-message');
    const email      = emailInput.value.trim();

    if (!email) return;

    btn.textContent = '…';
    btn.disabled    = true;

    // Беремо nonce з wp_nonce_field або з berezaAjax
    const nonce = (typeof berezaAjax !== 'undefined') ? berezaAjax.nonce : '';
    const ajaxUrl = (typeof berezaAjax !== 'undefined') ? berezaAjax.url : '/wp-admin/admin-ajax.php';

    try {
      const body = new URLSearchParams({ action: 'bereza_subscribe', email, nonce });
      const res  = await fetch(ajaxUrl, { method: 'POST', body });
      const json = await res.json();

      if (json.success) {
        btn.textContent   = 'готово ✓';
        emailInput.value  = '';
        if (msg) { msg.textContent = json.data.message || 'Дякуємо!'; msg.style.display = 'block'; }
      } else {
        btn.textContent = 'Підписатися';
        btn.disabled    = false;
        if (msg) { msg.textContent = json.data.message || 'Помилка. Спробуйте ще.'; msg.style.display = 'block'; }
      }
    } catch {
      btn.textContent = 'Підписатися';
      btn.disabled    = false;
    }
  });
}

// ── Пошук (заглушка — підключіть WordPress Search або Algolia) ────────────────
function initSearch() {
  const searchBtn = document.querySelector('.js-search-toggle');
  if (!searchBtn) return;

  searchBtn.addEventListener('click', () => {
    const existing = document.querySelector('.bereza-search-overlay');
    if (existing) { existing.remove(); return; }

    const overlay = document.createElement('div');
    overlay.className = 'bereza-search-overlay';
    overlay.style.cssText = `
      position: fixed; inset: 0; z-index: 100;
      background: rgba(14,14,12,0.96);
      display: flex; align-items: center; justify-content: center;
    `;
    overlay.innerHTML = `
      <form method="get" action="${window.location.origin}/" style="width:min(600px,90vw)">
        <input
          type="search"
          name="s"
          autofocus
          placeholder="Пошук по сайту…"
          style="width:100%; background:transparent; border:0; border-bottom:2px solid var(--accent);
                 padding:16px 0; font-family:var(--f-display); font-size:32px; color:var(--ink); outline:none;"
        >
      </form>
      <button style="position:absolute; top:24px; right:32px; color:var(--ink-dim); font-size:28px;"
              aria-label="Закрити">✕</button>
    `;
    overlay.querySelector('button').addEventListener('click', () => overlay.remove());
    overlay.addEventListener('click', (e) => { if (e.target === overlay) overlay.remove(); });
    document.body.appendChild(overlay);
  });
}
