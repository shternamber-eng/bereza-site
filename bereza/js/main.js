/* ============================================
   main.js — точка входу
   Завантажує JSON-дані і викликає рендер.
   Якщо fetch недоступний (file://), використовує
   вбудований об'єкт window.__BEREZA_DATA__ як фолбек.
   ============================================ */

(async function init() {
  const ready = (cb) =>
    document.readyState === 'loading'
      ? document.addEventListener('DOMContentLoaded', cb)
      : cb();

  ready(async () => {
    let data = null;

    // 1) Спроба завантажити JSON через fetch (працює на http/https)
    try {
      const res = await fetch('data/content.json', { cache: 'no-cache' });
      if (res.ok) data = await res.json();
    } catch (e) {
      // Тихий збій — підемо у фолбек
    }

    // 2) Фолбек на вбудовані дані (для відкриття index.html напряму з диска)
    if (!data && window.__BEREZA_DATA__) {
      data = window.__BEREZA_DATA__;
    }

    if (data && window.BerezaRender) {
      window.BerezaRender.hydrate(data);
    }

    // Маленькі покращення UX, не залежать від даних
    setupSmoothNav();
  });
})();

function setupSmoothNav() {
  // Закриття/розкриття навігації може бути додано тут пізніше.
  // Підкреслення активного пункту вже задано класом .active у HTML.
}
