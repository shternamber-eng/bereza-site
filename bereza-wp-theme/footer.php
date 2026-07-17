<!-- ============ FOOTER ============ -->
<footer>
  <div class="container">
    <div class="footer-grid">
      <div class="footer-brand">
        <h3>БЕРЕЗА<span>.</span></h3>
        <p>Особистий медіа-проєкт: колонки, розслідування, відео і подкаст. Без редакцій і прес-секретарів. Незалежне фінансування — від читачів.</p>
      </div>

      <div class="footer-col">
        <h4>Розділи</h4>
        <ul>
          <li><a href="<?php echo esc_url(home_url('/kolumny/')); ?>">Колонки</a></li>
          <li><a href="<?php echo esc_url(home_url('/rozsliduvannya/')); ?>">Розслідування</a></li>
          <li><a href="<?php echo esc_url(home_url('/arkhiv/')); ?>">Архів</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4>Проєкт</h4>
        <ul>
          <li><a href="<?php echo esc_url(home_url('/pro-avtora/')); ?>">Про автора</a></li>
          <li><a href="<?php echo esc_url(home_url('/redaktsiyna-polityka/')); ?>">Редакційна політика</a></li>
          <li><a href="<?php echo esc_url(home_url('/pidtrymaty/')); ?>">Підтримати</a></li>
          <li><a href="<?php echo esc_url(home_url('/kontakty/')); ?>">Контакти</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4>Контакти</h4>
        <ul>
          <?php
          $email_main = bereza_field('email_main', 'option', 'hello@bereza.media');
          ?>
          <li><a href="mailto:<?php echo esc_attr($email_main); ?>"><?php echo esc_html($email_main); ?></a></li>
        </ul>
      </div>
    </div>

    <div class="footer-bottom">
      <span>© <?php echo date('Y'); ?> BEREZA.MEDIA · Київ</span>
      <span>Зроблено з турботою про факт</span>
    </div>
  </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
