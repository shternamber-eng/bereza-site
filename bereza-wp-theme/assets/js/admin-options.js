/* Options page — repeaters for ticker and channels */
(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {

    // ── Добавление строк ─────────────────────────────────────────────────────
    document.querySelectorAll('.bereza-add-row').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var target   = btn.dataset.target;
        var repeater = btn.previousElementSibling;
        // Ищем ближайший .bereza-repeater перед кнопкой
        while (repeater && !repeater.classList.contains('bereza-repeater')) {
          repeater = repeater.previousElementSibling;
        }
        if (!repeater) return;

        if (target === 'ticker') {
          var row = document.createElement('div');
          row.className = 'bereza-repeater-row';
          var inp = document.createElement('input');
          inp.type = 'text'; inp.name = 'bereza_ticker_row[]';
          inp.placeholder = 'Текст рядка'; inp.style.width = '85%';
          var btn2 = removeBtn();
          row.appendChild(inp); row.appendChild(btn2);
          repeater.appendChild(row);
        }

        if (target === 'channels') {
          var row2 = document.createElement('div');
          row2.className = 'bereza-repeater-row bereza-channel-row';
          var fields = [
            { name: 'bereza_ch_icon[]',  placeholder: 'Іконка (YT)', width: '60px',  type: 'text' },
            { name: 'bereza_ch_name[]',  placeholder: 'Назва',       width: '120px', type: 'text' },
            { name: 'bereza_ch_count[]', placeholder: '218K',         width: '70px',  type: 'text' },
            { name: 'bereza_ch_url[]',   placeholder: 'https://…',   width: '220px', type: 'url'  },
          ];
          fields.forEach(function (f) {
            var el = document.createElement('input');
            el.type = f.type; el.name = f.name;
            el.placeholder = f.placeholder; el.style.width = f.width;
            row2.appendChild(el);
          });
          row2.appendChild(removeBtn());
          repeater.appendChild(row2);
        }
      });
    });

    // ── Удаление строк ───────────────────────────────────────────────────────
    document.addEventListener('click', function (e) {
      if (e.target.classList.contains('bereza-remove-row')) {
        e.target.closest('.bereza-repeater-row').remove();
      }
    });

    // ── Перед отправкой формы — собрать ticker и channels в JSON ─────────────
    var form = document.querySelector('form');
    if (!form) return;

    form.addEventListener('submit', function () {
      // Тикер
      var tickerJson = document.getElementById('bereza_ticker_items_json');
      if (tickerJson) {
        var rows = form.querySelectorAll('[name="bereza_ticker_row[]"]');
        var items = [];
        rows.forEach(function (r) { if (r.value.trim()) items.push({ text: r.value.trim() }); });
        tickerJson.value = JSON.stringify(items);
        // Отключаем оригинальные поля, чтобы не дублировались
        rows.forEach(function (r) { r.disabled = true; });
      }

      // Каналы
      var chJson = document.getElementById('bereza_channels_json');
      if (chJson) {
        var icons  = form.querySelectorAll('[name="bereza_ch_icon[]"]');
        var names  = form.querySelectorAll('[name="bereza_ch_name[]"]');
        var counts = form.querySelectorAll('[name="bereza_ch_count[]"]');
        var urls   = form.querySelectorAll('[name="bereza_ch_url[]"]');
        var chs = [];
        icons.forEach(function (_, i) {
          if (!icons[i].value && !names[i].value) return;
          chs.push({
            icon:  icons[i].value,
            name:  names[i] ? names[i].value : '',
            count: counts[i] ? counts[i].value : '',
            url:   urls[i]   ? urls[i].value   : '',
          });
        });
        chJson.value = JSON.stringify(chs);
        icons.forEach(function (el) { el.disabled = true; });
        names.forEach(function (el) { el.disabled = true; });
        counts.forEach(function (el) { el.disabled = true; });
        urls.forEach(function (el) { el.disabled = true; });
      }
    });
  });

  function removeBtn() {
    var btn = document.createElement('button');
    btn.type = 'button'; btn.className = 'bereza-remove-row button'; btn.textContent = '✕';
    return btn;
  }
})();
