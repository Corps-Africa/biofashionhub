<?php
require __DIR__ . '/_inc.php';
require_admin();

$fields = setting_fields();
$errors = [];

if (is_post()) {
    csrf_check();
    $values = [];
    foreach ($fields as $key => [$label, $default, $type]) {
        $current = setting($key, $default);
        if ($type === 'image') {
            try { $values[$key] = apply_image($key, $current); }
            catch (RuntimeException $ex) { $errors[] = $label . ': ' . $ex->getMessage(); }
            continue;
        }
        if ($type === 'checkbox') { $values[$key] = isset($_POST[$key]) ? '1' : '0'; continue; }
        $v = mb_substr(post($key), 0, $type === 'textarea' ? 3000 : 300);
        if ($type === 'email' && $v !== '' && !filter_var($v, FILTER_VALIDATE_EMAIL)) $errors[] = "$label is not a valid email address.";
        if ($type === 'url' && $v !== '') {
            if (!preg_match('#^https?://#i', $v)) $v = 'https://' . $v;
            if (!filter_var($v, FILTER_VALIDATE_URL)) $errors[] = "$label is not a valid link.";
        }
        if ($key === 'tiktok_username') $v = preg_replace('/[^A-Za-z0-9_.]/', '', ltrim($v, '@'));
        if ($key === 'whatsapp_number' && strlen(wa_number($v)) < 11) $errors[] = 'Please enter a full WhatsApp number, e.g. 054 847 3309.';
        $values[$key] = $v;
    }
    if (!$errors) {
        $st = db()->prepare('INSERT INTO settings (key, value) VALUES (?, ?) ON CONFLICT(key) DO UPDATE SET value = excluded.value');
        foreach ($values as $k => $v) $st->execute([$k, $v]);
        commit_image_changes();
        flash('success', 'Settings saved. Changes are live on the website.');
        redirect('admin/settings.php');
    }
    rollback_image_changes();
}

$groups = [];
foreach ($fields as $key => $f) $groups[$f[3]][$key] = $f;
admin_header('Site settings', 'settings');
?>
<?php foreach ($errors as $er): ?><div class="flash flash-error"><?= e($er) ?></div><?php endforeach; ?>
<form method="post" enctype="multipart/form-data" class="a-form">
  <?= csrf_field() ?>
  <?php foreach ($groups as $group => $gf): ?>
    <section class="a-card">
      <h2><?= e($group) ?></h2>
      <?php foreach ($gf as $key => [$label, $default, $type, , $help]):
          $val = is_post() ? (string)($_POST[$key] ?? '') : setting($key, $type === 'checkbox' ? '0' : '');
          if ($type === 'image'): echo image_input($key, $label, setting($key), $help);
          elseif ($type === 'checkbox'): ?>
            <label class="a-check"><input type="checkbox" name="<?= e($key) ?>" value="1"<?= setting($key) === '1' ? ' checked' : '' ?>> <?= e($label) ?></label>
            <?php if ($help): ?><p class="a-help"><?= e($help) ?></p><?php endif; ?>
          <?php else: ?>
            <div class="a-field">
              <label for="<?= e($key) ?>"><?= e($label) ?></label>
              <?php if ($type === 'textarea'): ?>
                <textarea id="<?= e($key) ?>" name="<?= e($key) ?>" rows="<?= str_contains($key, 'story') ? 7 : 3 ?>"><?= e($val) ?></textarea>
              <?php else: ?>
                <input id="<?= e($key) ?>" name="<?= e($key) ?>" type="<?= $type === 'url' ? 'text' : e($type) ?>" value="<?= e($val) ?>"<?= $type === 'url' ? ' inputmode="url" placeholder="https://"' : '' ?>>
              <?php endif; ?>
              <?php if ($help): ?><p class="a-help"><?= e($help) ?></p><?php endif; ?>
            </div>
          <?php endif; ?>
      <?php endforeach; ?>
      <?php if ($group === 'Business details'): ?>
        <p class="a-help">Test your WhatsApp link: <a href="<?= e(wa_link('Test message from the website')) ?>" target="_blank" rel="noopener"><?= e(wa_link()) ?></a></p>
      <?php endif; ?>
    </section>
  <?php endforeach; ?>
  <div class="a-actions a-sticky"><button class="a-btn" type="submit">Save settings</button></div>
</form>
<?php admin_footer(); ?>
