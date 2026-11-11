<?php
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/partials.php';

$p = row('SELECT p.*, c.name AS category_name, c.slug AS category_slug FROM products p
          LEFT JOIN categories c ON c.id = p.category_id WHERE p.slug = ? AND p.published = 1', [(string)($_GET['slug'] ?? '')]);
if (!$p) { http_response_code(404); require __DIR__ . '/404.php'; exit; }

$related = rows('SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id
                 WHERE p.published = 1 AND p.id != ? AND p.category_id IS ? ORDER BY p.sort, p.id DESC LIMIT 4', [$p['id'], $p['category_id']]);

$activeNav = 'shop';
$pageTitle = $p['name'];
$pageDescription = mb_substr($p['description'], 0, 160);
$pageImage = $p['image'];
require __DIR__ . '/inc/header.php';
$images = array_values(array_filter([$p['image'], $p['image2']]));
?>
<section class="section">
  <div class="wrap">
    <nav class="crumbs" aria-label="Breadcrumb">
      <a href="<?= e(base_url('shop.php')) ?>">Shop</a>
      <?php if ($p['category_name']): ?> / <a href="<?= e(base_url('shop.php?category=' . rawurlencode($p['category_slug']))) ?>"><?= e($p['category_name']) ?></a><?php endif; ?>
    </nav>
    <div class="product-detail">
      <div class="pd-media">
        <img id="pd-main" src="<?= e(img($images[0] ?? '')) ?>" alt="<?= e($p['name']) ?>">
        <?php if (count($images) > 1): ?>
          <div class="pd-thumbs">
            <?php foreach ($images as $i => $im): ?>
              <button type="button" data-src="<?= e(img($im)) ?>" aria-label="Show photo <?= $i + 1 ?>"><img src="<?= e(img($im)) ?>" alt=""></button>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
      <div class="pd-info">
        <?php if ($p['category_name']): ?><p class="eyebrow"><?= e($p['category_name']) ?></p><?php endif; ?>
        <h1><?= e($p['name']) ?></h1>
        <p class="price price-lg"><?= e(money($p['price'])) ?></p>
        <p class="stock <?= $p['in_stock'] ? 'in' : 'out' ?>"><?= $p['in_stock'] ? icon('check') . ' Available now' : icon('clock') . ' Made to order — ask for timing' ?></p>
        <div class="prose"><?= paras($p['description']) ?></div>
        <?php if (trim($p['details']) !== ''): ?>
          <ul class="details">
            <?php foreach (preg_split('/\R/', trim($p['details'])) as $line): if (trim($line) === '') continue; ?>
              <?php [$k, $v] = array_pad(explode(':', $line, 2), 2, null); ?>
              <li><?= $v !== null ? '<span>' . e(trim($k)) . '</span>' . e(trim($v)) : e($line) ?></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
        <a class="btn btn-wa btn-lg btn-block" href="<?= e(wa_link(product_order_message($p))) ?>" target="_blank" rel="noopener"><?= icon('whatsapp') ?> Order on WhatsApp</a>
        <p class="small muted">Your item is added to the message automatically. We’ll reply to confirm size, colour, price and delivery.</p>
        <a class="btn btn-outline btn-block" href="<?= e(base_url('custom-orders.php?item=' . rawurlencode($p['name']))) ?>"><?= icon('ruler') ?> Need it in your size or fabric?</a>
      </div>
    </div>
  </div>
</section>

<?php if ($related): ?>
<section class="section section-tint">
  <div class="wrap">
    <div class="section-head"><p class="eyebrow">You may also like</p><h2>More from <?= e($p['category_name'] ?: 'the shop') ?></h2></div>
    <div class="product-grid"><?php foreach ($related as $r) echo product_card($r); ?></div>
  </div>
</section>
<?php endif; ?>
<?php require __DIR__ . '/inc/footer.php'; ?>
