<?php
$videos_query = new WP_Query([
    'post_type'      => 'video',
    'posts_per_page' => 3,
]);
if (!$videos_query->have_posts()) return;

$posts   = $videos_query->posts;
$large   = $posts[0];
$smalls  = array_slice($posts, 1);
?>

<section class="video-section">
  <div class="container">
    <div class="section-label">Відео-канал · <strong>YouTube</strong></div>
    <div class="video-grid">

      <!-- Велика карточка -->
      <?php
      $duration  = bereza_field('duration', $large->ID, '');
      $view_meta = bereza_field('view_meta', $large->ID, '');
      $yt_url    = bereza_field('youtube_url', $large->ID, '#');
      ?>
      <article class="video-card large">
        <a href="<?php echo esc_url($yt_url ?: get_permalink($large->ID)); ?>">
          <div class="video-thumb">
            <?php if ($duration): ?>
              <span class="duration"><?php echo esc_html($duration); ?></span>
            <?php endif; ?>
          </div>
          <h3><?php echo esc_html($large->post_title); ?></h3>
          <?php if ($view_meta): ?>
            <div class="vmeta"><?php echo esc_html($view_meta); ?></div>
          <?php endif; ?>
        </a>
      </article>

      <!-- Малі карточки -->
      <?php foreach ($smalls as $v):
        $d  = bereza_field('duration', $v->ID, '');
        $vm = bereza_field('view_meta', $v->ID, '');
        $yu = bereza_field('youtube_url', $v->ID, '#');
      ?>
        <article class="video-card">
          <a href="<?php echo esc_url($yu ?: get_permalink($v->ID)); ?>">
            <div class="video-thumb">
              <?php if ($d): ?>
                <span class="duration"><?php echo esc_html($d); ?></span>
              <?php endif; ?>
            </div>
            <h3><?php echo esc_html($v->post_title); ?></h3>
            <?php if ($vm): ?>
              <div class="vmeta"><?php echo esc_html($vm); ?></div>
            <?php endif; ?>
          </a>
        </article>
      <?php endforeach; wp_reset_postdata(); ?>

    </div>
  </div>
</section>
