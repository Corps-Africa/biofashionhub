<?php
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/partials.php';

$gallery = rows('SELECT * FROM gallery ORDER BY sort, id DESC');
$activeNav = 'lookbook';
$pageTitle = 'Lookbook';
$pageDescription = 'A look at kaftans, custom outfits and accessories styled by Bio Fashion Hub.';
require __DIR__ . '/inc/header.php';
echo page_hero('Lookbook', 'Our work', 'Outfits we’ve made and styled. See something you like? Tap a photo and ask us about it.');
?>
<section class="section section-tight">
  <div class="wrap">
    <?php if ($gallery): ?>
      <div class="gallery">
        <?php foreach ($gallery as $g): ?>
          <figure>
            <button type="button" class="lightbox-open" data-src="<?= e(img($g['image'])) ?>" data-caption="<?= e($g['caption']) ?>">
              <img src="<?= e(img($g['image'])) ?>" alt="<?= e($g['caption'] ?: 'Lookbook photo') ?>" loading="lazy">
            </button>
            <?php if ($g['caption']): ?><figcaption><?= e($g['caption']) ?></figcaption><?php endif; ?>
          </figure>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="empty">New photos coming soon.</p>
    <?php endif; ?>
  </div>
</section>
<dialog class="lightbox" id="lightbox">
  <button type="button" class="lightbox-close" aria-label="Close"><?= icon('close') ?></button>
  <img src="" alt="">
  <div class="lightbox-bar">
    <p class="lightbox-caption"></p>
    <a class="btn btn-wa btn-sm lightbox-wa" href="<?= e(wa_link()) ?>" data-base="<?= e(wa_link()) ?>" target="_blank" rel="noopener"><?= icon('whatsapp') ?> Ask about this</a>
  </div>
</dialog>
<?php require __DIR__ . '/inc/footer.php'; ?>
