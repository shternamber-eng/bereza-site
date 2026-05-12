/* about.js — завантажує data/about.json і гідратує сторінку "Про автора" */
(async function () {
  let data;
  try {
    const res = await fetch('../data/about.json', { cache: 'no-cache' });
    if (!res.ok) return;
    data = await res.json();
  } catch (e) { return; }

  if (data.bodyMarkdown) {
    const el = document.querySelector('[data-about-body]');
    if (el && window.marked) el.innerHTML = marked.parse(data.bodyMarkdown);
  }

  if (data.facts) {
    const el = document.querySelector('[data-about-facts]');
    if (el) {
      el.innerHTML = data.facts.map(f => `
        <div class="about-fact">
          <div class="num">${esc(f.num)}</div>
          <div class="label">${esc(f.label)}</div>
        </div>`).join('');
    }
  }

  if (data.quote) {
    const el = document.querySelector('[data-about-quote]');
    if (el) {
      el.innerHTML = `
        <blockquote>${esc(data.quote.text)}</blockquote>
        <div class="qmeta">${esc(data.quote.source)}</div>`;
    }
  }

  function esc(v) {
    return v == null ? '' : String(v)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
  }
})();
