<?php
$quote_text   = bereza_field('quote_text', 'option', 'Журналістика — це не «розповідати, як було». Це <em>не давати забути</em>, як було насправді.');
$quote_source = bereza_field('quote_source', 'option', 'Б. Береза · з есе «Те, що залишається»');
?>

<section class="quote-section container">
  <blockquote>«<?php echo wp_kses($quote_text, ['em' => []]); ?>»</blockquote>
  <div class="qmeta"><?php echo esc_html($quote_source); ?></div>
</section>
