<?php
// Головна публікація: спочатку шукаємо пост із ACF-полем is_hero = true,
// якщо немає — беремо найновіший серед усіх публічних CPT і post.
$hero_query = new WP_Query([
    'post_type'      => ['post', 'kolumna', 'rozsliduvannya', 'video', 'podkast'],
    'posts_per_page' => 1,
    'meta_query'     => [
        ['key' => 'is_hero', 'value' => '1', 'compare' => '='],
    ],
]);

if (!$hero_query->have_posts()) {
    $hero_query = new WP_Query([
        'post_type'      => ['post', 'kolumna', 'rozsliduvannya', 'video', 'podkast'],
        'posts_per_page' => 1,
    ]);
}

// Бічна панель: 4 найновіших публікації, крім hero
$hero_id = $hero_query->have_posts() ? $hero_query->posts[0]->ID : 0;
$side_query = new WP_Query([
    'post_type'      => ['post', 'kolumna', 'rozsliduvannya', 'video', 'podkast'],
    'posts_per_page' => 4,
    'post__not_in'   => $hero_id ? [$hero_id] : [],
]);
?>

<section class="hero container">
  <div class="section-label">Головна публікація · <strong>сьогодні</strong></div>
  <div class="hero-grid">

    <!-- Головний матеріал -->
    <?php
    $hero_img_style = '';
    if ($hero_query->have_posts() && has_post_thumbnail($hero_query->posts[0]->ID)) {
        $hero_img_url   = get_the_post_thumbnail_url($hero_query->posts[0]->ID, 'bereza-hero');
        $hero_img_style = " style=\"--hero-img: url('" . esc_url($hero_img_url) . "')\"";
    }
    ?>
    <article class="hero-main"<?php echo $hero_img_style; ?>>
      <?php if ($hero_query->have_posts()): $hero_query->the_post(); ?>
        <span class="monogram" aria-hidden="true">Б</span>

        <?php
        $tag       = bereza_get_tag(get_post());
        $is_urgent = bereza_field('is_urgent');
        $lede      = bereza_field('lede') ?: get_the_excerpt();
        $read_time = bereza_field('read_time', null, '');
        ?>

        <span class="tag<?php echo $is_urgent ? ' urgent' : ''; ?>"><?php echo esc_html($tag); ?></span>

        <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>

        <?php if ($lede): ?>
          <p class="lede"><?php echo esc_html($lede); ?></p>
        <?php endif; ?>

        <div class="byline">
          <?php $author = get_the_author(); ?>
          <span>Автор: <strong><?php echo esc_html($author); ?></strong></span>
          <span class="dot">●</span>
          <?php if ($read_time): ?>
            <span>читати <?php echo esc_html($read_time); ?></span>
            <span class="dot">●</span>
          <?php endif; ?>
          <span><?php echo bereza_date(); ?></span>
        </div>

      <?php else: ?>
        <p style="color: var(--ink-dim);">Матеріалів ще немає.</p>
      <?php endif; wp_reset_postdata(); ?>
    </article>

    <!-- Бічна панель -->
    <aside class="hero-side" aria-label="Топ матеріали">
      <?php if ($side_query->have_posts()):
        $i = 1;
        while ($side_query->have_posts()): $side_query->the_post();
          $cat = bereza_get_tag(get_post());
      ?>
        <a class="item" href="<?php the_permalink(); ?>">
          <?php if (has_post_thumbnail()): ?>
            <img class="item-thumb" src="<?php echo esc_url(get_the_post_thumbnail_url(get_the_ID(), 'bereza-thumb')); ?>" alt="" loading="lazy" />
          <?php endif; ?>
          <div class="item-body">
            <div class="row1">
              <span class="num"><?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?></span>
              <span class="cat"><?php echo esc_html($cat); ?></span>
            </div>
            <h3><?php the_title(); ?></h3>
            <time datetime="<?php echo get_the_date('Y-m-d'); ?>">
              <?php
              $rt = bereza_field('read_time');
              echo esc_html(bereza_date('d.m') . ($rt ? " · $rt" : ''));
              ?>
            </time>
          </div>
        </a>
      <?php $i++; endwhile; wp_reset_postdata(); endif; ?>
    </aside>

  </div>
</section>
