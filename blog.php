<?php
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/partials.php';

$posts = rows('SELECT * FROM posts WHERE published = 1 ORDER BY published_at DESC');
$activeNav = 'blog';
$pageTitle = 'Style Journal';
$pageDescription = 'Style tips, new arrivals and news from Bio Fashion Hub.';
require __DIR__ . '/inc/header.php';
echo page_hero('Journal', 'Style tips & news', 'Guides, new arrivals and updates from the hub.');
?>
<section class="section section-tight">
  <div class="wrap">
    <?php if ($posts): ?>
      <div class="post-grid">
        <?php foreach ($posts as $post): $u = base_url('post.php?slug=' . rawurlencode($post['slug'])); ?>
          <article class="card post-card">
            <a class="card-media" href="<?= e($u) ?>"><img src="<?= e(img($post['image'])) ?>" alt="" loading="lazy"></a>
            <div class="card-body">
              <p class="eyebrow"><?= e(date('j M Y', strtotime($post['published_at']))) ?></p>
              <h2 class="card-title"><a href="<?= e($u) ?>"><?= e($post['title']) ?></a></h2>
              <p><?= e($post['excerpt']) ?></p>
              <a class="link-arrow" href="<?= e($u) ?>">Read more <?= icon('arrow') ?></a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="empty">Articles coming soon.</p>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/inc/footer.php'; ?>
