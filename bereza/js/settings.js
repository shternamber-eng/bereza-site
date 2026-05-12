/* settings.js — оновлює мастхед і SEO з data/settings.json */
(async function () {
  const isSubpage = window.location.pathname.replace(/\\/g, '/').includes('/pages/');
  const url = (isSubpage ? '../' : '') + 'data/settings.json';

  let s;
  try {
    const res = await fetch(url, { cache: 'no-cache' });
    if (!res.ok) return;
    s = await res.json();
  } catch (e) { return; }

  if (s.masthead) {
    const m = s.masthead;
    const metaL = document.querySelector('.meta-l');
    if (metaL) {
      metaL.innerHTML =
        `№ ${esc(m.issueNumber)} · ${esc(m.dayOfWeek)}<br><strong>${esc(m.date)}</strong>`;
    }
    const metaR = document.querySelector('.meta-r');
    if (metaR) {
      metaR.innerHTML =
        `${esc(m.city)} · <strong>${esc(m.temperature)}</strong><br>Тираж · ${esc(m.circulation)} читачів`;
    }
  }

  const path = window.location.pathname.replace(/\\/g, '/');
  if (s.seo) {
    if (path.endsWith('about.html') && s.seo.aboutTitle) {
      document.title = s.seo.aboutTitle;
    } else if (!path.includes('/pages/') && s.seo.homeTitle) {
      document.title = s.seo.homeTitle;
      const desc = document.querySelector('meta[name="description"]');
      if (desc && s.seo.homeDescription) desc.setAttribute('content', s.seo.homeDescription);
    }
  }

  function esc(v) {
    return v == null ? '' : String(v)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
  }
})();
