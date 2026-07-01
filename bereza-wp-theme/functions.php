<?php
defined('ABSPATH') || exit;

require_once get_template_directory() . '/inc/cpt.php';
require_once get_template_directory() . '/inc/meta-boxes.php';
require_once get_template_directory() . '/inc/options-page.php';
require_once get_template_directory() . '/inc/blocks.php';
require_once get_template_directory() . '/inc/seo.php';

// ── Открываем поля темы для REST API (для импорта/публикации через wp-json) ───
add_action('init', function () {
    $types = ['post', 'kolumna', 'rozsliduvannya', 'podkast', 'video'];
    $fields = ['bereza_category_label', 'bereza_lede', 'bereza_read_time'];

    foreach ($types as $type) {
        foreach ($fields as $field) {
            register_post_meta($type, $field, [
                'type'          => 'string',
                'single'        => true,
                'show_in_rest'  => true,
                'auth_callback' => fn() => current_user_can('edit_posts'),
            ]);
        }
    }
});

// ── Поддержка функций WordPress ───────────────────────────────────────────────
add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'comment-form', 'gallery', 'caption']);
    add_theme_support('align-wide');
    add_theme_support('editor-styles');
    add_theme_support('wp-block-styles');

    register_nav_menus([
        'primary' => __('Головне меню', 'bereza'),
        'footer'  => __('Меню футера', 'bereza'),
    ]);
});

// ── Подключение стилей и скриптов ─────────────────────────────────────────────
add_action('wp_enqueue_scripts', function () {
    $ver = wp_get_theme()->get('Version');
    $dir = get_template_directory_uri() . '/assets';

    wp_enqueue_style('bereza-reset',      "$dir/css/reset.css",      [],                $ver);
    wp_enqueue_style('bereza-tokens',     "$dir/css/tokens.css",     ['bereza-reset'],  $ver);
    wp_enqueue_style('bereza-base',       "$dir/css/base.css",       ['bereza-tokens'], $ver);
    wp_enqueue_style('bereza-layout',     "$dir/css/layout.css",     ['bereza-base'],   $ver);
    wp_enqueue_style('bereza-components', "$dir/css/components.css", ['bereza-layout'], $ver);

    wp_enqueue_script('bereza-main', "$dir/js/main.js", [], $ver, true);
    wp_localize_script('bereza-main', 'berezaAjax', [
        'url'   => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('bereza_subscribe'),
    ]);
});

// ── AJAX: форма підписки ───────────────────────────────────────────────────────
add_action('wp_ajax_nopriv_bereza_subscribe', 'bereza_handle_subscribe');
add_action('wp_ajax_bereza_subscribe',        'bereza_handle_subscribe');

function bereza_handle_subscribe(): void {
    check_ajax_referer('bereza_subscribe', 'nonce');

    $email = sanitize_email(wp_unslash($_POST['email'] ?? ''));
    if (!is_email($email)) {
        wp_send_json_error(['message' => 'Невірний email']);
    }

    $api_key = defined('BEREZA_MAILERLITE_KEY')   ? BEREZA_MAILERLITE_KEY   : bereza_opt('mailerlite_key');
    $group   = defined('BEREZA_MAILERLITE_GROUP') ? BEREZA_MAILERLITE_GROUP : bereza_opt('mailerlite_group');

    if (!$api_key) {
        $existing = get_option('bereza_subscribers', []);
        if (!in_array($email, $existing, true)) {
            $existing[] = $email;
            update_option('bereza_subscribers', $existing, false);
        }
        wp_send_json_success(['message' => 'Дякуємо! Ви підписалися.']);
    }

    $response = wp_remote_post("https://api.mailerlite.com/api/v2/groups/{$group}/subscribers", [
        'headers' => ['X-MailerLite-ApiKey' => $api_key, 'Content-Type' => 'application/json'],
        'body'    => wp_json_encode(['email' => $email]),
        'timeout' => 10,
    ]);

    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) >= 400) {
        wp_send_json_error(['message' => 'Помилка підписки. Спробуйте ще раз.']);
    }

    wp_send_json_success(['message' => 'Дякуємо! Ви підписалися.']);
}

// ── Хелперы ───────────────────────────────────────────────────────────────────

/**
 * Читает мета-поле поста или глобальную опцию.
 * Замена get_field() из ACF — совместима со всеми шаблонами.
 *
 * @param string          $key     Имя поля без префикса «bereza_»
 * @param int|string|null $post_id ID поста или 'option' для глобальных настроек
 * @param mixed           $default Значение по умолчанию
 */
function bereza_field(string $key, $post_id = null, $default = '') {
    if ($post_id === 'option') {
        return bereza_opt($key, $default);
    }
    $id  = $post_id ?: (get_the_ID() ?: null);
    $val = $id ? get_post_meta((int) $id, "bereza_$key", true) : '';
    return ($val !== '' && $val !== false) ? $val : $default;
}

/**
 * Читает глобальную настройку темы из wp_options.
 */
function bereza_opt(string $key, $default = '') {
    $val = get_option("bereza_$key", null);
    return ($val !== null && $val !== '') ? $val : $default;
}

/**
 * Возвращает рубрику (тег) для поста.
 */
function bereza_get_tag(WP_Post $post): string {
    $label = bereza_field('category_label', $post->ID);
    if ($label) return $label;

    $map = [
        'kolumna'        => 'колонка',
        'rozsliduvannya' => 'розслідування',
        'video'          => 'відео',
        'podkast'        => 'подкаст',
        'post'           => 'публікація',
    ];
    return $map[$post->post_type] ?? 'матеріал';
}

/**
 * Форматирует дату публикации.
 */
function bereza_date(string $format = 'd.m.Y', $post_id = null): string {
    return get_the_date($format, $post_id);
}

// ── Размеры миниатюр ──────────────────────────────────────────────────────────
add_action('after_setup_theme', function () {
    add_image_size('bereza-hero',  1400, 800, true);
    add_image_size('bereza-card',   600, 400, true);
    add_image_size('bereza-thumb',  400, 300, true);
});

add_filter('excerpt_more',   fn() => '…');
add_filter('excerpt_length', fn() => 30);

// ── Отключение sitemap пользователей (приватность: не светим логины авторов) ──
add_filter('wp_sitemaps_add_provider', function ($provider, $name) {
    return 'users' === $name ? false : $provider;
}, 10, 2);
