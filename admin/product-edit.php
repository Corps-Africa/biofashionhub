<?php
require __DIR__ . '/_inc.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
$p = $id ? row('SELECT * FROM products WHERE id = ?', [$id]) : null;
if ($id && !$p) { flash('error', 'That product no longer exists.'); redirect('admin/products.php'); }
$p = $p ?: ['id' => 0, 'category_id' => null, 'name' => '', 'slug' => '', 'price' => '', 'description' => '', 'details' => '', 'image' => '', 'image2' => '', 'featured' => 0, 'in_stock' => 1, 'published' => 1, 'sort' => 0];
$cats = rows('SELECT * FROM categories ORDER BY sort, name');
$errors = [];

if (is_post()) {
    csrf_check();
    $in = [
        'name' => mb_substr(post('name'), 0, 140),
        'category_id' => (int)post('category_id') ?: null,
        'price' => preg_replace('/[^0-9.]/', '', post('price')),
        'description' => mb_substr(post('description'), 0, 4000),
        'details' => mb_substr(post('details'), 0, 2000),
        'featured' => isset($_POST['featured']) ? 1 : 0,
        'in_stock' => isset($_POST['in_stock']) ? 1 : 0,
        'published' => isset($_POST['published']) ? 1 : 0,
        'sort' => (int)post('sort'),
    ];
    if ($in['name'] === '') $errors[] = 'Please give the product a name.';
    if ($in['price'] !== '' && !is_numeric($in['price'])) $errors[] = 'Price should be a number, e.g. 350 or 350.50 — or leave it empty for “Price on request”.';
    if (!$errors) {
        try {
            $in['image'] = apply_image('image', $p['image']);
            $in['image2'] = apply_image('image2', $p['image2']);
        } catch (RuntimeException $ex) {
            rollback_image_changes();
            $errors[] = $ex->getMessage();
        }
    }
    if (!$errors) {
        $in['price'] = $in['price'] === '' ? null : $in['price'];
        if ($p['id']) {
            $in['slug'] = $p['name'] === $in['name'] ? $p['slug'] : unique_slug('products', $in['name'], (int)$p['id']);
            q('UPDATE products SET name=?, slug=?, category_id=?, price=?, description=?, details=?, image=?, image2=?, featured=?, in_stock=?, published=?, sort=? WHERE id=?',
                [$in['name'], $in['slug'], $in['category_id'], $in['price'], $in['description'], $in['details'], $in['image'], $in['image2'], $in['featured'], $in['in_stock'], $in['published'], $in['sort'], $p['id']]);
            flash('success', 'Saved “' . $in['name'] . '”.');
        } else {
            $in['slug'] = unique_slug('products', $in['name']);
            q('INSERT INTO products (name, slug, category_id, price, description, details, image, image2, featured, in_stock, published, sort) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)',
                [$in['name'], $in['slug'], $in['category_id'], $in['price'], $in['description'], $in['details'], $in['image'], $in['image2'], $in['featured'], $in['in_stock'], $in['published'], $in['sort']]);
            flash('success', 'Added “' . $in['name'] . '”.');
        }
        commit_image_changes();
        redirect('admin/products.php');
    }
    $p = array_merge($p, $in);
}

admin_header($p['id'] ? 'Edit product' : 'Add product', 'products');
?>
<?php foreach ($errors as $er): ?><div class="flash flash-error"><?= e($er) ?></div><?php endforeach; ?>
<form method="post" enctype="multipart/form-data" class="a-form">
  <?= csrf_field() ?>
  <div class="a-card">
    <div class="a-field"><label for="name">Product name *</label><input id="name" name="name" required maxlength="140" value="<?= e($p['name']) ?>"></div>
    <div class="a-grid-2">
      <div class="a-field"><label for="category_id">Category</label>
        <select id="category_id" name="category_id"><option value="">— None —</option>
          <?php foreach ($cats as $c): ?><option value="<?= $c['id'] ?>"<?= $p['category_id'] == $c['id'] ? ' selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
        </select></div>
      <div class="a-field"><label for="price">Price (GH₵)</label><input id="price" name="price" inputmode="decimal" placeholder="Leave empty for “Price on request”" value="<?= e((string)$p['price']) ?>"></div>
    </div>
    <div class="a-field"><label for="description">Description</label><textarea id="description" name="description" rows="5"><?= e($p['description']) ?></textarea>
      <p class="a-help">Leave an empty line between paragraphs. Wrap words in **double stars** to make them bold.</p></div>
    <div class="a-field"><label for="details">Details (one per line)</label><textarea id="details" name="details" rows="4" placeholder="Colour: Navy&#10;Sizes: S – XXL"><?= e($p['details']) ?></textarea>
      <p class="a-help">Write “Label: value” on each line, e.g. <em>Sizes: S – XXL</em>.</p></div>
  </div>
  <div class="a-card">
    <h2>Photos</h2>
    <div class="a-grid-2">
      <?= image_input('image', 'Main photo', $p['image']) ?>
      <?= image_input('image2', 'Second photo (optional)', $p['image2']) ?>
    </div>
  </div>
  <div class="a-card">
    <h2>Display</h2>
    <label class="a-check"><input type="checkbox" name="published" value="1"<?= $p['published'] ? ' checked' : '' ?>> Show on website</label>
    <label class="a-check"><input type="checkbox" name="featured" value="1"<?= $p['featured'] ? ' checked' : '' ?>> Feature on home page</label>
    <label class="a-check"><input type="checkbox" name="in_stock" value="1"<?= $p['in_stock'] ? ' checked' : '' ?>> Available now (untick for “Made to order”)</label>
    <div class="a-field a-narrow"><label for="sort">Order</label><input id="sort" name="sort" type="number" value="<?= (int)$p['sort'] ?>"><p class="a-help">Lower numbers appear first.</p></div>
  </div>
  <div class="a-actions">
    <button class="a-btn" type="submit">Save product</button>
    <a class="a-btn a-btn-ghost" href="<?= e(base_url('admin/products.php')) ?>">Cancel</a>
    <?php if ($p['id'] && $p['published']): ?><a class="a-link" href="<?= e(base_url('product.php?slug=' . rawurlencode($p['slug']))) ?>" target="_blank" rel="noopener">View on website ↗</a><?php endif; ?>
  </div>
</form>
<?php admin_footer(); ?>
