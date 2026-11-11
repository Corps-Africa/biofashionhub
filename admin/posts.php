<?php
require __DIR__ . '/_inc.php';
require_admin();

if (is_post()) {
    csrf_check();
    $p = row('SELECT * FROM posts WHERE id = ?', [(int)($_POST['id'] ?? 0)]);
    if ($p && ($_POST['action'] ?? '') === 'delete') {
        delete_upload($p['image']);
        q('DELETE FROM posts WHERE id = ?', [$p['id']]);
        flash('success', 'Deleted “' . $p['title'] . '”.');
    }
    redirect('admin/posts.php');
}
$posts = rows('SELECT * FROM posts ORDER BY published_at DESC');
admin_header('Journal / Blog', 'posts');
?>
<div class="a-toolbar"><a class="a-btn" href="<?= e(base_url('admin/post-edit.php')) ?>">+ Write article</a></div>
<?php if (!$posts): ?>
  <div class="a-card"><p>No articles yet.</p></div>
<?php else: ?>
<ul class="a-items">
  <?php foreach ($posts as $p): ?>
    <li class="a-item<?= $p['published'] ? '' : ' is-hidden' ?>">
      <img src="<?= e(img($p['image'])) ?>" alt="">
      <div class="a-item-main">
        <a class="a-item-title" href="<?= e(base_url('admin/post-edit.php?id=' . $p['id'])) ?>"><?= e($p['title']) ?></a>
        <span class="a-sub"><?= e(date('j M Y', strtotime($p['published_at']))) ?></span>
        <span class="a-badges"><?= $p['published'] ? '<span class="a-tag">Published</span>' : '<span class="a-tag a-tag-muted">Draft</span>' ?></span>
      </div>
      <div class="a-item-actions">
        <a class="a-btn a-btn-sm" href="<?= e(base_url('admin/post-edit.php?id=' . $p['id'])) ?>">Edit</a>
        <form method="post" data-confirm="Delete “<?= e($p['title']) ?>”?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $p['id'] ?>"><input type="hidden" name="action" value="delete"><button class="a-btn a-btn-danger a-btn-sm">Delete</button></form>
      </div>
    </li>
  <?php endforeach; ?>
</ul>
<?php endif; ?>
<?php admin_footer(); ?>
