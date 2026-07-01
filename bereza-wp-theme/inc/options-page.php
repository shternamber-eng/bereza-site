<?php
defined('ABSPATH') || exit;

add_action('admin_menu',            'bereza_add_options_page');
add_action('admin_init',            'bereza_register_settings');
add_action('admin_enqueue_scripts', 'bereza_enqueue_options_assets');

// ── Меню ──────────────────────────────────────────────────────────────────────
function bereza_add_options_page(): void {
    add_menu_page(
        'Налаштування БЕРЕЗА',
        'БЕРЕЗА',
        'manage_options',
        'bereza-settings',
        'bereza_render_options_page',
        'dashicons-editor-quote',
        3
    );
}

// ── Регистрация настроек ───────────────────────────────────────────────────────
function bereza_register_settings(): void {
    $simple = [
        'site_issue_number', 'site_city', 'site_temperature', 'site_readers',
        'quote_text', 'quote_source',
        'email_main', 'email_tip', 'pgp_url', 'secure_drop_url',
        'mailerlite_key', 'mailerlite_group',
    ];
    foreach ($simple as $key) {
        register_setting('bereza_options', "bereza_$key", ['sanitize_callback' => 'sanitize_text_field']);
    }

    // Поля, которые хранятся как JSON (массивы)
    foreach (['ticker_items', 'channels'] as $key) {
        register_setting('bereza_options', "bereza_$key", ['sanitize_callback' => 'bereza_sanitize_json_option']);
    }
}

function bereza_sanitize_json_option($val): string {
    if (is_string($val)) {
        $decoded = json_decode(stripslashes($val), true);
        return $decoded ? wp_json_encode($decoded) : '[]';
    }
    return '[]';
}

// ── Assets для страницы настроек ──────────────────────────────────────────────
function bereza_enqueue_options_assets(string $hook): void {
    if ($hook !== 'toplevel_page_bereza-settings') return;

    $ver = wp_get_theme()->get('Version');
    $uri = get_template_directory_uri() . '/assets';

    wp_enqueue_style( 'bereza-admin',         "$uri/css/admin.css",         [], $ver);
    wp_enqueue_script('bereza-admin-options',  "$uri/js/admin-options.js",   [], $ver, true);
}

