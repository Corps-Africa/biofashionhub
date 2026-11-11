<?php
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/partials.php';

$items = rows('SELECT * FROM testimonials WHERE published = 1 ORDER BY sort, id');
$pageTitle = 'Testimonials';
$pageDescription = 'What customers say about Bio Fashion Hub.';
require __DIR__ . '/inc/header.php';
echo page_hero('Testimonials', 'Customer stories', 'Real words from the people we’ve dressed.');
?>
<section class="section section-tight">
  <div class="wrap">
    <?php if ($items): ?>
      <div class="quote-grid">
        <?php foreach ($items as $t): ?>
          <figure class="quote">
            <?php if ($t['image']): ?><img class="quote-photo" src="<?= e(img($t['image'])) ?>" alt="" loading="lazy"><?php endif; ?>
            <div class="stars" aria-label="<?= (int)$t['rating'] ?> out of 5 stars"><?= str_repeat(icon('star'), max(0, min(5, (int)$t['rating']))) ?></div>
            <blockquote>“<?= e($t['quote']) ?>”</blockquote>
            <figcaption><strong><?= e($t['name']) ?></strong><?= $t['role'] ? ' · ' . e($t['role']) : '' ?></figcaption>
          </figure>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="empty">
        <p>Customer stories are coming soon.</p>
        <p>Bought from us? We’d love to hear how it went.</p>
      </div>
    <?php endif; ?>
    <div class="center" style="margin-top:2.5rem">
      <a class="btn btn-wa" href="<?= e(wa_link('Hello Bio Fashion Hub, I would like to share my experience:')) ?>" target="_blank" rel="noopener"><?= icon('whatsapp') ?> Share your experience</a>
    </div>
  </div>
</section>
<?php require __DIR__ . '/inc/footer.php'; ?>
