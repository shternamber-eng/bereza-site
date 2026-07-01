<?php
// Фолбэк для всех незаданных шаблонов
get_header();
?>
<main class="container" style="padding: 64px 0;">
  <?php if (have_posts()): ?>
    <?php while (have_posts()): the_post(); ?>
      <article style="margin-bottom: 40px; border-top: 3px solid var(--ink); padding-top: 20px;">
        <h2 style="font-family: var(--f-display); font-size: 28px; margin-bottom: 12px;">
          <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
        </h2>
        <div style="font-family: var(--f-mono); font-size: 11px; color: var(--ink-dim); margin-bottom: 16px;">
          <?php echo bereza_date(); ?>
        </div>
        <?php the_excerpt(); ?>
      </article>
    <?php endwhile; ?>
    <?php the_posts_pagination(); ?>
  <?php else: ?>
    <p>Матеріалів не знайдено.</p>
  <?php endif; ?>
</main>
<?php get_footer(); ?>
