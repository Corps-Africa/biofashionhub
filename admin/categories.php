<?php
require __DIR__ . '/_inc.php';
require_admin();

if (is_post()) {
    csrf_check();
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    if ($action === 'delete' && $id) {
        $c = row('SELECT * FROM categories WHERE id = ?', [$id]);
        if ($c) {
            q('DELETE FROM categories WHERE id = ?', [$id]);
            flash('success', 'Deleted “' . $c['name'] . '”. Its products are kept with no category.');
        }
    } elseif ($action === 'save') {
        $name = mb_substr(post('name'), 0, 80);
        $tagline = mb_substr(post('tagline'), 0, 200);
        $icon = in_array(post('icon'), ['kaftan', 'fabric', 'hat', 'watch', 'scissors', 'bag', 'star'], true) ? post('icon') : 'kaftan';
        $sort = (int)post('sort');
        if ($name === '') {
            flash('error', 'Category name is required.');
        } elseif ($id) {
            $old = row('SELECT * FROM categories WHERE id = ?', [$id]);
            if ($old) {
                $slug = $old['name'] === $name ? $old['slug'] : unique_slug('categories', $name, $id);
                q('UPDATE categories SET name=?, slug=?, tagline=?, icon=?, sort=? WHERE id=?', [$name, $slug, $tagline, $icon, $sort, $id]);
                flash('success', 'Saved “' . $name . '”.');
            }
        } else {
            q('INSERT INTO categories (name, slug, tagline, icon, sort) VALUES (?,?,?,?,?)', [$name, unique_slug('categories', $name), $tagline, $icon, $sort]);
            flash('success', 'Added “' . $name . '”.');
        }
    }
    redirect('admin/categories.php');
}

$cats = rows('SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) AS n FROM categories c ORDER BY sort, name');
admin_header('Categories', 'categories');

function cat_form(array $c): void { ?>
  <form method="post" class="a-cat-form">
    <?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int)($c['id'] ?? 0) ?>">
    <div class="a-grid-cat">
      <div class="a-field"><label>Name</label><input name="name" required value="<?= e($c['name'] ?? '') ?>"></div>
      <div class="a-field"><label>Short description</label><input name="tagline" value="<?= e($c['tagline'] ?? '') ?>"></div>
      <div class="a-field"><label>Icon</label><select name="icon"><?= admin_icon_options($c['icon'] ?? 'kaftan') ?></select></div>
      <div class="a-field"><label>Order</label><input name="sort" type="number" value="<?= (int)($c['sort'] ?? 0) ?>"></div>
    </div>
    <button class="a-btn a-btn-sm" type="submit"><?= empty($c['id']) ? 'Add category' : 'Save' ?></button>
  </form>
<?php }
?>
<p class="a-help">Categories group your products in the shop and appear as the “What we offer” list on the home page.</p>
<?php foreach ($cats as $c): ?>
  <div class="a-card">
    <div class="a-card-head"><h2><?= icon($c['icon']) ?> <?= e($c['name']) ?> <span class="a-sub">(<?= (int)$c['n'] ?> products)</span></h2>
      <form method="post" data-confirm="Delete the “<?= e($c['name']) ?>” category? Products in it will be kept."><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $c['id'] ?>"><button class="a-btn a-btn-danger a-btn-sm">Delete</button></form>
    </div>
    <?php cat_form($c); ?>
  </div>
<?php endforeach; ?>
<div class="a-card a-card-new">
  <h2>Add a new category</h2>
  <?php cat_form([]); ?>
</div>
<?php admin_footer(); ?>
