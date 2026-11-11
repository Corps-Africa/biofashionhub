<?php
require __DIR__ . '/_inc.php';
require_admin();

if (is_post()) {
    csrf_check();
    $action = $_POST['action'] ?? '';
    if ($action === 'upload') {
        $files = $_FILES['photos'] ?? null;
        $added = 0;
        $fail = [];
        if ($files && is_array($files['name'])) {
            $next = (int)q('SELECT COALESCE(MIN(sort),0) FROM gallery')->fetchColumn() - 1;
            foreach (array_keys($files['name']) as $i) {
                // Re-shape each file so handle_image_upload can process it.
                $_FILES['__one'] = ['name' => $files['name'][$i], 'type' => $files['type'][$i], 'tmp_name' => $files['tmp_name'][$i], 'error' => $files['error'][$i], 'size' => $files['size'][$i]];
                try {
                    $path = handle_image_upload('__one');
                    if ($path) { q('INSERT INTO gallery (image, caption, sort) VALUES (?,?,?)', [$path, mb_substr(post('caption'), 0, 160), $next--]); $added++; }
                } catch (RuntimeException $ex) {
                    $fail[] = $files['name'][$i] . ': ' . $ex->getMessage();
                }
            }
        }
        if ($added) flash('success', "Added $added photo" . ($added > 1 ? 's' : '') . ' to the lookbook.');
        foreach ($fail as $f) flash('error', $f);
        if (!$added && !$fail) flash('error', 'Please choose at least one photo.');
    } elseif ($action === 'save') {
        foreach ((array)($_POST['caption_for'] ?? []) as $id => $cap) {
            q('UPDATE gallery SET caption = ?, sort = ? WHERE id = ?', [mb_substr(trim((string)$cap), 0, 160), (int)($_POST['sort_for'][$id] ?? 0), (int)$id]);
        }
        flash('success', 'Captions saved.');
    } elseif ($action === 'delete') {
        $g = row('SELECT * FROM gallery WHERE id = ?', [(int)($_POST['id'] ?? 0)]);
        if ($g) { delete_upload($g['image']); q('DELETE FROM gallery WHERE id = ?', [$g['id']]); flash('success', 'Photo removed.'); }
    }
    redirect('admin/gallery.php');
}

$items = rows('SELECT * FROM gallery ORDER BY sort, id DESC');
admin_header('Lookbook photos', 'gallery');
?>
<form method="post" enctype="multipart/form-data" class="a-card a-form">
  <h2>Add photos</h2>
  <?= csrf_field() ?><input type="hidden" name="action" value="upload">
  <div class="a-field"><label for="photos">Choose one or more photos</label><input id="photos" name="photos[]" type="file" accept="image/jpeg,image/png,image/webp" multiple required>
    <p class="a-help">You can select several at once. New photos appear first.</p></div>
  <div class="a-field"><label for="caption">Caption (optional, used for all photos in this upload)</label><input id="caption" name="caption" placeholder="e.g. Groom’s party outfits, June wedding"></div>
  <button class="a-btn" type="submit">Upload</button>
</form>

<?php if ($items): ?>
<form method="post" id="captions"><?= csrf_field() ?><input type="hidden" name="action" value="save"></form>
<div class="a-gallery">
  <?php foreach ($items as $g): ?>
    <div class="a-gal-item">
      <img src="<?= e(img($g['image'])) ?>" alt="">
      <label class="sr-only" for="cap-<?= $g['id'] ?>">Caption</label>
      <input form="captions" id="cap-<?= $g['id'] ?>" name="caption_for[<?= $g['id'] ?>]" value="<?= e($g['caption']) ?>" placeholder="Caption">
      <div class="a-gal-row">
        <label class="a-sub" for="sort-<?= $g['id'] ?>">Order</label>
        <input form="captions" id="sort-<?= $g['id'] ?>" class="a-sort" type="number" name="sort_for[<?= $g['id'] ?>]" value="<?= (int)$g['sort'] ?>">
        <form method="post" data-confirm="Remove this photo from the lookbook?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $g['id'] ?>"><button class="a-btn a-btn-danger a-btn-sm">Remove</button></form>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<div class="a-actions a-sticky"><button class="a-btn" type="submit" form="captions">Save captions &amp; order</button></div>
<?php endif; ?>
<?php admin_footer(); ?>
