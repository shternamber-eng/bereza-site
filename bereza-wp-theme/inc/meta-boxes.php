<?php
defined('ABSPATH') || exit;

add_action('add_meta_boxes',        'bereza_add_meta_boxes');
add_action('save_post',             'bereza_save_post_meta', 10, 2);
add_action('admin_enqueue_scripts', 'bereza_enqueue_admin_assets');

// ── Регистрация meta boxes ─────────────────────────────────────────────────────
function bereza_add_meta_boxes(): void {
    $article_types = ['post', 'kolumna', 'rozsliduvannya', 'podkast'];

    add_meta_box('bereza_article', 'Параметри матеріалу',    'bereza_render_article_meta', $article_types, 'normal', 'high');
    add_meta_box('bereza_video',   'Параметри відео',         'bereza_render_video_meta',   'video',          'normal', 'high');
    add_meta_box('bereza_about',   'Сторінка «Про автора»',   'bereza_render_about_meta',   'page',           'normal', 'high');
}

// ── HTML для полей статьи ──────────────────────────────────────────────────────
function bereza_render_article_meta(WP_Post $post): void {
    wp_nonce_field('bereza_save_meta', 'bereza_meta_nonce');

    $is_hero   = get_post_meta($post->ID, 'bereza_is_hero',       true);
    $is_urgent = get_post_meta($post->ID, 'bereza_is_urgent',     true);
    $cat       = get_post_meta($post->ID, 'bereza_category_label',true);
    $lede      = get_post_meta($post->ID, 'bereza_lede',          true);
    $read_time = get_post_meta($post->ID, 'bereza_read_time',     true);
    $sources   = json_decode(get_post_meta($post->ID, 'bereza_sources', true) ?: '[]', true);
    ?>
    <div class="bereza-meta">

      <label class="bereza-check">
        <input type="checkbox" name="bereza_is_hero" value="1" <?php checked($is_hero, '1'); ?>>
        <span>Головний матеріал <small>(відображається у великому блоці на головній)</small></span>
      </label>

      <label class="bereza-check">
        <input type="checkbox" name="bereza_is_urgent" value="1" <?php checked($is_urgent, '1'); ?>>
        <span>Терміново <small>(тег стає червоним)</small></span>
      </label>

      <div class="bereza-row">
        <label for="bereza_category_label">Рубрика (тег)</label>
        <input type="text" id="bereza_category_label" name="bereza_category_label"
               value="<?php echo esc_attr($cat); ?>" placeholder="наприклад: розслідування">
      </div>

      <div class="bereza-row">
        <label for="bereza_lede">Лід / підзаголовок</label>
        <textarea id="bereza_lede" name="bereza_lede" rows="3"
                  placeholder="Короткий анонс — 2–3 речення"><?php echo esc_textarea($lede); ?></textarea>
      </div>

      <div class="bereza-row">
        <label for="bereza_read_time">Час читання</label>
        <input type="text" id="bereza_read_time" name="bereza_read_time"
               value="<?php echo esc_attr($read_time); ?>" placeholder="14 хв">
      </div>

      <div class="bereza-row">
        <label>Джерела</label>
        <div class="bereza-repeater" data-repeater="bereza_sources">
          <?php foreach ($sources as $s): ?>
            <div class="bereza-repeater-row">
              <input type="text"  name="bereza_sources[][label]" value="<?php echo esc_attr($s['label'] ?? ''); ?>" placeholder="Назва джерела">
              <input type="url"   name="bereza_sources[][url]"   value="<?php echo esc_attr($s['url']   ?? ''); ?>" placeholder="https://…">
              <button type="button" class="bereza-remove-row button">✕</button>
            </div>
          <?php endforeach; ?>
        </div>
        <button type="button" class="bereza-add-row button" data-target="bereza_sources">+ Додати джерело</button>
      </div>

    </div>
    <?php
}

// ── HTML для полей видео ───────────────────────────────────────────────────────
function bereza_render_video_meta(WP_Post $post): void {
    wp_nonce_field('bereza_save_meta', 'bereza_meta_nonce');

    $yt_url    = get_post_meta($post->ID, 'bereza_youtube_url', true);
    $duration  = get_post_meta($post->ID, 'bereza_duration',   true);
    $view_meta = get_post_meta($post->ID, 'bereza_view_meta',  true);
    ?>
    <div class="bereza-meta">

      <div class="bereza-row">
        <label for="bereza_youtube_url">YouTube URL</label>
        <input type="url" id="bereza_youtube_url" name="bereza_youtube_url"
               value="<?php echo esc_attr($yt_url); ?>" placeholder="https://youtu.be/…">
      </div>

      <div class="bereza-row">
        <label for="bereza_duration">Тривалість</label>
        <input type="text" id="bereza_duration" name="bereza_duration"
               value="<?php echo esc_attr($duration); ?>" placeholder="42:18">
      </div>

      <div class="bereza-row">
        <label for="bereza_view_meta">Перегляди (текст)</label>
        <input type="text" id="bereza_view_meta" name="bereza_view_meta"
               value="<?php echo esc_attr($view_meta); ?>" placeholder="147 тис. переглядів · 3 дні тому">
      </div>

    </div>
    <?php
}

