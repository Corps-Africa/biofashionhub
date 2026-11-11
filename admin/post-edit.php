<?php
require __DIR__ . '/_inc.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
$p = $id ? row('SELECT * FROM posts WHERE id = ?', [$id]) : null;
if ($id && !$p) { flash('error', 'That article no longer exists.'); redirect('admin/posts.php'); }
$p = $p ?: ['id' => 0, 'title' => '', 'slug' => '', 'excerpt' => '', 'body' => '', 'image' => '', 'published' => 1, 'published_at' => date('Y-m-d H:i:s')];
$errors = [];

if (is_post()) {
    csrf_check();
    $date = post('published_date');
    $in = [
        'title' => mb_substr(post('title'), 0, 160),
        'excerpt' => mb_substr(post('excerpt'), 0, 300),
        'body' => mb_substr(post('body'), 0, 30000),
        'published' => isset($_POST['published']) ? 1 : 0,
        'published_at' => preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ? $date . ' 09:00:00' : $p['published_at'],
    ];
    if ($in['title'] === '') $errors[] = 'Please add a title.';
    if (!$errors) {
        try { $in['image'] = apply_image('image', $p['image']); }
        catch (RuntimeException $ex) { rollback_image_changes(); $errors[] = $ex->getMessage(); }
    }
    if (!$errors) {
        if ($p['id']) {
            $slug = $p['title'] === $in['title'] ? $p['slug'] : unique_slug('posts', $in['title'], (int)$p['id']);
            q('UPDATE posts SET title=?, slug=?, excerpt=?, body=?, image=?, published=?, published_at=? WHERE id=?',
                [$in['title'], $slug, $in['excerpt'], $in['body'], $in['image'], $in['published'], $in['published_at'], $p['id']]);
        } else {
            q('INSERT INTO posts (title, slug, excerpt, body, image, published, published_at) VALUES (?,?,?,?,?,?,?)',
                [$in['title'], unique_slug('posts', $in['title']), $in['excerpt'], $in['body'], $in['image'], $in['published'], $in['published_at']]);
        }
        commit_image_changes();
        flash('success', 'Saved “' . $in['title'] . '”.');
        redirect('admin/posts.php');
    }
    $p = array_merge($p, $in);
}

admin_header($p['id'] ? 'Edit article' : 'Write article', 'posts');
?>
<?php foreach ($errors as $er): ?><div class="flash flash-error"><?= e($er) ?></div><?php endforeach; ?>
<form method="post" enctype="multipart/form-data" class="a-form">
  <?= csrf_field() ?>
  <div class="a-card">
    <div class="a-field"><label for="title">Title *</label><input id="title" name="title" required value="<?= e($p['title']) ?>"></div>
    <div class="a-field"><label for="excerpt">Short summary</label><textarea id="excerpt" name="excerpt" rows="2" maxlength="300"><?= e($p['excerpt']) ?></textarea><p class="a-help">Shown on the Journal list and when the link is shared.</p></div>
    <div class="a-field"><label for="body">Article</label><textarea id="body" name="body" rows="14"><?= e($p['body']) ?></textarea>
      <p class="a-help">Leave an empty line between paragraphs. Wrap words in **double stars** to make them bold.</p></div>
    <?= image_input('image', 'Cover photo', $p['image']) ?>
  </div>
  <div class="a-card">
    <label class="a-check"><input type="checkbox" name="published" value="1"<?= $p['published'] ? ' checked' : '' ?>> Published (untick to save as a draft)</label>
    <div class="a-field a-narrow"><label for="published_date">Date</label><input id="published_date" name="published_date" type="date" value="<?= e(substr($p['published_at'], 0, 10)) ?>"></div>
  </div>
  <div class="a-actions">
    <button class="a-btn" type="submit">Save article</button>
    <a class="a-btn a-btn-ghost" href="<?= e(base_url('admin/posts.php')) ?>">Cancel</a>
  </div>
</form>
<?php admin_footer(); ?>
