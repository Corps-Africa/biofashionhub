<?php
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/partials.php';

$post = row('SELECT * FROM posts WHERE slug = ? AND published = 1', [(string)($_GET['slug'] ?? '')]);
if (!$post) { http_response_code(404); require __DIR__ . '/404.php'; exit; }

$activeNav = 'blog';
$pageTitle = $post['title'];
$pageDescription = $post['excerpt'];
$pageImage = $post['image'] ?: null;
require __DIR__ . '/inc/header.php';
?>
<article class="section">
  <div class="wrap narrow">
    <nav class="crumbs" aria-label="Breadcrumb"><a href="<?= e(base_url('blog.php')) ?>">Journal</a></nav>
    <p class="eyebrow"><?= e(date('j F Y', strtotime($post['published_at']))) ?></p>
    <h1 class="post-title"><?= e($post['title']) ?></h1>
    <?php if ($post['excerpt']): ?><p class="lead"><?= e($post['excerpt']) ?></p><?php endif; ?>
    <?php if ($post['image']): ?><img class="post-cover" src="<?= e(img($post['image'])) ?>" alt=""><?php endif; ?>
    <div class="prose prose-lg"><?= paras($post['body']) ?></div>
    <div class="post-cta">
      <p><strong>Like this look?</strong> We can help you put it together.</p>
      <a class="btn btn-wa" href="<?= e(wa_link('Hello Bio Fashion Hub, I just read "' . $post['title'] . '" and would like some help.')) ?>" target="_blank" rel="noopener"><?= icon('whatsapp') ?> Chat with us</a>
    </div>
  </div>
</article>
<?php require __DIR__ . '/inc/footer.php'; ?>
