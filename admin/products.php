<?php
require __DIR__ . '/_inc.php';
require_admin();

if (is_post()) {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    $p = row('SELECT * FROM products WHERE id = ?', [$id]);
    if ($p) {
        switch ($_POST['action'] ?? '') {
            case 'delete':
                delete_upload($p['image']);
                delete_upload($p['image2']);
                q('DELETE FROM products WHERE id = ?', [$id]);
                flash('success', '“' . $p['name'] . '” was deleted.');
                break;
            case 'toggle_published':
                q('UPDATE products SET published = 1 - published WHERE id = ?', [$id]);
                flash('success', '“' . $p['name'] . '” is now ' . ($p['published'] ? 'hidden' : 'visible') . ' on the website.');
                break;
            case 'toggle_stock':
                q('UPDATE products SET in_stock = 1 - in_stock WHERE id = ?', [$id]);
                flash('success', '“' . $p['name'] . '” marked as ' . ($p['in_stock'] ? 'made to order' : 'available now') . '.');
                break;
        }
    }
    redirect('admin/products.php' . (isset($_GET['category']) ? '?category=' . (int)$_GET['category'] : ''));
}

$cats = rows('SELECT * FROM categories ORDER BY sort, name');
$filter = (int)($_GET['category'] ?? 0);
$products = rows('SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id'
    . ($filter ? ' WHERE p.category_id = ?' : '') . ' ORDER BY p.sort, p.id DESC', $filter ? [$filter] : []);

admin_header('Products', 'products');
?>
<div class="a-toolbar">
  <a class="a-btn" href="<?= e(base_url('admin/product-edit.php')) ?>">+ Add product</a>
  <form method="get" class="a-inline">
    <label class="sr-only" for="category">Filter by category</label>
    <select id="category" name="category" onchange="this.form.submit()">
      <option value="0">All categories</option>
      <?php foreach ($cats as $c): ?><option value="<?= $c['id'] ?>"<?= $filter == $c['id'] ? ' selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
    </select>
    <noscript><button class="a-btn a-btn-ghost a-btn-sm">Filter</button></noscript>
  </form>
</div>

<?php if (!$products): ?>
  <div class="a-card"><p>No products yet. <a href="<?= e(base_url('admin/product-edit.php')) ?>">Add your first product</a>.</p></div>
<?php else: ?>
<ul class="a-items">
  <?php foreach ($products as $p): ?>
    <li class="a-item<?= $p['published'] ? '' : ' is-hidden' ?>">
      <img src="<?= e(img($p['image'])) ?>" alt="">
      <div class="a-item-main">
        <a class="a-item-title" href="<?= e(base_url('admin/product-edit.php?id=' . $p['id'])) ?>"><?= e($p['name']) ?></a>
        <span class="a-sub"><?= e($p['category_name'] ?: 'No category') ?> · <?= e(money($p['price'])) ?></span>
        <span class="a-badges">
          <?php if (!$p['published']): ?><span class="a-tag a-tag-muted">Hidden</span><?php endif; ?>
          <?php if ($p['featured']): ?><span class="a-tag a-tag-gold">Featured</span><?php endif; ?>
          <span class="a-tag"><?= $p['in_stock'] ? 'Available' : 'Made to order' ?></span>
          <?php if (str_starts_with($p['image'], 'assets/img/placeholder/')): ?><span class="a-tag a-tag-warn">Sample photo</span><?php endif; ?>
        </span>
      </div>
      <div class="a-item-actions">
        <a class="a-btn a-btn-sm" href="<?= e(base_url('admin/product-edit.php?id=' . $p['id'])) ?>">Edit</a>
        <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $p['id'] ?>"><input type="hidden" name="action" value="toggle_stock">
          <button class="a-btn a-btn-ghost a-btn-sm"><?= $p['in_stock'] ? 'Mark made-to-order' : 'Mark available' ?></button></form>
        <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $p['id'] ?>"><input type="hidden" name="action" value="toggle_published">
          <button class="a-btn a-btn-ghost a-btn-sm"><?= $p['published'] ? 'Hide' : 'Show' ?></button></form>
        <form method="post" data-confirm="Delete “<?= e($p['name']) ?>”? This cannot be undone."><?= csrf_field() ?><input type="hidden" name="id" value="<?= $p['id'] ?>"><input type="hidden" name="action" value="delete">
          <button class="a-btn a-btn-danger a-btn-sm">Delete</button></form>
      </div>
    </li>
  <?php endforeach; ?>
</ul>
<?php endif; ?>
<?php admin_footer(); ?>
