<?php
require __DIR__ . '/_inc.php';
require_admin();

$counts = [
    'Products' => [(int)q('SELECT COUNT(*) FROM products')->fetchColumn(), 'products.php'],
    'Unread enquiries' => [(int)q('SELECT COUNT(*) FROM enquiries WHERE is_read = 0')->fetchColumn(), 'enquiries.php'],
    'Lookbook photos' => [(int)q('SELECT COUNT(*) FROM gallery')->fetchColumn(), 'gallery.php'],
    'Live testimonials' => [(int)q('SELECT COUNT(*) FROM testimonials WHERE published = 1')->fetchColumn(), 'testimonials.php'],
];
$recent = rows('SELECT * FROM enquiries ORDER BY id DESC LIMIT 5');
$placeholders = (int)q("SELECT COUNT(*) FROM products WHERE image LIKE 'assets/img/placeholder/%'")->fetchColumn();
$noPrice = (int)q("SELECT COUNT(*) FROM products WHERE price IS NULL OR price = ''")->fetchColumn();

admin_header('Dashboard', 'dashboard');
?>
<div class="a-stats">
  <?php foreach ($counts as $label => [$n, $href]): ?>
    <a class="a-stat" href="<?= e(base_url('admin/' . $href)) ?>"><strong><?= $n ?></strong><span><?= e($label) ?></span></a>
  <?php endforeach; ?>
</div>

<div class="a-quick">
  <a class="a-btn" href="<?= e(base_url('admin/product-edit.php')) ?>">+ Add product</a>
  <a class="a-btn a-btn-ghost" href="<?= e(base_url('admin/gallery.php')) ?>">+ Add lookbook photos</a>
  <a class="a-btn a-btn-ghost" href="<?= e(base_url('admin/post-edit.php')) ?>">+ Write article</a>
</div>

<?php if ($placeholders || $noPrice || !setting('tiktok_username') && !setting('facebook_url')): ?>
<section class="a-card">
  <h2>Getting ready to launch</h2>
  <ul class="a-todo">
    <?php if ($placeholders): ?><li><?= $placeholders ?> product(s) still use sample photos from the flyer. <a href="<?= e(base_url('admin/products.php')) ?>">Add real photos</a></li><?php endif; ?>
    <?php if ($noPrice): ?><li><?= $noPrice ?> product(s) show “Price on request”. Add prices if you want them on the site.</li><?php endif; ?>
    <?php if (!setting('tiktok_username') && !setting('facebook_url')): ?><li>Add your social media links. <a href="<?= e(base_url('admin/settings.php')) ?>">Open settings</a></li><?php endif; ?>
    <li>Check the shop address and map location are exact. <a href="<?= e(base_url('admin/settings.php')) ?>">Open settings</a></li>
  </ul>
</section>
<?php endif; ?>

<section class="a-card">
  <div class="a-card-head"><h2>Latest enquiries</h2><a href="<?= e(base_url('admin/enquiries.php')) ?>">See all</a></div>
  <?php if ($recent): ?>
    <ul class="a-list">
      <?php foreach ($recent as $q): ?>
        <li class="<?= $q['is_read'] ? '' : 'unread' ?>">
          <a href="<?= e(base_url('admin/enquiries.php?view=' . $q['id'])) ?>">
            <strong><?= e($q['name']) ?></strong> <span class="a-tag"><?= $q['type'] === 'custom' ? 'Custom order' : 'Contact' ?></span>
            <span class="a-sub"><?= e(mb_strimwidth($q['message'], 0, 90, '…')) ?></span>
            <span class="a-sub"><?= e(date('j M, g:ia', strtotime($q['created_at'] . ' UTC'))) ?></span>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <p class="a-help">No enquiries yet. Messages from the Contact and Custom Orders forms will appear here.</p>
  <?php endif; ?>
</section>
<?php admin_footer(); ?>
