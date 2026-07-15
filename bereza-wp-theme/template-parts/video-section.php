<?php
$videos = bereza_get_youtube_videos(3);
if (empty($videos)) return;

$large  = $videos[0];
$smalls = array_slice($videos, 1);
?>

<section class="video-section">
  <div class="container">
    <div class="section-label">Відео-канал · <strong>YouTube</strong></div>
    <div class="video-grid">

      <!-- Велика карточка -->
      <article class="video-card large">
        <a href="<?php echo esc_url($large['url']); ?>" target="_blank" rel="noopener">
          <div class="video-thumb">
            <?php if ($large['thumbnail']): ?>
              <img src="<?php echo esc_url($large['thumbnail']); ?>" alt="" loading="lazy" />
            <?php endif; ?>
          </div>
          <h3><?php echo esc_html($large['title']); ?></h3>
          <?php if ($large['published']): ?>
            <div class="vmeta"><?php echo esc_html(date_i18n('d.m.Y', strtotime($large['published']))); ?></div>
          <?php endif; ?>
        </a>
      </article>

      <!-- Малі карточки -->
      <?php foreach ($smalls as $v): ?>
        <article class="video-card">
          <a href="<?php echo esc_url($v['url']); ?>" target="_blank" rel="noopener">
            <div class="video-thumb">
              <?php if ($v['thumbnail']): ?>
                <img src="<?php echo esc_url($v['thumbnail']); ?>" alt="" loading="lazy" />
              <?php endif; ?>
            </div>
            <h3><?php echo esc_html($v['title']); ?></h3>
            <?php if ($v['published']): ?>
              <div class="vmeta"><?php echo esc_html(date_i18n('d.m.Y', strtotime($v['published']))); ?></div>
            <?php endif; ?>
          </a>
        </article>
      <?php endforeach; ?>

    </div>
  </div>
</section>
