<?php
defined('ABSPATH') || exit;

// ── Meta description + Open Graph / Twitter Card для статей ───────────────────
add_action('wp_head', function () {
    if (!is_singular() || is_front_page()) return;

    $post_id     = get_the_ID();
    $description = bereza_field('lede', $post_id) ?: get_the_excerpt();
    $description = trim(wp_strip_all_tags($description));
    $description = $description ? mb_substr($description, 0, 160) : '';

    $title = wp_strip_all_tags(get_the_title($post_id));
    $url   = get_permalink($post_id);
    $image = get_the_post_thumbnail_url($post_id, 'bereza-hero');

    if ($description) {
        printf('<meta name="description" content="%s">' . "\n", esc_attr($description));
    }

    printf('<meta property="og:type" content="article">' . "\n");
    printf('<meta property="og:title" content="%s">' . "\n", esc_attr($title));
    if ($description) {
        printf('<meta property="og:description" content="%s">' . "\n", esc_attr($description));
    }
    printf('<meta property="og:url" content="%s">' . "\n", esc_url($url));
    if ($image) {
        printf('<meta property="og:image" content="%s">' . "\n", esc_url($image));
    }
    printf('<meta name="twitter:card" content="%s">' . "\n", $image ? 'summary_large_image' : 'summary');
}, 5);

// ── Meta description для головної сторінки ─────────────────────────────────────
add_action('wp_head', function () {
    if (!is_front_page()) return;

    $name = bereza_opt('person_full_name', 'Борислав Береза');
    $description = sprintf(
        '%s — офіційний сайт: колонки, розслідування та відео. Актуальні новини, позиція та контакти.',
        $name
    );

    printf('<meta name="description" content="%s">' . "\n", esc_attr($description));
    printf('<meta property="og:type" content="website">' . "\n");
    printf('<meta property="og:title" content="%s | Офіційний сайт">' . "\n", esc_attr($name));
    printf('<meta property="og:description" content="%s">' . "\n", esc_attr($description));
    printf('<meta property="og:url" content="%s">' . "\n", esc_url(home_url('/')));
}, 5);

// ── Title сторінки: "Ім'я | Офіційний сайт" на головній ────────────────────────
add_filter('document_title_parts', function (array $parts): array {
    if (!is_front_page()) return $parts;

    $name = bereza_opt('person_full_name', 'Борислав Береза');
    return [
        'title' => "$name | Офіційний сайт",
    ];
});

// ── Структурированные данные Person (JSON-LD) — головна сторінка ───────────────
add_action('wp_head', function () {
    if (!is_front_page()) return;

    $name  = bereza_opt('person_full_name', 'Борислав Береза');
    $job   = bereza_opt('person_job_title', '');
    $wiki  = bereza_opt('person_wikipedia_url', '');
    $photo = bereza_opt('person_photo_url', '');

    if (!$photo) {
        $about_page = get_page_by_path('pro-avtora');
        if ($about_page && has_post_thumbnail($about_page->ID)) {
            $photo = get_the_post_thumbnail_url($about_page->ID, 'bereza-hero');
        }
    }

    $same_as = [];
    $channels_raw = bereza_opt('channels', '');
    $channels = $channels_raw ? json_decode($channels_raw, true) : [];
    if (is_array($channels)) {
        foreach ($channels as $ch) {
            if (!empty($ch['url'])) {
                $same_as[] = esc_url_raw($ch['url']);
            }
        }
    }
    if ($wiki) {
        $same_as[] = esc_url_raw($wiki);
    }

    $data = [
        '@context' => 'https://schema.org',
        '@type'    => 'Person',
        'name'     => $name,
        'url'      => home_url('/'),
    ];

    if ($job)     $data['jobTitle'] = $job;
    if ($photo)   $data['image']    = $photo;
    if ($same_as) $data['sameAs']   = array_values(array_unique($same_as));

    echo '<script type="application/ld+json">'
        . wp_json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        . '</script>' . "\n";
}, 6);

// ── Структурированные данные NewsArticle (JSON-LD) ─────────────────────────────
add_action('wp_head', function () {
    $types = ['post', 'kolumna', 'rozsliduvannya', 'podkast'];
    if (!is_singular($types)) return;

    $post_id = get_the_ID();
    $image   = get_the_post_thumbnail_url($post_id, 'bereza-hero');
    $lede    = bereza_field('lede', $post_id) ?: get_the_excerpt();
    $lede    = trim(wp_strip_all_tags($lede));

    $data = [
        '@context'         => 'https://schema.org',
        '@type'            => 'NewsArticle',
        'headline'         => wp_strip_all_tags(get_the_title($post_id)),
        'datePublished'    => get_the_date('c', $post_id),
        'dateModified'     => get_the_modified_date('c', $post_id),
        'author'           => [
            '@type' => 'Person',
            'name'  => get_the_author_meta('display_name', get_post_field('post_author', $post_id)),
        ],
        'publisher'        => [
            '@type' => 'Organization',
            'name'  => 'БЕРЕЗА',
        ],
        'mainEntityOfPage' => [
            '@type' => 'WebPage',
            '@id'   => get_permalink($post_id),
        ],
    ];

    if ($lede) {
        $data['description'] = mb_substr($lede, 0, 300);
    }

    if ($image) {
        $data['image'] = [$image];
    }

    echo '<script type="application/ld+json">'
        . wp_json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        . '</script>' . "\n";
}, 6);

// ── Alt-текст по умолчанию для изображений без него ────────────────────────────
add_filter('wp_get_attachment_image_attributes', function ($attr, $attachment) {
    if (empty($attr['alt'])) {
        $alt = trim($attachment->post_excerpt) ?: trim($attachment->post_title);
        if ($alt) {
            $attr['alt'] = $alt;
        }
    }
    return $attr;
}, 10, 2);

add_filter('the_content', function ($content) {
    if (!is_singular() || !str_contains($content, '<img')) {
        return $content;
    }

    $fallback_alt = wp_strip_all_tags(get_the_title());

    return preg_replace_callback('/<img\s[^>]*>/i', function ($m) use ($fallback_alt) {
        $tag = $m[0];

        if (preg_match('/\salt=(["\']).*?\1/i', $tag)) {
            // alt уже указан (даже пустой) — не трогаем
            if (preg_match('/\salt=(["\'])\s*\1/i', $tag)) {
                return preg_replace('/\salt=(["\'])\s*\1/i', ' alt="' . esc_attr($fallback_alt) . '"', $tag);
            }
            return $tag;
        }

        return preg_replace('/<img\s/i', '<img alt="' . esc_attr($fallback_alt) . '" ', $tag);
    }, $content);
}, 20);
