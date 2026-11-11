<?php
require __DIR__ . '/_inc.php';
require_admin();

$errors = [];
$editId = (int)($_GET['edit'] ?? 0);

if (is_post()) {
    csrf_check();
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    $t = $id ? row('SELECT * FROM testimonials WHERE id = ?', [$id]) : null;
    if ($action === 'delete' && $t) {
        delete_upload($t['image']);
        q('DELETE FROM testimonials WHERE id = ?', [$id]);
        flash('success', 'Testimonial deleted.');
        redirect('admin/testimonials.php');
    }
    if ($action === 'toggle' && $t) {
        q('UPDATE testimonials SET published = 1 - published WHERE id = ?', [$id]);
        flash('success', $t['published'] ? 'Hidden from the website.' : 'Now showing on the website.');
        redirect('admin/testimonials.php');
    }
    if ($action === 'save') {
        $name = mb_substr(post('name'), 0, 100);
        $role = mb_substr(post('role'), 0, 100);
        $quote = mb_substr(post('quote'), 0, 800);
        $rating = max(1, min(5, (int)post('rating', '5')));
        $published = isset($_POST['published']) ? 1 : 0;
        if ($name === '' || $quote === '') $errors[] = 'Name and the customer’s words are both required.';
        if (!$errors) {
            try { $image = apply_image('image', $t['image'] ?? ''); }
            catch (RuntimeException $ex) { rollback_image_changes(); $errors[] = $ex->getMessage(); }
        }
        if (!$errors) {
            if ($t) q('UPDATE testimonials SET name=?, role=?, quote=?, rating=?, image=?, published=? WHERE id=?', [$name, $role, $quote, $rating, $image, $published, $id]);
            else q('INSERT INTO testimonials (name, role, quote, rating, image, published, sort) VALUES (?,?,?,?,?,?,?)', [$name, $role, $quote, $rating, $image, $published, (int)q('SELECT COALESCE(MAX(sort),0)+1 FROM testimonials')->fetchColumn()]);
            commit_image_changes();
            flash('success', 'Testimonial saved.');
            redirect('admin/testimonials.php');
        }
        $editId = $id;
    }
}

$items = rows('SELECT * FROM testimonials ORDER BY sort, id');
$edit = $editId ? row('SELECT * FROM testimonials WHERE id = ?', [$editId]) : null;
$form = $edit ?: ['id' => 0, 'name' => post('name'), 'role' => post('role'), 'quote' => post('quote'), 'rating' => 5, 'image' => '', 'published' => 1];

admin_header('Testimonials', 'testimonials');
?>
<p class="a-help">Only share words customers have agreed to let you publish. A good way: screenshot a happy WhatsApp message and ask, “May we share this on our website?”</p>
<?php foreach ($errors as $er): ?><div class="flash flash-error"><?= e($er) ?></div><?php endforeach; ?>

<ul class="a-items">
  <?php foreach ($items as $t): ?>
    <li class="a-item<?= $t['published'] ? '' : ' is-hidden' ?>">
      <div class="a-item-main">
        <span class="a-item-title"><?= e($t['name']) ?> <span class="a-sub"><?= e($t['role']) ?></span></span>
        <span class="a-sub">“<?= e(mb_strimwidth($t['quote'], 0, 140, '…')) ?>”</span>
        <span class="a-badges"><?= $t['published'] ? '<span class="a-tag">On website</span>' : '<span class="a-tag a-tag-muted">Hidden</span>' ?><?= str_starts_with($t['name'], 'SAMPLE') ? '<span class="a-tag a-tag-warn">Sample — replace</span>' : '' ?></span>
      </div>
      <div class="a-item-actions">
        <a class="a-btn a-btn-sm" href="?edit=<?= $t['id'] ?>#form">Edit</a>
        <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $t['id'] ?>"><input type="hidden" name="action" value="toggle"><button class="a-btn a-btn-ghost a-btn-sm"><?= $t['published'] ? 'Hide' : 'Show' ?></button></form>
        <form method="post" data-confirm="Delete this testimonial?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $t['id'] ?>"><input type="hidden" name="action" value="delete"><button class="a-btn a-btn-danger a-btn-sm">Delete</button></form>
      </div>
    </li>
  <?php endforeach; ?>
</ul>

<form method="post" enctype="multipart/form-data" class="a-card a-form" id="form">
  <h2><?= $form['id'] ? 'Edit testimonial' : 'Add a testimonial' ?></h2>
  <?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int)$form['id'] ?>">
  <div class="a-grid-2">
    <div class="a-field"><label for="t-name">Customer name *</label><input id="t-name" name="name" required value="<?= e($form['name']) ?>"></div>
    <div class="a-field"><label for="t-role">About them (optional)</label><input id="t-role" name="role" placeholder="e.g. Groom, Tamale" value="<?= e($form['role']) ?>"></div>
  </div>
  <div class="a-field"><label for="t-quote">What they said *</label><textarea id="t-quote" name="quote" rows="4" required><?= e($form['quote']) ?></textarea></div>
  <div class="a-grid-2">
    <div class="a-field"><label for="t-rating">Stars</label><select id="t-rating" name="rating"><?php for ($i = 5; $i >= 1; $i--): ?><option value="<?= $i ?>"<?= (int)$form['rating'] === $i ? ' selected' : '' ?>><?= $i ?> star<?= $i > 1 ? 's' : '' ?></option><?php endfor; ?></select></div>
    <?= image_input('image', 'Customer photo (optional)', $form['image']) ?>
  </div>
  <label class="a-check"><input type="checkbox" name="published" value="1"<?= $form['published'] ? ' checked' : '' ?>> Show on website</label>
  <div class="a-actions"><button class="a-btn" type="submit">Save testimonial</button><?php if ($form['id']): ?><a class="a-btn a-btn-ghost" href="<?= e(base_url('admin/testimonials.php')) ?>">Cancel</a><?php endif; ?></div>
</form>
<?php admin_footer(); ?>
