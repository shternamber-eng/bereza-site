<?php get_header(); the_post(); ?>

<main class="container" style="padding: 64px 0; max-width: 860px;">
  <h1 style="font-family: var(--f-display); font-weight: 800; font-size: clamp(36px,5vw,72px); letter-spacing: -0.03em; margin-bottom: 40px; line-height: 1.02;">
    <?php the_title(); ?>
  </h1>
  <div class="content" style="font-family: var(--f-body); font-size: 19px; line-height: 1.7;">
    <?php the_content(); ?>
  </div>
</main>

<?php get_footer(); ?>
