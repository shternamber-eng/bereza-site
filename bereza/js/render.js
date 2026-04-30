/* ============================================
   render.js — наповнення сторінки з JSON
   ============================================ */

// Безпечний escape тексту, щоб не зламати розмітку випадковими символами
function esc(str) {
  if (str == null) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;');
}

// Деякі поля (наприклад, цитата) свідомо містять <em> — для них дозволяємо
// тільки <em>, інше екрануємо. Простий whitelist.
function escAllowEm(str) {
  if (str == null) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/<(?!\/?em>)/g, '&lt;');
}

function renderTicker(items) {
  const track = document.querySelector('.ticker-track');
  if (!track) return;
  track.innerHTML = items.map(t => `<span>${esc(t)}</span>`).join('');
}

function renderHero(hero, side) {
  const main = document.querySelector('[data-hero-main]');
  if (main) {
    main.innerHTML = `
      <span class="monogram" aria-hidden="true">Б</span>
      <span class="tag${hero.tagUrgent ? ' urgent' : ''}">${esc(hero.tag)}</span>
      <h2><a href="${esc(hero.url)}">${esc(hero.title)}</a></h2>
      <p class="lede">${esc(hero.lede)}</p>
      <div class="byline">
        <span>Автор: <strong>${esc(hero.author)}</strong></span>
        <span class="dot">●</span>
        <span>читати ${esc(hero.readTime)}</span>
        <span class="dot">●</span>
        <span>${esc(hero.date)}</span>
      </div>
    `;
  }

  const sideEl = document.querySelector('[data-hero-side]');
  if (sideEl) {
    sideEl.innerHTML = side.map((it, i) => `
      <a class="item" href="${esc(it.url)}">
        <div class="row1">
          <span class="num">${String(i + 1).padStart(2, '0')}</span>
          <span class="cat">${esc(it.cat)}</span>
        </div>
        <h3>${esc(it.title)}</h3>
        <time>${esc(it.date)}</time>
      </a>
    `).join('');
  }
}

function renderColumns(items) {
  const grid = document.querySelector('[data-columns]');
  if (!grid) return;
  grid.innerHTML = items.map(c => `
    <article class="column-card">
      <div class="cat">${esc(c.cat)}</div>
      <h3>${esc(c.title)}</h3>
      <p>${esc(c.excerpt)}</p>
      <div class="meta">
        <span>${esc(c.date)}</span>
        <span class="read">читати →</span>
      </div>
    </article>
  `).join('');
}

function renderVideos(items) {
  const grid = document.querySelector('[data-videos]');
  if (!grid) return;
  grid.innerHTML = items.map(v => `
    <article class="video-card${v.size === 'large' ? ' large' : ''}">
      <div class="video-thumb">
        <span class="duration">${esc(v.duration)}</span>
      </div>
      <h3>${esc(v.title)}</h3>
      <div class="vmeta">${esc(v.meta)}</div>
    </article>
  `).join('');
}

function renderQuote(quote) {
  const el = document.querySelector('[data-quote]');
  if (!el) return;
  el.innerHTML = `
    <blockquote>«${escAllowEm(quote.text)}»</blockquote>
    <div class="qmeta">${esc(quote.source)}</div>
  `;
}

function renderLatest(items) {
  const grid = document.querySelector('[data-latest]');
  if (!grid) return;
  grid.innerHTML = items.map(it => `
    <article class="latest-card">
      <div class="top">
        <span class="cat">${esc(it.cat)}</span>
        <time>${esc(it.date)}</time>
      </div>
      <h3>${esc(it.title)}</h3>
      <span class="arrow">${esc(it.action)}</span>
    </article>
  `).join('');
}

function renderChannels(items) {
  const list = document.querySelector('[data-channels]');
  if (!list) return;
  list.innerHTML = items.map(ch => `
    <a class="channel" href="#">
      <div class="left">
        <div class="icon">${esc(ch.icon)}</div>
        <div class="name">${esc(ch.name)}</div>
      </div>
      <div class="count">${esc(ch.count)}</div>
    </a>
  `).join('');
}

// Головна функція: бере дані і наповнює всі секції головної
function hydrate(data) {
  if (!data) return;
  if (data.ticker) renderTicker(data.ticker);
  if (data.hero && data.heroSide) renderHero(data.hero, data.heroSide);
  if (data.columns) renderColumns(data.columns);
  if (data.videos) renderVideos(data.videos);
  if (data.quote) renderQuote(data.quote);
  if (data.latest) renderLatest(data.latest);
  if (data.channels) renderChannels(data.channels);
}

// Експорт у глобал для main.js
window.BerezaRender = { hydrate };
