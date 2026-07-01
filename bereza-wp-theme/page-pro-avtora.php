<?php
/*
 * Template Name: Про автора
 */
get_header(); the_post();

$bio_paragraphs = bereza_field('bio_paragraphs', null, []);
$facts = bereza_field('about_facts', null, [
    ['num' => '184 700', 'label' => 'читачів щомісяця'],
    ['num' => '6',       'label' => 'років у журналістиці'],
    ['num' => '200+',    'label' => 'розслідувань'],
    ['num' => '37',      'label' => 'випусків подкасту'],
]);
$timeline = bereza_field('timeline', null, []);
?>

<!-- ============ ABOUT HERO ============ -->
<div class="about-hero container">
  <div class="about-portrait" aria-hidden="true">
    <?php if (has_post_thumbnail()): ?>
      <?php the_post_thumbnail('bereza-hero'); ?>
    <?php endif; ?>
  </div>
  <div class="about-text">
    <h1><?php the_title(); ?> <span>.</span></h1>
    <?php if ($bio_paragraphs && is_array($bio_paragraphs)):
      foreach ($bio_paragraphs as $p):
    ?>
        <p><?php echo esc_html($p['text'] ?? ''); ?></p>
    <?php endforeach;
    else: the_content(); endif; ?>
  </div>
</div>

<!-- ============ FACTS ============ -->
<?php if ($facts): ?>
<div class="about-facts container">
  <?php foreach ($facts as $f): ?>
    <div class="about-fact">
      <div class="num"><?php echo esc_html($f['num'] ?? ''); ?></div>
      <div class="label"><?php echo esc_html($f['label'] ?? ''); ?></div>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- ============ TIMELINE (якщо є) ============ -->
<?php if ($timeline && is_array($timeline)): ?>
<section class="latest container" style="padding: 64px 0;">
  <div class="section-label">Хронологія · <strong>ключові моменти</strong></div>
  <div style="display: flex; flex-direction: column; gap: 24px; margin-top: 8px;">
    <?php foreach ($timeline as $item): ?>
      <div style="display: grid; grid-template-columns: 120px 1fr; gap: 24px; border-top: 1px solid var(--rule); padding-top: 20px;">
        <div style="font-family: var(--f-mono); font-size: 12px; color: var(--accent); letter-spacing: 0.15em;">
          <?php echo esc_html($item['year'] ?? ''); ?>
        </div>
        <div>
          <strong style="font-family: var(--f-display); font-size: 17px;"><?php echo esc_html($item['title'] ?? ''); ?></strong>
          <p style="color: var(--ink-dim); font-size: 15px; margin-top: 6px;"><?php echo esc_html($item['desc'] ?? ''); ?></p>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php get_template_part('template-parts/subscribe-section'); ?>

<?php get_footer(); ?>
