<?php
defined('ABSPATH') || exit;

add_action('init', 'bereza_register_post_types');

function bereza_register_post_types(): void {
    $icon = 'dashicons-media-text';

    // ── Колонки ───────────────────────────────────────────────────────────────
    register_post_type('kolumna', [
        'labels' => bereza_cpt_labels('Колонка', 'Колонки'),
        'public'            => true,
        'has_archive'       => 'kolumny',
        'rewrite'           => ['slug' => 'kolumny'],
        'supports'          => ['title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields'],
        'show_in_rest'      => true,
        'menu_icon'         => 'dashicons-edit',
        'menu_position'     => 5,
        'show_in_nav_menus' => true,
    ]);

    // ── Розслідування ─────────────────────────────────────────────────────────
    register_post_type('rozsliduvannya', [
        'labels' => bereza_cpt_labels('Розслідування', 'Розслідування'),
        'public'            => true,
        'has_archive'       => 'rozsliduvannya',
        'rewrite'           => ['slug' => 'rozsliduvannya'],
        'supports'          => ['title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields'],
        'show_in_rest'      => true,
        'menu_icon'         => 'dashicons-search',
        'menu_position'     => 6,
        'show_in_nav_menus' => true,
    ]);

    // ── Відео ─────────────────────────────────────────────────────────────────
    register_post_type('video', [
        'labels' => bereza_cpt_labels('Відео', 'Відео'),
        'public'            => true,
        'has_archive'       => 'video',
        'rewrite'           => ['slug' => 'video'],
        'supports'          => ['title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields'],
        'show_in_rest'      => true,
        'menu_icon'         => 'dashicons-video-alt3',
        'menu_position'     => 7,
        'show_in_nav_menus' => true,
    ]);

    // ── Подкаст ───────────────────────────────────────────────────────────────
    register_post_type('podkast', [
        'labels' => bereza_cpt_labels('Подкаст', 'Подкасти'),
        'public'            => true,
        'has_archive'       => 'podkast',
        'rewrite'           => ['slug' => 'podkast'],
        'supports'          => ['title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields'],
        'show_in_rest'      => true,
        'menu_icon'         => 'dashicons-microphone',
        'menu_position'     => 8,
        'show_in_nav_menus' => true,
    ]);
}

function bereza_cpt_labels(string $singular, string $plural): array {
    return [
        'name'               => $plural,
        'singular_name'      => $singular,
        'add_new'            => "Додати $singular",
        'add_new_item'       => "Додати новий $singular",
        'edit_item'          => "Редагувати $singular",
        'new_item'           => "Новий $singular",
        'view_item'          => "Переглянути $singular",
        'search_items'       => "Шукати $plural",
        'not_found'          => "$plural не знайдено",
        'not_found_in_trash' => "$plural не знайдено у кошику",
        'menu_name'          => $plural,
    ];
}

// ── Скидання правил ЧПУ при активації теми ───────────────────────────────────
add_action('after_switch_theme', function () {
    bereza_register_post_types();
    flush_rewrite_rules();
});
