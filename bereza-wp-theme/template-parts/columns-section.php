<?php
$columns_query = new WP_Query([
    'post_type'      => 'kolumna',
    'posts_per_page' => 3,
]);
if (!$columns_query->have_posts()) return;
?>

<section class="columns container">
  <div class="section-label">Авторські колонки · <strong>цього тижня</strong></div>
  <div class="columns-grid">
    <?php while ($columns_query->have_posts()): $columns_query->the_post();
      $cat     = bereza_field('category_label', null, 'Колонка');
      $excerpt = bereza_field('lede') ?: get_the_excerpt();
    ?>
      <article class="column-card">
        <a href="<?php the_permalink(); ?>">
          <div class="cat"><?php echo esc_html($cat); ?></div>
          <h3><?php the_title(); ?></h3>
          <?php if ($excerpt): ?>
            <p><?php echo esc_html($excerpt); ?></p>
          <?php endif; ?>
          <div class="meta">
            <span><?php echo bereza_date(); ?></span>
            <span class="read">читати →</span>
          </div>
        </a>
      </article>
    <?php endwhile; wp_reset_postdata(); ?>
  </div>
</section>