// ── HTML страницы настроек ────────────────────────────────────────────────────
function bereza_render_options_page(): void {
    if (!current_user_can('manage_options')) return;

    // Сохранение
    if (isset($_POST['bereza_options_nonce']) && wp_verify_nonce($_POST['bereza_options_nonce'], 'bereza_save_options')) {
        bereza_save_options();
        echo '<div class="notice notice-success is-dismissible"><p>Налаштування збережено.</p></div>';
    }

    // Чтение
    $issue    = bereza_opt('site_issue_number', '184');
    $city     = bereza_opt('site_city', 'Київ');
    $temp     = bereza_opt('site_temperature', '9°C');
    $readers  = bereza_opt('site_readers', '184 700');
    $qt       = bereza_opt('quote_text',   'Журналістика — це не «розповідати, як було». Це <em>не давати забути</em>, як було насправді.');
    $qs       = bereza_opt('quote_source', 'Б. Береза · з есе «Те, що залишається»');
    $em_main  = bereza_opt('email_main',   'hello@bereza.media');
    $em_tip   = bereza_opt('email_tip',    'tip@bereza.media');
    $pgp      = bereza_opt('pgp_url',      '');
    $sd       = bereza_opt('secure_drop_url', '');
    $ml_key   = bereza_opt('mailerlite_key',   '');
    $ml_group = bereza_opt('mailerlite_group', '');

    $ticker_raw   = bereza_opt('ticker_items', '');
    $ticker_items = $ticker_raw ? json_decode($ticker_raw, true) : [['text' => 'Прямий ефір на YouTube — щосереди о 20:00']];

    $channels_raw = bereza_opt('channels', '');
    $channels     = $channels_raw ? json_decode($channels_raw, true) : [
        ['icon' => 'YT', 'name' => 'YouTube',    'count' => '218K', 'url' => ''],
        ['icon' => 'TG', 'name' => 'Telegram',   'count' => '94K',  'url' => ''],
        ['icon' => 'FB', 'name' => 'Facebook',   'count' => '168K', 'url' => ''],
        ['icon' => 'X',  'name' => 'X / Twitter','count' => '31K',  'url' => ''],
    ];
    ?>
    <div class="wrap bereza-options-wrap">
      <h1>⬛ Налаштування БЕРЕЗА</h1>
      <form method="post" action="">
        <?php wp_nonce_field('bereza_save_options', 'bereza_options_nonce'); ?>

        <div class="bereza-options-grid">

          <!-- ── ШАПКА ── -->
          <section class="bereza-section">
            <h2>Шапка сайту</h2>
            <table class="form-table">
              <tr><th><label for="bo_issue">№ випуску</label></th>
                  <td><input id="bo_issue" type="text" name="bereza_site_issue_number" value="<?php echo esc_attr($issue); ?>" class="regular-text"></td></tr>
              <tr><th><label for="bo_city">Місто</label></th>
                  <td><input id="bo_city" type="text" name="bereza_site_city" value="<?php echo esc_attr($city); ?>" class="regular-text"></td></tr>
              <tr><th><label for="bo_temp">Температура</label></th>
                  <td><input id="bo_temp" type="text" name="bereza_site_temperature" value="<?php echo esc_attr($temp); ?>" placeholder="9°C" class="small-text"></td></tr>
              <tr><th><label for="bo_readers">Тираж (читачів)</label></th>
                  <td><input id="bo_readers" type="text" name="bereza_site_readers" value="<?php echo esc_attr($readers); ?>" placeholder="184 700" class="regular-text"></td></tr>
            </table>
          </section>

          <!-- ── ТИКЕР ── -->
          <section class="bereza-section">
            <h2>Рядок тікера</h2>
            <div class="bereza-repeater" data-repeater="ticker">
              <?php foreach ((array) $ticker_items as $ti): ?>
                <div class="bereza-repeater-row">
                  <input type="text" name="bereza_ticker_row[]" value="<?php echo esc_attr($ti['text'] ?? ''); ?>" placeholder="Текст рядка" style="width:85%">
                  <button type="button" class="bereza-remove-row button">✕</button>
                </div>
              <?php endforeach; ?>
            </div>
            <button type="button" class="bereza-add-row button" data-target="ticker">+ Додати рядок</button>
            <input type="hidden" name="bereza_ticker_items" id="bereza_ticker_items_json">
          </section>

          <!-- ── ЦИТАТА ── -->
          <section class="bereza-section">
            <h2>Цитата на головній</h2>
            <table class="form-table">
              <tr><th><label for="bo_qt">Текст</label></th>
                  <td><textarea id="bo_qt" name="bereza_quote_text" rows="3" class="large-text"><?php echo esc_textarea($qt); ?></textarea>
                      <p class="description">Можна використовувати &lt;em&gt; для підсвічування.</p></td></tr>
              <tr><th><label for="bo_qs">Підпис</label></th>
                  <td><input id="bo_qs" type="text" name="bereza_quote_source" value="<?php echo esc_attr($qs); ?>" class="large-text"></td></tr>
            </table>
          </section>

          <!-- ── СОЦМЕРЕЖІ ── -->
          <section class="bereza-section">
            <h2>Соціальні мережі</h2>
            <div class="bereza-repeater bereza-channels-repeater" data-repeater="channels">
              <?php foreach ((array) $channels as $ch): ?>
                <div class="bereza-repeater-row bereza-channel-row">
                  <input type="text" name="bereza_ch_icon[]"  value="<?php echo esc_attr($ch['icon']  ?? ''); ?>" placeholder="Іконка (YT)" style="width:60px">
                  <input type="text" name="bereza_ch_name[]"  value="<?php echo esc_attr($ch['name']  ?? ''); ?>" placeholder="Назва" style="width:120px">
                  <input type="text" name="bereza_ch_count[]" value="<?php echo esc_attr($ch['count'] ?? ''); ?>" placeholder="218K" style="width:70px">
                  <input type="url"  name="bereza_ch_url[]"   value="<?php echo esc_attr($ch['url']   ?? ''); ?>" placeholder="https://…" style="width:220px">
                  <button type="button" class="bereza-remove-row button">✕</button>
                </div>
              <?php endforeach; ?>
            </div>
            <button type="button" class="bereza-add-row button" data-target="channels">+ Додати канал</button>
            <input type="hidden" name="bereza_channels" id="bereza_channels_json">
          </section>

          <!-- ── КОНТАКТИ ── -->
          <section class="bereza-section">
            <h2>Контакти</h2>
            <table class="form-table">
              <tr><th><label for="bo_em">Email (основний)</label></th>
                  <td><input id="bo_em" type="email" name="bereza_email_main" value="<?php echo esc_attr($em_main); ?>" class="regular-text"></td></tr>
              <tr><th><label for="bo_et">Email (для тіпів)</label></th>
                  <td><input id="bo_et" type="email" name="bereza_email_tip" value="<?php echo esc_attr($em_tip); ?>" class="regular-text"></td></tr>
              <tr><th><label for="bo_pgp">PGP URL</label></th>
                  <td><input id="bo_pgp" type="url" name="bereza_pgp_url" value="<?php echo esc_attr($pgp); ?>" class="regular-text"></td></tr>
              <tr><th><label for="bo_sd">SecureDrop URL</label></th>
                  <td><input id="bo_sd" type="url" name="bereza_secure_drop_url" value="<?php echo esc_attr($sd); ?>" class="regular-text"></td></tr>
            </table>
          </section>

          <!-- ── MAILERLITE ── -->
          <section class="bereza-section">
            <h2>MailerLite (підписка)</h2>
            <p class="description" style="margin-bottom:12px">Без заповнення — emails зберігаються у базі даних WordPress.</p>
            <table class="form-table">
              <tr><th><label for="bo_mlk">API Key</label></th>
                  <td><input id="bo_mlk" type="password" name="bereza_mailerlite_key"   value="<?php echo esc_attr($ml_key); ?>"   class="regular-text" autocomplete="new-password"></td></tr>
              <tr><th><label for="bo_mlg">Group ID</label></th>
                  <td><input id="bo_mlg" type="text"     name="bereza_mailerlite_group" value="<?php echo esc_attr($ml_group); ?>" class="regular-text"></td></tr>
            </table>
          </section>

        </div><!-- .bereza-options-grid -->

        <?php submit_button('Зберегти налаштування'); ?>
      </form>
    </div>
    <?php
}

