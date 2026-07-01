<?php
$channels_raw = bereza_field('channels', 'option');
$channels_default = [
    ['icon' => 'YT', 'name' => 'YouTube',    'count' => '218K', 'url' => '#'],
    ['icon' => 'TG', 'name' => 'Telegram',   'count' => '94K',  'url' => '#'],
    ['icon' => 'FB', 'name' => 'Facebook',   'count' => '168K', 'url' => '#'],
    ['icon' => 'X',  'name' => 'X / Twitter','count' => '31K',  'url' => '#'],
];
$channels = $channels_raw ?: $channels_default;
?>

<section class="subscribe">
  <div class="container">
    <div class="subscribe-inner">

      <div>
        <h2>Щотижневий <mark>дайджест</mark> — без зайвого шуму.</h2>
        <p>Найважливіше за тиждень: одна колонка, два розслідування, кілька рекомендацій. Без сповіщень о третій ночі. Можна відписатися в один клік.</p>

        <form class="sub-form js-subscribe-form" novalidate>
          <?php wp_nonce_field('bereza_subscribe', 'bereza_nonce'); ?>
          <input type="email" name="email" placeholder="email@example.com" required aria-label="Ваш email">
          <button type="submit">Підписатися</button>
        </form>
        <p class="sub-message" style="display:none; margin-top: 12px; font-family: var(--f-mono); font-size: 12px; color: var(--accent);"></p>
      </div>

      <div class="channels">
        <?php foreach ($channels as $ch):
          $url   = esc_url($ch['url'] ?? '#');
          $icon  = esc_html($ch['icon'] ?? '');
          $name  = esc_html($ch['name'] ?? '');
          $count = esc_html($ch['count'] ?? '');
        ?>
          <a class="channel" href="<?php echo $url; ?>">
            <div class="left">
              <div class="icon"><?php echo $icon; ?></div>
              <div class="name"><?php echo $name; ?></div>
            </div>
            <div class="count"><?php echo $count; ?></div>
          </a>
        <?php endforeach; ?>
      </div>

    </div>
  </div>
</section>
