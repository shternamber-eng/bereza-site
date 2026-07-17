<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php wp_head(); ?>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@400;600;700;800;900&family=Spectral:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php
// Тикер — берётся из ACF Options или дефолтные значения
$ticker_items = bereza_field('ticker_items', 'option');
$ticker_defaults = [
    'Прямий ефір на YouTube — щосереди о 20:00',
    'Новий випуск подкасту вже доступний',
    'Розслідування тижня: контракти на закупівлю',
    'Підпишіться на розсилку — щотижневий дайджест',
];
$ticker = $ticker_items ?: array_map(fn($t) => ['text' => $t], $ticker_defaults);
?>

<!-- ============ TOP BAR ============ -->
<div class="topbar">
  <div class="topbar-inner">
    <div class="ticker" aria-hidden="true">
      <div class="ticker-track">
        <?php foreach ($ticker as $item): ?>
          <span><?php echo esc_html(is_array($item) ? ($item['text'] ?? '') : $item); ?></span>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<!-- ============ MASTHEAD ============ -->
<?php
$issue_num  = bereza_field('site_issue_number', 'option', '184');
$readers    = bereza_field('site_readers', 'option', '184 700');

$days_uk = ['Неділя', 'Понеділок', 'Вівторок', 'Середа', 'Четвер', 'П\'ятниця', 'Субота'];
$months_uk = ['', 'січня', 'лютого', 'березня', 'квітня', 'травня', 'червня',
              'липня', 'серпня', 'вересня', 'жовтня', 'листопада', 'грудня'];
$dow = (int) date('w');
$day = (int) date('j');
$mon = (int) date('n');
$year = date('Y');
$day_label = $days_uk[$dow];
$date_str  = "$day {$months_uk[$mon]} $year";
?>
<header class="masthead">
  <div class="masthead-grid">
    <div class="meta-l">№ <?php echo esc_html($issue_num); ?> · <?php echo esc_html($day_label); ?><br><strong><?php echo esc_html($date_str); ?></strong></div>
    <div class="logo">
      <div class="kicker">особистий медіа-проєкт</div>
      <h1><a href="<?php echo esc_url(home_url('/')); ?>">БЕРЕЗА<span>.</span></a></h1>
      <div class="subline">Колонки · Розслідування · Розмови</div>
    </div>
    <div class="meta-r">Тираж · <?php echo esc_html($readers); ?> читачів</div>
  </div>
</header>

<!-- ============ NAV ============ -->
<nav class="primary" aria-label="Головна навігація">
  <div class="nav-inner">
    <button class="nav-toggle js-nav-toggle" aria-label="Меню" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>
    <ul>
      <li><a href="<?php echo esc_url(home_url('/')); ?>" <?php echo is_front_page() ? 'class="active"' : ''; ?>>Головна</a></li>
      <li><a href="<?php echo esc_url(home_url('/kolumny/')); ?>" <?php echo is_post_type_archive('kolumna') ? 'class="active"' : ''; ?>>Колонки</a></li>
      <li><a href="<?php echo esc_url(home_url('/rozsliduvannya/')); ?>" <?php echo is_post_type_archive('rozsliduvannya') ? 'class="active"' : ''; ?>>Розслідування</a></li>
      <li><a href="<?php echo esc_url(home_url('/video/')); ?>" <?php echo is_post_type_archive('video') ? 'class="active"' : ''; ?>>Відео</a></li>
      <li><a href="<?php echo esc_url(get_page_link(get_page_by_path('pro-avtora'))); ?>" <?php echo is_page('pro-avtora') ? 'class="active"' : ''; ?>>Про автора</a></li>
    </ul>
    <button class="search-btn js-search-toggle" aria-label="Пошук">Пошук <span aria-hidden="true">↗</span></button>
  </div>
</nav>
