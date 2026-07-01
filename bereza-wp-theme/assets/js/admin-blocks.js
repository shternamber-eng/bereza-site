/* Gutenberg blocks editor — native WordPress globals, no build step */
(function () {
  'use strict';

  var registerBlockType  = wp.blocks.registerBlockType;
  var el                 = wp.element.createElement;
  var InspectorControls  = wp.blockEditor.InspectorControls;
  var useBlockProps      = wp.blockEditor.useBlockProps;
  var PanelBody          = wp.components.PanelBody;
  var TextControl        = wp.components.TextControl;
  var TextareaControl    = wp.components.TextareaControl;
  var ToggleControl      = wp.components.ToggleControl;
  var ServerSideRender   = wp.serverSideRender;

  /* ── Блок: Цитата ──────────────────────────────────────────────────────── */
  registerBlockType('bereza/quote', {
    edit: function (props) {
      var attrs = props.attributes;
      var set   = props.setAttributes;

      return el(
        'div', useBlockProps({ className: 'bereza-block-preview' }),
        el(InspectorControls, null,
          el(PanelBody, { title: 'Цитата', initialOpen: true },
            el(TextareaControl, {
              label:    'Текст цитати',
              help:     'Можна використовувати <em> для підсвічування.',
              value:    attrs.text,
              onChange: function (v) { set({ text: v }); },
              rows:     4,
            }),
            el(TextControl, {
              label:    'Підпис',
              value:    attrs.source,
              onChange: function (v) { set({ source: v }); },
            })
          )
        ),
        el(ServerSideRender, { block: 'bereza/quote', attributes: attrs })
      );
    },
    save: function () { return null; },
  });

  /* ── Блок: Hero-карточка ───────────────────────────────────────────────── */
  registerBlockType('bereza/hero-card', {
    edit: function (props) {
      var attrs = props.attributes;
      var set   = props.setAttributes;

      return el(
        'div', useBlockProps({ className: 'bereza-block-preview' }),
        el(InspectorControls, null,
          el(PanelBody, { title: 'Hero-карточка', initialOpen: true },
            el(TextControl, {
              label: 'Рубрика (тег)',
              value: attrs.tag,
              onChange: function (v) { set({ tag: v }); },
            }),
            el(ToggleControl, {
              label:    'Терміново (червоний тег)',
              checked:  attrs.urgent,
              onChange: function (v) { set({ urgent: v }); },
            }),
            el(TextControl, {
              label: 'Заголовок',
              value: attrs.title,
              onChange: function (v) { set({ title: v }); },
            }),
            el(TextareaControl, {
              label: 'Лід',
              value: attrs.lede,
              rows:  3,
              onChange: function (v) { set({ lede: v }); },
            }),
            el(TextControl, {
              label: 'Посилання (URL)',
              type:  'url',
              value: attrs.url,
              onChange: function (v) { set({ url: v }); },
            }),
            el(TextControl, {
              label: 'Автор',
              value: attrs.author,
              onChange: function (v) { set({ author: v }); },
            }),
            el(TextControl, {
              label:       'Час читання',
              value:       attrs.read_time,
              placeholder: '14 хв',
              onChange: function (v) { set({ read_time: v }); },
            })
          )
        ),
        el(ServerSideRender, { block: 'bereza/hero-card', attributes: attrs })
      );
    },
    save: function () { return null; },
  });

  /* ── Блок: Форма підписки ──────────────────────────────────────────────── */
  registerBlockType('bereza/subscribe', {
    edit: function (props) {
      return el(
        'div', useBlockProps({ className: 'bereza-block-preview' }),
        el(ServerSideRender, { block: 'bereza/subscribe', attributes: {} })
      );
    },
    save: function () { return null; },
  });
})();
