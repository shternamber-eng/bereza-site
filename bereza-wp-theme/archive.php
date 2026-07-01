<?php get_header(); ?>

<?php
$post_type    = get_post_type();
$labels_map   = [
    'kolumna'        => ['Авторські колонки', 'цього місяця'],
    'rozsliduvannya' => ['Розслідування', 'всі матеріали'],
    'video'          => ['Відео-канал', 'YouTube'],
    'podkast'        => ['Подкаст', 'всі випуски'],
];
[$section_title, $section_sub] = $labels_map[$post_type] ?? [get_post_type_object($post_type)->labels->name ?? 'Публікації', 'архів'];
?>

<main class="latest container" style="padding-top: 48px;">
  <div class="section-label"><?php echo esc_html($section_title); ?> · <strong><?php echo esc_html($section_sub); ?></strong></div>

  <div class="archive-list">
    <?php if (have_posts()):
      while (have_posts()): the_post();
        $cat    = strtoupper(bereza_get_tag(get_post()));
        $action = ($post_type === 'podkast') ? '→ слухати' : '→ читати';
    ?>
        <article class="archive-row">
          <a href="<?php the_permalink(); ?>">
            <time datetime="<?php echo get_the_date('Y-m-d'); ?>"><?php echo bereza_date('d.m.Y'); ?></time>
            <span class="cat"><?php echo esc_html($cat); ?></span>
            <h3><?php the_title(); ?></h3>
            <span class="arrow"><?php echo esc_html($action); ?></span>
          </a>
        </article>
    <?php endwhile; ?>
    <?php else: ?>
      <p style="color: var(--ink-dim); padding: 24px;">Матеріалів ще немає.</p>
    <?php endif; ?>
  </div>

  <div style="margin-top: 40px;">
    <?php the_posts_pagination(['prev_text' => '← Попередні', 'next_text' => 'Наступні →']); ?>
  </div>
</main>

<?php get_footer(); ?>
