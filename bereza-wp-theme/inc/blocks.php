<?php
defined('ABSPATH') || exit;

add_action('init',                  'bereza_register_blocks');
add_action('enqueue_block_editor_assets', 'bereza_enqueue_block_editor_assets');

// ── Регистрация нативных Gutenberg-блоков ─────────────────────────────────────
function bereza_register_blocks(): void {
    // Блок: Цитата
    register_block_type('bereza/quote', [
        'api_version'     => 2,
        'title'           => 'БЕРЕЗА: Цитата',
        'category'        => 'formatting',
        'icon'            => 'format-quote',
        'description'     => 'Велика редакційна цитата з підписом.',
        'keywords'        => ['цитата', 'quote'],
        'supports'        => ['align' => false, 'html' => false],
        'attributes'      => [
            'text'   => ['type' => 'string', 'default' => ''],
            'source' => ['type' => 'string', 'default' => ''],
        ],
        'render_callback' => 'bereza_block_render_quote',
    ]);

    // Блок: Hero-карточка
    register_block_type('bereza/hero-card', [
        'api_version'     => 2,
        'title'           => 'БЕРЕЗА: Головна карточка',
        'category'        => 'layout',
        'icon'            => 'admin-home',
        'description'     => 'Hero-карточка матеріалу з тегом, заголовком і лідом.',
        'supports'        => ['align' => ['full', 'wide'], 'html' => false],
        'attributes'      => [
            'tag'       => ['type' => 'string', 'default' => 'матеріал'],
            'urgent'    => ['type' => 'boolean', 'default' => false],
            'title'     => ['type' => 'string', 'default' => ''],
            'lede'      => ['type' => 'string', 'default' => ''],
            'url'       => ['type' => 'string', 'default' => ''],
            'author'    => ['type' => 'string', 'default' => ''],
            'read_time' => ['type' => 'string', 'default' => ''],
        ],
        'render_callback' => 'bereza_block_render_hero_card',
    ]);

    // Блок: Форма підписки
    register_block_type('bereza/subscribe', [
        'api_version'     => 2,
        'title'           => 'БЕРЕЗА: Форма підписки',
        'category'        => 'widgets',
        'icon'            => 'email',
        'description'     => 'Секція підписки на розсилку з каналами.',
        'supports'        => ['align' => ['full'], 'html' => false],
        'attributes'      => [],
        'render_callback' => 'bereza_block_render_subscribe',
    ]);
}

// ── Render callbacks ──────────────────────────────────────────────────────────
function bereza_block_render_quote(array $attrs): string {
    $text   = wp_kses($attrs['text']   ?? '', ['em' => []]);
    $source = esc_html($attrs['source'] ?? '');

    if (!$text) return '';

    ob_start();
    ?>
    <section class="quote-section">
      <blockquote>«<?php echo $text; ?>»</blockquote>
      <?php if ($source): ?><div class="qmeta"><?php echo $source; ?></div><?php endif; ?>
    </section>
    <?php
    return ob_get_clean();
}

function bereza_block_render_hero_card(array $attrs): string {
    $tag       = esc_html($attrs['tag']       ?? 'матеріал');
    $urgent    = !empty($attrs['urgent']);
    $title     = esc_html($attrs['title']     ?? '');
    $lede      = esc_html($attrs['lede']      ?? '');
    $url       = esc_url($attrs['url']        ?? '#');
    $author    = esc_html($attrs['author']    ?? '');
    $read_time = esc_html($attrs['read_time'] ?? '');

    if (!$title) return '';

    ob_start();
    ?>
    <article class="hero-main" style="min-height:420px">
      <span class="monogram" aria-hidden="true">Б</span>
      <span class="tag<?php echo $urgent ? ' urgent' : ''; ?>"><?php echo $tag; ?></span>
      <h2><a href="<?php echo $url; ?>"><?php echo $title; ?></a></h2>
      <?php if ($lede): ?><p class="lede"><?php echo $lede; ?></p><?php endif; ?>
      <div class="byline">
        <?php if ($author): ?>
          <span>Автор: <strong><?php echo $author; ?></strong></span>
          <span class="dot">●</span>
        <?php endif; ?>
        <?php if ($read_time): ?>
          <span>читати <?php echo $read_time; ?></span>
        <?php endif; ?>
      </div>
    </article>
    <?php
    return ob_get_clean();
}

function bereza_block_render_subscribe(): string {
    ob_start();
    get_template_part('template-parts/subscribe-section');
    return ob_get_clean();
}

// ── JS для редактора блоков ────────────────────────────────────────────────────
function bereza_enqueue_block_editor_assets(): void {
    $ver = wp_get_theme()->get('Version');
    $uri = get_template_directory_uri() . '/assets';

    // Стили блоков в редакторе
    wp_enqueue_style('bereza-editor-blocks', "$uri/css/editor-blocks.css", ['wp-edit-blocks'], $ver);

    // JS регистрации блоков (использует wp.blocks, wp.element и др. глобалы)
    wp_enqueue_script(
        'bereza-blocks-editor',
        "$uri/js/admin-blocks.js",
        ['wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render'],
        $ver,
        true
    );
}
