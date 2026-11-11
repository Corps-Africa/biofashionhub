<?php
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/partials.php';

$result = handle_enquiry('custom');
$item = mb_substr(trim((string)($_GET['item'] ?? '')), 0, 120);

$activeNav = 'custom';
$pageTitle = 'Custom Orders & Fittings';
$pageDescription = 'Custom kaftans and outfits for weddings, grooms, Eid and special occasions in ' . setting('city', 'Tamale') . '. Book a fitting.';
require __DIR__ . '/inc/header.php';
echo page_hero('Custom orders', setting('custom_title'), setting('custom_text'));
?>
<section class="section">
  <div class="wrap two-col">
    <div>
      <h2 class="h3">How it works</h2>
      <ol class="numbered">
        <li><strong>Tell us about your occasion.</strong> Fill in the form or message us on WhatsApp.</li>
        <li><strong>Choose your style and fabric.</strong> Bring your own fabric or choose from our selection.</li>
        <li><strong>Get measured.</strong> Visit us in <?= e(setting('city', 'Tamale')) ?> at a time that suits you, or send measurements.</li>
        <li><strong>Fitting &amp; collection.</strong> We fine-tune the fit so you look your best on the day.</li>
      </ol>
      <div class="note-card">
        <h3 class="h4"><?= icon('clock') ?> Plan ahead</h3>
        <p>For weddings and group orders, contact us as early as you can so there’s plenty of time for fittings.</p>
      </div>
      <div class="ideal-for">
        <p class="eyebrow">Perfect for</p>
        <ul class="tags"><li>Weddings</li><li>Grooms &amp; groomsmen</li><li>Naming ceremonies</li><li>Eid</li><li>Graduations</li><li>Office &amp; events</li></ul>
      </div>
    </div>

    <div class="form-card">
      <?php if ($result && $result['ok']): ?>
        <div class="success">
          <span class="success-icon"><?= icon('check') ?></span>
          <h2 class="h3">Thank you — request received!</h2>
          <p>We’ll get back to you shortly. For a faster reply, send it to us on WhatsApp too.</p>
          <a class="btn btn-wa btn-block" href="<?= e($result['wa']) ?>" target="_blank" rel="noopener"><?= icon('whatsapp') ?> Send on WhatsApp</a>
        </div>
      <?php else: ?>
        <h2 class="h3">Request a custom outfit</h2>
        <?= isset($result['errors']['form']) ? '<div class="flash flash-error">' . e($result['errors']['form']) . '</div>' : '' ?>
        <form method="post" novalidate>
          <?= spam_fields() ?>
          <div class="field">
            <label for="name">Your name *</label>
            <input id="name" name="name" autocomplete="name" required value="<?= old($result, 'name') ?>"<?= isset($result['errors']['name']) ? ' aria-invalid="true" aria-describedby="err-name"' : '' ?>>
            <?= field_error($result, 'name') ?>
          </div>
          <div class="field-row">
            <div class="field">
              <label for="phone">Phone / WhatsApp *</label>
              <input id="phone" name="phone" type="tel" autocomplete="tel" inputmode="tel" placeholder="e.g. 024 000 0000" value="<?= old($result, 'phone') ?>"<?= isset($result['errors']['phone']) ? ' aria-invalid="true" aria-describedby="err-phone"' : '' ?>>
              <?= field_error($result, 'phone') ?>
            </div>
            <div class="field">
              <label for="email">Email (optional)</label>
              <input id="email" name="email" type="email" autocomplete="email" value="<?= old($result, 'email') ?>"<?= isset($result['errors']['email']) ? ' aria-invalid="true" aria-describedby="err-email"' : '' ?>>
              <?= field_error($result, 'email') ?>
            </div>
          </div>
          <div class="field-row">
            <div class="field">
              <label for="occasion">Occasion</label>
              <select id="occasion" name="occasion">
                <?php $occ = $result['old']['occasion'] ?? ''; foreach (['', 'Wedding (groom)', 'Wedding (guest / groomsmen)', 'Naming ceremony', 'Eid / Jumu’ah', 'Graduation', 'Work / everyday', 'Other'] as $o): ?>
                  <option value="<?= e($o) ?>"<?= $occ === $o ? ' selected' : '' ?>><?= $o === '' ? 'Choose…' : e($o) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="field">
              <label for="preferred_date">Fitting / visit date</label>
              <input id="preferred_date" name="preferred_date" type="date" min="<?= date('Y-m-d') ?>" value="<?= old($result, 'preferred_date') ?>">
            </div>
          </div>
          <div class="field">
            <label for="message">What would you like? *</label>
            <textarea id="message" name="message" rows="5" placeholder="Style, colours, number of outfits, your event date…"<?= isset($result['errors']['message']) ? ' aria-invalid="true" aria-describedby="err-message"' : '' ?>><?= $result ? old($result, 'message') : e($item ? "I'm interested in: $item\n" : '') ?></textarea>
            <?= field_error($result, 'message') ?>
          </div>
          <button class="btn btn-navy btn-block btn-lg" type="submit">Send request</button>
          <p class="small muted center">Prefer to chat? <a href="<?= e(wa_link('Hello Bio Fashion Hub, I would like a custom outfit.')) ?>" target="_blank" rel="noopener">Message us on WhatsApp</a>.</p>
        </form>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php require __DIR__ . '/inc/footer.php'; ?>
