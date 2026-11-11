<?php
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/partials.php';

$activeNav = 'about';
$pageTitle = 'About Us';
$pageDescription = setting('about_intro');
require __DIR__ . '/inc/header.php';
echo page_hero('About us', 'Look good. Feel confident.', setting('about_intro'));
?>
<section class="section">
  <div class="wrap about-grid">
    <div class="about-media"><img src="<?= e(img(setting('about_image'))) ?>" alt="Bio Fashion Hub kaftan" loading="lazy"></div>
    <div>
      <p class="eyebrow">Our story</p>
      <h2>Fashion that builds confidence</h2>
      <div class="prose"><?= paras(setting('about_story')) ?></div>
      <div class="mission">
        <p class="eyebrow eyebrow-gold">Our mission</p>
        <p><?= e(setting('about_mission')) ?></p>
      </div>
    </div>
  </div>
</section>

<section class="section section-tint">
  <div class="wrap">
    <div class="section-head center"><p class="eyebrow">What we stand for</p><h2>Our promise to you</h2></div>
    <div class="values">
      <div><span class="offer-icon"><?= icon('fabric') ?></span><h3>Quality</h3><p>Carefully selected fabrics and finishing that last.</p></div>
      <div><span class="offer-icon"><?= icon('kaftan') ?></span><h3>Style</h3><p>Classic and modern looks for every occasion.</p></div>
      <div><span class="offer-icon"><?= icon('check') ?></span><h3>Affordability</h3><p>Elegant fashion at prices that make sense.</p></div>
      <div><span class="offer-icon"><?= icon('scissors') ?></span><h3>Personal</h3><p>Custom outfits made to fit you and your moment.</p></div>
    </div>
  </div>
</section>

<section class="section">
  <div class="wrap founder">
    <?php if (setting('ceo_photo')): ?>
      <img class="founder-photo" src="<?= e(img(setting('ceo_photo'))) ?>" alt="<?= e(setting('ceo_name')) ?>" loading="lazy">
    <?php else: ?>
      <span class="founder-photo founder-initials" aria-hidden="true"><?= e(implode('', array_map(fn($w) => mb_substr($w, 0, 1), array_slice(explode(' ', setting('ceo_name')), 0, 2)))) ?></span>
    <?php endif; ?>
    <div>
      <p class="eyebrow">Meet the founder</p>
      <blockquote class="founder-quote">“<?= e(setting('ceo_bio')) ?>”</blockquote>
      <p><strong><?= e(setting('ceo_name')) ?></strong> · <?= e(setting('ceo_role')) ?></p>
    </div>
  </div>
</section>

<section class="band band-slim">
  <div class="wrap band-cta">
    <h2>Ready to find your look?</h2>
    <div class="btn-row">
      <a class="btn btn-gold" href="<?= e(base_url('shop.php')) ?>">Shop now</a>
      <a class="btn btn-outline-light" href="<?= e(wa_link('Hello Bio Fashion Hub!')) ?>" target="_blank" rel="noopener"><?= icon('whatsapp') ?> Chat with us</a>
    </div>
  </div>
</section>
<?php require __DIR__ . '/inc/footer.php'; ?>
