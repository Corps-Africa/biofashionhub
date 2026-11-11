<?php
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/partials.php';

$categories = rows('SELECT * FROM categories ORDER BY sort, name');
$slug = (string)($_GET['category'] ?? '');
$current = $slug !== '' ? row('SELECT * FROM categories WHERE slug = ?', [$slug]) : null;
$search = mb_substr(trim((string)($_GET['q'] ?? '')), 0, 60);

$sql = 'SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id WHERE p.published = 1';
$params = [];
if ($current) { $sql .= ' AND p.category_id = ?'; $params[] = $current['id']; }
if ($search !== '') { $sql .= ' AND (p.name LIKE ? OR p.description LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; }
$products = rows($sql . ' ORDER BY p.sort, p.id DESC', $params);

$activeNav = 'shop';
$pageTitle = $current ? $current['name'] : 'Shop';
$pageDescription = $current ? $current['tagline'] : 'Shop kaftans, quality fabrics, Hausa caps, accessories and wristwatches. Order on WhatsApp.';
require __DIR__ . '/inc/header.php';
echo page_hero('Shop', $current ? $current['name'] : 'The collection', $current ? $current['tagline'] : 'Tap “Order on WhatsApp” on any item and we’ll confirm size, price and delivery with you.');
?>
<section class="section section-tight">
  <div class="wrap">
    <div class="shop-bar">
      <nav class="chips" aria-label="Categories">
        <a class="chip" href="<?= e(base_url('shop.php')) ?>"<?= !$current ? ' aria-current="page"' : '' ?>>All</a>
        <?php foreach ($categories as $c): ?>
          <a class="chip" href="<?= e(base_url('shop.php?category=' . rawurlencode($c['slug']))) ?>"<?= $current && $current['id'] == $c['id'] ? ' aria-current="page"' : '' ?>><?= e($c['name']) ?></a>
        <?php endforeach; ?>
      </nav>
      <form class="search" action="<?= e(base_url('shop.php')) ?>" method="get" role="search">
        <?php if ($current): ?><input type="hidden" name="category" value="<?= e($current['slug']) ?>"><?php endif; ?>
        <label class="sr-only" for="q">Search products</label>
        <input id="q" name="q" type="search" placeholder="Search products" value="<?= e($search) ?>">
        <button class="btn btn-navy btn-sm" type="submit">Search</button>
      </form>
    </div>

    <?php if ($products): ?>
      <div class="product-grid">
        <?php foreach ($products as $p) echo product_card($p); ?>
      </div>
    <?php else: ?>
      <div class="empty">
        <p>No products found<?= $search ? ' for “' . e($search) . '”' : '' ?>.</p>
        <p>Can’t see what you want? We can source it or make it for you.</p>
        <a class="btn btn-wa" href="<?= e(wa_link('Hello Bio Fashion Hub, I am looking for ' . ($search ?: 'something specific') . '.')) ?>" target="_blank" rel="noopener"><?= icon('whatsapp') ?> Ask us on WhatsApp</a>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/inc/footer.php'; ?>
