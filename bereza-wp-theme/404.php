<?php get_header(); ?>

<main class="container" style="padding: 96px 0; max-width: 720px; text-align: center;">
  <div class="section-label">Помилка 404</div>
  <h1 style="font-family: var(--f-display); font-weight: 800; font-size: clamp(36px,6vw,72px); letter-spacing: -0.03em; margin: 16px 0;">
    Сторінку не знайдено
  </h1>
  <p style="font-family: var(--f-body); font-size: 19px; line-height: 1.7; color: var(--ink-dim); margin-bottom: 32px;">
    Матеріал за цією адресою відсутній або був переміщений.
  </p>
  <a class="search-btn" href="<?php echo esc_url(home_url('/')); ?>">На головну ↗</a>
</main>

<?php get_footer(); ?>
