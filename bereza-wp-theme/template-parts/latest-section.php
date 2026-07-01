<?php
$latest_query = new WP_Query([
    'post_type'      => ['post', 'kolumna', 'rozsliduvannya', 'video', 'podkast'],
    'posts_per_page' => 8,
    'meta_query'     => [['key' => '_thumbnail_id', 'compare' => 'EXISTS']],
]);
if (!$latest_query->have_posts()) return;
?>

<section class="latest container">
  <div class="section-label">Свіжі матеріали · <strong>усі рубрики</strong></div>
  <div class="latest-grid">
    <?php while ($latest_query->have_posts()): $latest_query->the_post();
      $cat    = strtoupper(bereza_get_tag(get_post()));
      $post_t = get_post_type();
      $action = ($post_t === 'podkast') ? '→ слухати' : '→ читати';
    ?>
      <article class="latest-card">
        <a href="<?php the_permalink(); ?>">
          <?php if (has_post_thumbnail()): ?>
            <div class="latest-card-thumb">
              <img src="<?php echo esc_url(get_the_post_thumbnail_url(get_the_ID(), 'bereza-card')); ?>" alt="" loading="lazy" />
            </div>
          <?php endif; ?>
          <div class="top">
            <span class="cat"><?php echo esc_html($cat); ?></span>
            <time datetime="<?php echo get_the_date('Y-m-d'); ?>"><?php echo bereza_date('d.m'); ?></time>
          </div>
          <h3><?php the_title(); ?></h3>
          <span class="arrow"><?php echo esc_html($action); ?></span>
        </a>
      </article>
    <?php endwhile; wp_reset_postdata(); ?>
  </div>
</section>