// ── HTML для страницы «Про автора» ────────────────────────────────────────────
function bereza_render_about_meta(WP_Post $post): void {
    if (get_page_template_slug($post->ID) !== 'page-pro-avtora.php') {
        echo '<p style="color:#999;margin:8px 0;">Ці поля доступні лише для шаблону <strong>«Про автора»</strong>.</p>';
        return;
    }

    wp_nonce_field('bereza_save_meta', 'bereza_meta_nonce');

    $bio   = json_decode(get_post_meta($post->ID, 'bereza_bio_paragraphs', true) ?: '[]', true);
    $facts = json_decode(get_post_meta($post->ID, 'bereza_about_facts',    true) ?: '[]', true);
    $tl    = json_decode(get_post_meta($post->ID, 'bereza_timeline',       true) ?: '[]', true);
    ?>
    <div class="bereza-meta">

      <div class="bereza-row">
        <label>Біографія (абзаци)</label>
        <div class="bereza-repeater" data-repeater="bereza_bio_paragraphs">
          <?php foreach ($bio as $p): ?>
            <div class="bereza-repeater-row">
              <textarea name="bereza_bio_paragraphs[][text]" rows="3" placeholder="Текст абзацу"><?php echo esc_textarea($p['text'] ?? ''); ?></textarea>
              <button type="button" class="bereza-remove-row button">✕</button>
            </div>
          <?php endforeach; ?>
        </div>
        <button type="button" class="bereza-add-row button" data-target="bereza_bio_paragraphs">+ Додати абзац</button>
      </div>

      <div class="bereza-row">
        <label>Цифри / факти</label>
        <div class="bereza-repeater" data-repeater="bereza_about_facts">
          <?php foreach ($facts as $f): ?>
            <div class="bereza-repeater-row">
              <input type="text" name="bereza_about_facts[][num]"   value="<?php echo esc_attr($f['num']   ?? ''); ?>" placeholder="184 700">
              <input type="text" name="bereza_about_facts[][label]" value="<?php echo esc_attr($f['label'] ?? ''); ?>" placeholder="читачів щомісяця">
              <button type="button" class="bereza-remove-row button">✕</button>
            </div>
          <?php endforeach; ?>
        </div>
        <button type="button" class="bereza-add-row button" data-target="bereza_about_facts">+ Додати факт</button>
      </div>

      <div class="bereza-row">
        <label>Хронологія</label>
        <div class="bereza-repeater" data-repeater="bereza_timeline">
          <?php foreach ($tl as $item): ?>
            <div class="bereza-repeater-row bereza-repeater-row--tall">
              <input type="text"     name="bereza_timeline[][year]"  value="<?php echo esc_attr($item['year']  ?? ''); ?>" placeholder="2020">
              <input type="text"     name="bereza_timeline[][title]" value="<?php echo esc_attr($item['title'] ?? ''); ?>" placeholder="Назва події">
              <textarea              name="bereza_timeline[][desc]"  rows="2" placeholder="Короткий опис"><?php echo esc_textarea($item['desc'] ?? ''); ?></textarea>
              <button type="button" class="bereza-remove-row button">✕</button>
            </div>
          <?php endforeach; ?>
        </div>
        <button type="button" class="bereza-add-row button" data-target="bereza_timeline">+ Додати подію</button>
      </div>

    </div>
    <?php
}

// ── Сохранение meta полей ──────────────────────────────────────────────────────
function bereza_save_post_meta(int $post_id, WP_Post $post): void {
    if (!isset($_POST['bereza_meta_nonce'])) return;
    if (!wp_verify_nonce($_POST['bereza_meta_nonce'], 'bereza_save_meta')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    if (wp_is_post_revision($post_id)) return;

    // Чекбоксы
    foreach (['is_hero', 'is_urgent'] as $f) {
        update_post_meta($post_id, "bereza_$f", !empty($_POST["bereza_$f"]) ? '1' : '');
    }

    // Текстовые поля
    foreach (['category_label', 'read_time', 'duration', 'view_meta'] as $f) {
        if (array_key_exists("bereza_$f", $_POST)) {
            update_post_meta($post_id, "bereza_$f", sanitize_text_field(wp_unslash($_POST["bereza_$f"])));
        }
    }

    // Текстовые области
    if (array_key_exists('bereza_lede', $_POST)) {
        update_post_meta($post_id, 'bereza_lede', sanitize_textarea_field(wp_unslash($_POST['bereza_lede'])));
    }

    // URL
    if (array_key_exists('bereza_youtube_url', $_POST)) {
        update_post_meta($post_id, 'bereza_youtube_url', esc_url_raw(wp_unslash($_POST['bereza_youtube_url'])));
    }

    // JSON-репитеры
    $json_repeaters = [
        'bereza_sources'        => fn($r) => ['label' => sanitize_text_field($r['label'] ?? ''), 'url' => esc_url_raw($r['url'] ?? '')],
        'bereza_bio_paragraphs' => fn($r) => ['text'  => sanitize_textarea_field($r['text'] ?? '')],
        'bereza_about_facts'    => fn($r) => ['num'   => sanitize_text_field($r['num'] ?? ''), 'label' => sanitize_text_field($r['label'] ?? '')],
        'bereza_timeline'       => fn($r) => ['year'  => sanitize_text_field($r['year'] ?? ''), 'title' => sanitize_text_field($r['title'] ?? ''), 'desc' => sanitize_textarea_field($r['desc'] ?? '')],
    ];

    foreach ($json_repeaters as $meta_key => $sanitizer) {
        if (!array_key_exists($meta_key, $_POST)) continue;
        $rows = array_filter(array_map($sanitizer, (array) wp_unslash($_POST[$meta_key])));
        update_post_meta($post_id, $meta_key, wp_json_encode(array_values($rows)));
    }
}

// ── Подключение Admin CSS/JS ───────────────────────────────────────────────────
function bereza_enqueue_admin_assets(string $hook): void {
    if (!in_array($hook, ['post.php', 'post-new.php'], true)) return;

    $ver = wp_get_theme()->get('Version');
    $uri = get_template_directory_uri() . '/assets';

    wp_enqueue_style( 'bereza-admin',      "$uri/css/admin.css",      [],                $ver);
    wp_enqueue_script('bereza-admin-meta', "$uri/js/admin-meta.js",   [],                $ver, true);
}