// ── Сохранение всех настроек ──────────────────────────────────────────────────
function bereza_save_options(): void {
    $simple = [
        'site_issue_number' => 'sanitize_text_field',
        'site_city'         => 'sanitize_text_field',
        'site_temperature'  => 'sanitize_text_field',
        'site_readers'      => 'sanitize_text_field',
        'quote_source'      => 'sanitize_text_field',
        'email_main'        => 'sanitize_email',
        'email_tip'         => 'sanitize_email',
        'pgp_url'           => 'esc_url_raw',
        'secure_drop_url'   => 'esc_url_raw',
        'mailerlite_key'    => 'sanitize_text_field',
        'mailerlite_group'  => 'sanitize_text_field',
    ];
    foreach ($simple as $key => $sanitizer) {
        if (array_key_exists("bereza_$key", $_POST)) {
            update_option("bereza_$key", $sanitizer(wp_unslash($_POST["bereza_$key"])));
        }
    }

    // quote_text может содержать <em>
    if (array_key_exists('bereza_quote_text', $_POST)) {
        update_option('bereza_quote_text', wp_kses(wp_unslash($_POST['bereza_quote_text']), ['em' => []]));
    }

    // Тикер — строки из repeater собираются в JSON
    if (isset($_POST['bereza_ticker_items'])) {
        // Если JS отправил готовый JSON
        $raw = json_decode(stripslashes($_POST['bereza_ticker_items']), true);
        if (is_array($raw)) {
            update_option('bereza_ticker_items', wp_json_encode($raw));
        }
    } elseif (isset($_POST['bereza_ticker_row'])) {
        $items = array_map(fn($t) => ['text' => sanitize_text_field(wp_unslash($t))],
                           array_filter((array) $_POST['bereza_ticker_row']));
        update_option('bereza_ticker_items', wp_json_encode(array_values($items)));
    }

    // Каналы
    if (isset($_POST['bereza_channels'])) {
        $raw = json_decode(stripslashes($_POST['bereza_channels']), true);
        if (is_array($raw)) {
            update_option('bereza_channels', wp_json_encode($raw));
        }
    } elseif (isset($_POST['bereza_ch_icon'])) {
        $icons  = (array) wp_unslash($_POST['bereza_ch_icon']  ?? []);
        $names  = (array) wp_unslash($_POST['bereza_ch_name']  ?? []);
        $counts = (array) wp_unslash($_POST['bereza_ch_count'] ?? []);
        $urls   = (array) wp_unslash($_POST['bereza_ch_url']   ?? []);
        $chs = [];
        foreach ($icons as $i => $icon) {
            if (!$icon && !$names[$i]) continue;
            $chs[] = [
                'icon'  => sanitize_text_field($icon),
                'name'  => sanitize_text_field($names[$i]  ?? ''),
                'count' => sanitize_text_field($counts[$i] ?? ''),
                'url'   => esc_url_raw($urls[$i] ?? ''),
            ];
        }
        update_option('bereza_channels', wp_json_encode($chs));
    }
}
