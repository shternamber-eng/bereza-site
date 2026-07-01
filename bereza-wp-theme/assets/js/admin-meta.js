/* Admin meta boxes — repeater fields (vanilla JS, no jQuery) */
(function () {
  'use strict';

  // Шаблоны строк для каждого repeater
  var templates = {
    bereza_sources: function () {
      return row([
        inp('text', 'bereza_sources[][label]', '', 'Назва джерела'),
        inp('url',  'bereza_sources[][url]',   '', 'https://…'),
        removeBtn(),
      ]);
    },
    bereza_bio_paragraphs: function () {
      return row([
        ta('bereza_bio_paragraphs[][text]', 'Текст абзацу'),
        removeBtn(),
      ]);
    },
    bereza_about_facts: function () {
      return row([
        inp('text', 'bereza_about_facts[][num]',   '', '184 700'),
        inp('text', 'bereza_about_facts[][label]', '', 'читачів щомісяця'),
        removeBtn(),
      ]);
    },
    bereza_timeline: function () {
      var div = document.createElement('div');
      div.className = 'bereza-repeater-row bereza-repeater-row--tall';
      div.appendChild(inp('text', 'bereza_timeline[][year]',  '', '2020'));
      div.appendChild(inp('text', 'bereza_timeline[][title]', '', 'Назва події'));
      div.appendChild(ta('bereza_timeline[][desc]', 'Короткий опис'));
      div.appendChild(removeBtn());
      return div;
    },
  };

  document.addEventListener('DOMContentLoaded', function () {
    // Кнопки «Додати»
    document.querySelectorAll('.bereza-add-row').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var target  = btn.dataset.target;
        var repeater = btn.previousElementSibling;
        if (!repeater || !repeater.classList.contains('bereza-repeater')) return;
        var tmpl = templates[target];
        if (tmpl) repeater.appendChild(tmpl());
      });
    });

    // Делегирование — кнопки «✕»
    document.addEventListener('click', function (e) {
      if (e.target.classList.contains('bereza-remove-row')) {
        e.target.closest('.bereza-repeater-row').remove();
      }
    });
  });

  /* ─── helpers ─────────────────────────────────────────────────────────── */
  function inp(type, name, value, placeholder) {
    var el = document.createElement('input');
    el.type = type; el.name = name; el.value = value || '';
    if (placeholder) el.placeholder = placeholder;
    return el;
  }
  function ta(name, placeholder) {
    var el = document.createElement('textarea');
    el.name = name; el.rows = 3;
    if (placeholder) el.placeholder = placeholder;
    return el;
  }
  function removeBtn() {
    var btn = document.createElement('button');
    btn.type = 'button'; btn.className = 'bereza-remove-row button'; btn.textContent = '✕';
    return btn;
  }
  function row(children) {
    var div = document.createElement('div');
    div.className = 'bereza-repeater-row';
    children.forEach(function (c) { div.appendChild(c); });
    return div;
  }
})();
