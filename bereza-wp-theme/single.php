<?php get_header(); the_post(); ?>

<?php
$tag       = bereza_get_tag(get_post());
$is_urgent = bereza_field('is_urgent');
$lede      = bereza_field('lede') ?: get_the_excerpt();
$read_time = bereza_field('read_time', null, '');
$sources   = bereza_field('sources');   // repeater: [{label, value}]
?>

<!-- ============ ARTICLE HERO ============ -->
<div class="article-hero container">
  <div class="crumbs">
    <a href="<?php echo esc_url(home_url('/')); ?>">Головна</a>
    &nbsp;→&nbsp;
    <a href="<?php echo esc_url(home_url('/' . get_post_type() . '/')); ?>"><?php echo esc_html(ucfirst($tag)); ?></a>
  </div>

  <span class="tag<?php echo $is_urgent ? ' urgent' : ''; ?>"><?php echo esc_html($tag); ?></span>

  <h1><?php the_title(); ?></h1>

  <?php if ($lede): ?>
    <p class="lede"><?php echo esc_html($lede); ?></p>
  <?php endif; ?>

  <div class="byline">
    <span>Автор: <strong><?php the_author(); ?></strong></span>
    <span class="dot">●</span>
    <?php if ($read_time): ?>
      <span>читати <?php echo esc_html($read_time); ?></span>
      <span class="dot">●</span>
    <?php endif; ?>
    <span><?php echo bereza_date(); ?></span>
  </div>
</div>

<!-- ============ ARTICLE BODY ============ -->
<div class="article-body container">

  <!-- Ліва колонка з метаданими -->
  <aside class="article-aside">
    <?php if ($read_time): ?>
      <div class="item">
        <span class="label">Час читання</span>
        <strong><?php echo esc_html($read_time); ?></strong>
      </div>
    <?php endif; ?>
    <div class="item">
      <span class="label">Дата</span>
      <strong><?php echo bereza_date(); ?></strong>
    </div>
    <div class="item">
      <span class="label">Рубрика</span>
      <strong><?php echo esc_html(ucfirst($tag)); ?></strong>
    </div>
    <?php if ($sources && is_array($sources)): ?>
      <div class="item">
        <span class="label">Джерела</span>
        <?php foreach ($sources as $s): ?>
          <strong><?php echo esc_html($s['label'] ?? ''); ?></strong>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </aside>

  <!-- Текст статті -->
  <div class="content">
    <?php the_content(); ?>
  </div>

  <!-- Права колонка — пуста, для симетрії -->
  <div></div>

</div>

<!-- ============ ЧИТАЙТЕ ТАКОЖ (внутрішні посилання) ============ -->
<?php
$related = get_posts([
    'post_type'      => get_post_type(),
    'posts_per_page' => 4,
    'post__not_in'   => [get_the_ID()],
    'category__in'   => wp_get_post_categories(get_the_ID()),
    'orderby'        => 'date',
    'order'          => 'DESC',
]);
if (!$related) {
    $related = get_posts([
        'post_type'      => get_post_type(),
        'posts_per_page' => 4,
        'post__not_in'   => [get_the_ID()],
        'orderby'        => 'date',
        'order'          => 'DESC',
    ]);
}
?>
<?php if ($related): ?>
<section class="latest container">
  <div class="section-label">Читайте також · <strong><?php echo esc_html(ucfirst($tag)); ?></strong></div>
  <div class="latest-grid">
    <?php foreach ($related as $rp):
      $r_cat    = strtoupper(bereza_get_tag($rp));
      $r_type   = $rp->post_type;
      $r_action = ($r_type === 'podkast') ? '→ слухати' : '→ читати';
    ?>
      <article class="latest-card">
        <a href="<?php echo esc_url(get_permalink($rp)); ?>">
          <div class="top">
            <span class="cat"><?php echo esc_html($r_cat); ?></span>
            <time datetime="<?php echo esc_attr(get_the_date('Y-m-d', $rp)); ?>"><?php echo esc_html(bereza_date('d.m', $rp->ID)); ?></time>
          </div>
          <h3><?php echo esc_html(get_the_title($rp)); ?></h3>
          <span class="arrow"><?php echo esc_html($r_action); ?></span>
        </a>
      </article>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php get_template_part('template-parts/subscribe-section'); ?>

<?php get_footer(); ?>
