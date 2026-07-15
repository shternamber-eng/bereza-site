<?php
/*
 * Template Name: Архів
 */
get_header();

$paged = max(1, get_query_var('paged'), get_query_var('page'));

$archive_query = new WP_Query([
    'post_type'      => ['post', 'kolumna', 'rozsliduvannya', 'video', 'podkast'],
    'posts_per_page' => 20,
    'paged'          => $paged,
]);
?>

<main class="latest container" style="padding-top: 48px;">
  <div class="section-label">Архів · <strong>усі матеріали</strong></div>

  <div class="archive-list">
    <?php if ($archive_query->have_posts()):
      while ($archive_query->have_posts()): $archive_query->the_post();
        $cat    = strtoupper(bereza_get_tag(get_post()));
        $action = (get_post_type() === 'podkast') ? '→ слухати' : '→ читати';
    ?>
        <article class="archive-row">
          <a href="<?php the_permalink(); ?>">
            <time datetime="<?php echo get_the_date('Y-m-d'); ?>"><?php echo bereza_date('d.m.Y'); ?></time>
            <span class="cat"><?php echo esc_html($cat); ?></span>
            <h3><?php the_title(); ?></h3>
            <span class="arrow"><?php echo esc_html($action); ?></span>
          </a>
        </article>
    <?php endwhile; wp_reset_postdata(); ?>
    <?php else: ?>
      <p style="color: var(--ink-dim); padding: 24px;">Матеріалів ще немає.</p>
    <?php endif; ?>
  </div>

  <div style="margin-top: 40px;">
    <?php
    echo paginate_links([
        'total'     => $archive_query->max_num_pages,
        'current'   => $paged,
        'prev_text' => '← Попередні',
        'next_text' => 'Наступні →',
    ]);
    ?>
  </div>
</main>

<?php get_footer(); ?>
