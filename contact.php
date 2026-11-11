<?php
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/partials.php';

$result = handle_enquiry('contact');
$activeNav = 'contact';
$pageTitle = 'Contact';
$pageDescription = 'Visit Bio Fashion Hub in ' . setting('city', 'Tamale') . ', call ' . setting('phone_1') . ' or chat with us on WhatsApp.';
require __DIR__ . '/inc/header.php';
echo page_hero('Contact', 'We’d love to dress you', 'Visit the shop, call, or send us a message — the fastest way to reach us is WhatsApp.');
?>
<section class="section">
  <div class="wrap two-col">
    <div>
      <ul class="contact-list">
        <li><span class="ci"><?= icon('whatsapp') ?></span><div><strong>WhatsApp</strong><a href="<?= e(wa_link('Hello Bio Fashion Hub!')) ?>" target="_blank" rel="noopener"><?= e(setting('whatsapp_number')) ?></a></div></li>
        <li><span class="ci"><?= icon('phone') ?></span><div><strong>Call</strong>
          <a href="tel:<?= e(preg_replace('/\s+/', '', setting('phone_1'))) ?>"><?= e(setting('phone_1')) ?></a>
          <?php if (setting('phone_2')): ?><a href="tel:<?= e(preg_replace('/\s+/', '', setting('phone_2'))) ?>"><?= e(setting('phone_2')) ?></a><?php endif; ?>
        </div></li>
        <li><span class="ci"><?= icon('mail') ?></span><div><strong>Email</strong><a href="mailto:<?= e(setting('email')) ?>"><?= e(setting('email')) ?></a></div></li>
        <li><span class="ci"><?= icon('pin') ?></span><div><strong>Visit</strong><span><?= e(setting('address')) ?></span></div></li>
        <li><span class="ci"><?= icon('clock') ?></span><div><strong>Opening hours</strong><span><?= nl2br(e(setting('opening_hours'))) ?></span></div></li>
      </ul>
      <div class="map">
        <iframe title="Map showing <?= e(setting('business_name')) ?>" src="https://maps.google.com/maps?q=<?= e(rawurlencode(setting('map_query', 'Tamale, Ghana'))) ?>&z=15&output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
      </div>
      <a class="link-arrow" href="https://www.google.com/maps/search/?api=1&query=<?= e(rawurlencode(setting('map_query', 'Tamale, Ghana'))) ?>" target="_blank" rel="noopener">Get directions <?= icon('arrow') ?></a>
    </div>

    <div class="form-card">
      <?php if ($result && $result['ok']): ?>
        <div class="success">
          <span class="success-icon"><?= icon('check') ?></span>
          <h2 class="h3">Message sent — thank you!</h2>
          <p>We’ll reply as soon as we can. Want a faster answer? Send it on WhatsApp as well.</p>
          <a class="btn btn-wa btn-block" href="<?= e($result['wa']) ?>" target="_blank" rel="noopener"><?= icon('whatsapp') ?> Send on WhatsApp</a>
        </div>
      <?php else: ?>
        <h2 class="h3">Send us a message</h2>
        <?= isset($result['errors']['form']) ? '<div class="flash flash-error">' . e($result['errors']['form']) . '</div>' : '' ?>
        <form method="post" novalidate>
          <?= spam_fields() ?>
          <div class="field">
            <label for="name">Your name *</label>
            <input id="name" name="name" autocomplete="name" value="<?= old($result, 'name') ?>"<?= isset($result['errors']['name']) ? ' aria-invalid="true" aria-describedby="err-name"' : '' ?>>
            <?= field_error($result, 'name') ?>
          </div>
          <div class="field-row">
            <div class="field">
              <label for="phone">Phone / WhatsApp</label>
              <input id="phone" name="phone" type="tel" autocomplete="tel" inputmode="tel" value="<?= old($result, 'phone') ?>"<?= isset($result['errors']['phone']) ? ' aria-invalid="true" aria-describedby="err-phone"' : '' ?>>
              <?= field_error($result, 'phone') ?>
            </div>
            <div class="field">
              <label for="email">Email</label>
              <input id="email" name="email" type="email" autocomplete="email" value="<?= old($result, 'email') ?>"<?= isset($result['errors']['email']) ? ' aria-invalid="true" aria-describedby="err-email"' : '' ?>>
              <?= field_error($result, 'email') ?>
            </div>
          </div>
          <div class="field">
            <label for="message">Message *</label>
            <textarea id="message" name="message" rows="6"<?= isset($result['errors']['message']) ? ' aria-invalid="true" aria-describedby="err-message"' : '' ?>><?= old($result, 'message') ?></textarea>
            <?= field_error($result, 'message') ?>
          </div>
          <button class="btn btn-navy btn-block btn-lg" type="submit">Send message</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php require __DIR__ . '/inc/footer.php'; ?>
