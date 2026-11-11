</main>
<?php
$socials = array_filter([
    'facebook' => setting('facebook_url'),
    'tiktok' => setting('tiktok_username') ? 'https://www.tiktok.com/@' . ltrim(setting('tiktok_username'), '@') : '',
    'instagram' => setting('instagram_url'),
]);
?>
<footer class="site-footer">
  <div class="wrap footer-grid">
    <div class="footer-brand">
      <img src="<?= e(base_url('assets/img/logo-mark.png')) ?>" alt="" width="64" height="68">
      <p class="footer-name">Bio Fashion <em>Hub</em></p>
      <p class="tagline-caps"><?= e(setting('tagline')) ?></p>
    </div>
    <div>
      <h2 class="footer-h">Visit &amp; call</h2>
      <ul class="footer-list">
        <li><?= icon('pin') ?> <?= e(setting('address')) ?></li>
        <li><?= icon('phone') ?> <a href="tel:<?= e(preg_replace('/\s+/', '', setting('phone_1'))) ?>"><?= e(setting('phone_1')) ?></a></li>
        <?php if (setting('phone_2')): ?><li><?= icon('phone') ?> <a href="tel:<?= e(preg_replace('/\s+/', '', setting('phone_2'))) ?>"><?= e(setting('phone_2')) ?></a></li><?php endif; ?>
        <li><?= icon('mail') ?> <a href="mailto:<?= e(setting('email')) ?>"><?= e(setting('email')) ?></a></li>
      </ul>
    </div>
    <div>
      <h2 class="footer-h">Explore</h2>
      <ul class="footer-list plain">
        <li><a href="<?= e(base_url('shop.php')) ?>">Shop all</a></li>
        <li><a href="<?= e(base_url('custom-orders.php')) ?>">Custom orders &amp; fittings</a></li>
        <li><a href="<?= e(base_url('lookbook.php')) ?>">Lookbook</a></li>
        <li><a href="<?= e(base_url('testimonials.php')) ?>">Testimonials</a></li>
        <li><a href="<?= e(base_url('blog.php')) ?>">Style journal</a></li>
      </ul>
    </div>
    <div>
      <h2 class="footer-h">Follow us</h2>
      <?php if ($socials): ?>
      <div class="social-row">
        <?php foreach ($socials as $net => $url): ?>
          <a href="<?= e($url) ?>" target="_blank" rel="noopener" aria-label="<?= e(ucfirst($net)) ?>"><?= icon($net) ?></a>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
      <p class="muted-light">Social links coming soon.</p>
      <?php endif; ?>
      <a class="btn btn-gold btn-sm" href="<?= e(wa_link('Hello Bio Fashion Hub!')) ?>" target="_blank" rel="noopener"><?= icon('whatsapp') ?> WhatsApp us</a>
    </div>
  </div>
  <div class="wrap footer-base">
    <p>&copy; <?= date('Y') ?> <?= e(setting('business_name')) ?>. All rights reserved.</p>
    <p><a href="<?= e(base_url('admin/')) ?>">Staff login</a></p>
  </div>
</footer>
<a class="wa-float" href="<?= e(wa_link('Hello ' . setting('business_name') . ', I would like to make an enquiry.')) ?>" target="_blank" rel="noopener" aria-label="Chat with us on WhatsApp"><?= icon('whatsapp') ?><span>Chat with us</span></a>
<script src="<?= e(base_url('assets/js/site.js')) ?>?v=1" defer></script>
</body>
</html>
