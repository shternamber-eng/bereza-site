/* article.js — завантажує Markdown-статтю за параметром ?slug= */
(async function () {
  const slug = new URLSearchParams(window.location.search).get('slug');
  if (!slug) return; // немає slug → показуємо статичний fallback

  let md;
  try {
    const res = await fetch('../content/articles/' + encodeURIComponent(slug) + '.md',
      { cache: 'no-cache' });
    if (!res.ok) return;
    md = await res.text();
  } catch (e) { return; }

  const { front, body } = parseFrontmatter(md);

  // SEO
  if (front.title) document.title = front.title + ' — БЕРЕЗА';

  // Тег категорії
  const tagEl = document.querySelector('.article-hero .tag');
  if (tagEl && front.category) {
    tagEl.textContent = front.category;
    tagEl.className = 'tag' + (front.urgent ? ' urgent' : '');
  }

  // Крихти (breadcrumbs)
  const crumbCat = document.querySelector('.crumbs a:last-child');
  if (crumbCat && front.category) crumbCat.textContent = front.category;

  const h1 = document.querySelector('.article-hero h1');
  if (h1 && front.title) h1.textContent = front.title;

  const lede = document.querySelector('.article-hero .lede');
  if (lede && front.lede) lede.textContent = front.lede;

  const byline = document.querySelector('.article-hero .byline');
  if (byline) {
    byline.innerHTML =
      `<span>Автор: <strong>${esc(front.author || 'Б. Береза')}</strong></span>` +
      `<span class="dot">●</span>` +
      `<span>читати ${esc(front.readTime || '')}</span>` +
      `<span class="dot">●</span>` +
      `<span>${esc(front.date || '')}</span>`;
  }

  // Бокові факти
  if (front.sidebarFacts) {
    const aside = document.querySelector('.article-aside[aria-label="Метадані"]');
    if (aside) {
      aside.innerHTML = '<span class="label">У цьому матеріалі</span>' +
        front.sidebarFacts.map(s => {
          const [num, label] = s.split(' · ');
          return `<div class="item"><strong>${esc(num)}</strong> ${esc(label || '')}</div>`;
        }).join('');
    }
  }

  // Тіло статті
  const content = document.querySelector('.content');
  if (content && body && window.marked) {
    content.innerHTML = marked.parse(body);
  }

  function esc(v) {
    return v == null ? '' : String(v)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
  }
})();

function parseFrontmatter(text) {
  if (!text.startsWith('---')) return { front: {}, body: text };
  const end = text.indexOf('\n---', 3);
  if (end === -1) return { front: {}, body: text };
  const yamlStr = text.slice(3, end).trim();
  const body = text.slice(end + 4).trim();

  const front = {};
  const lines = yamlStr.split('\n');
  let i = 0;
  while (i < lines.length) {
    const line = lines[i];
    const colon = line.indexOf(':');
    if (colon === -1) { i++; continue; }
    const key = line.slice(0, colon).trim();
    const raw = line.slice(colon + 1).trim();

    if (raw === '') {
      // Список (- item)
      const items = [];
      i++;
      while (i < lines.length && /^\s*-/.test(lines[i])) {
        items.push(lines[i].replace(/^\s*-\s*/, '').replace(/^["']|["']$/g, ''));
        i++;
      }
      front[key] = items;
      continue;
    }

    if (raw === 'true') front[key] = true;
    else if (raw === 'false') front[key] = false;
    else front[key] = raw.replace(/^["']|["']$/g, '');
    i++;
  }

  return { front, body };
}
