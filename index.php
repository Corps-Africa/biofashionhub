<?php
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/partials.php';

$activeNav = 'home';
$categories = rows('SELECT * FROM categories ORDER BY sort, name');
$featured = rows('SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id
                  WHERE p.published = 1 AND p.featured = 1 ORDER BY p.sort, p.id DESC LIMIT 8');
$testimonials = rows('SELECT * FROM testimonials WHERE published = 1 ORDER BY sort, id LIMIT 3');
$gallery = rows('SELECT * FROM gallery ORDER BY sort, id DESC LIMIT 6');
$posts = rows('SELECT * FROM posts WHERE published = 1 ORDER BY published_at DESC LIMIT 2');
$tiktok = ltrim(setting('tiktok_username'), '@');

require __DIR__ . '/inc/header.php';
?>
<section class="hero">
  <div class="wrap hero-grid">
    <div class="hero-copy">
      <p class="eyebrow"><?= e(setting('hero_eyebrow')) ?></p>
      <h1 class="display"><?= e(setting('hero_title')) ?></h1>
      <p class="tagline-caps"><?= e(setting('tagline')) ?></p>
      <span class="rule" aria-hidden="true"></span>
      <p class="lead"><?= e(setting('hero_text')) ?></p>
      <div class="btn-row">
        <a class="btn btn-navy" href="<?= e(base_url('shop.php')) ?>"><?= icon('bag') ?> Shop the collection</a>
        <a class="btn btn-outline" href="<?= e(base_url('custom-orders.php')) ?>"><?= icon('scissors') ?> Book a custom fitting</a>
      </div>
    </div>
    <div class="hero-media">
      <span class="hero-arc" aria-hidden="true"></span>
      <img src="<?= e(img(setting('hero_image'))) ?>" alt="Navy and white embroidered kaftans on display" fetchpriority="high">
    </div>
  </div>
</section>

<section class="section">
  <div class="wrap">
    <div class="section-head center">
      <p class="eyebrow">What we offer</p>
      <h2>Everything for a complete look</h2>
    </div>
    <div class="offer-grid">
      <?php foreach ($categories as $c): ?>
        <a class="offer" href="<?= e(base_url('shop.php?category=' . rawurlencode($c['slug']))) ?>">
          <span class="offer-icon"><?= icon($c['icon']) ?></span>
          <span>
            <span class="offer-title"><?= e($c['name']) ?></span>
            <span class="offer-text"><?= e($c['tagline']) ?></span>
          </span>
        </a>
      <?php endforeach; ?>
      <a class="offer" href="<?= e(base_url('custom-orders.php')) ?>">
        <span class="offer-icon"><?= icon('scissors') ?></span>
        <span>
          <span class="offer-title">Custom Outfits</span>
          <span class="offer-text">Tailored for you, your groom’s party or your occasion.</span>
        </span>
      </a>
    </div>
  </div>
</section>

<?php if ($featured): ?>
<section class="section section-tint">
  <div class="wrap">
    <div class="section-head split">
      <div>
        <p class="eyebrow">Featured pieces</p>
        <h2>Fresh from the hub</h2>
      </div>
      <a class="link-arrow" href="<?= e(base_url('shop.php')) ?>">View all products <?= icon('arrow') ?></a>
    </div>
    <div class="product-grid">
      <?php foreach ($featured as $p) echo product_card($p); ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section">
  <div class="wrap">
    <div class="section-head center">
      <p class="eyebrow">Easy ordering</p>
      <h2>Order in three simple steps</h2>
    </div>
    <ol class="steps">
      <li><span class="step-icon"><?= icon('bag') ?></span><h3>Choose</h3><p>Browse the shop and pick what you love — or tell us what you have in mind.</p></li>
      <li><span class="step-icon"><?= icon('whatsapp') ?></span><h3>Chat</h3><p>Tap “Order on WhatsApp”. Your item is already in the message — just hit send.</p></li>
      <li><span class="step-icon"><?= icon('check') ?></span><h3>Collect</h3><p>We confirm size, price and payment, then you pick up in <?= e(setting('city', 'Tamale')) ?> or we arrange delivery.</p></li>
    </ol>
  </div>
</section>

<section class="band">
  <div class="wrap band-grid">
    <div>
      <p class="eyebrow eyebrow-gold">Custom orders</p>
      <h2><?= e(setting('custom_title')) ?></h2>
      <p><?= e(setting('custom_text')) ?></p>
      <ul class="ticks">
        <li><?= icon('check') ?> Grooms &amp; wedding parties</li>
        <li><?= icon('check') ?> Matching family &amp; group outfits</li>
        <li><?= icon('check') ?> Your fabric or ours</li>
      </ul>
      <a class="btn btn-gold" href="<?= e(base_url('custom-orders.php')) ?>"><?= icon('ruler') ?> Request a fitting</a>
    </div>
    <div class="band-media">
      <img src="<?= e(base_url('assets/img/placeholder/white-kaftan.jpg')) ?>" alt="White kaftan with woven pocket detail" loading="lazy">
    </div>
  </div>
</section>

<?php if ($gallery): ?>
<section class="section">
  <div class="wrap">
    <div class="section-head split">
      <div>
        <p class="eyebrow">Lookbook</p>
        <h2>Styled by Bio Fashion Hub</h2>
      </div>
      <a class="link-arrow" href="<?= e(base_url('lookbook.php')) ?>">See the lookbook <?= icon('arrow') ?></a>
    </div>
    <div class="mosaic">
      <?php foreach ($gallery as $g): ?>
        <figure><img src="<?= e(img($g['image'])) ?>" alt="<?= e($g['caption']) ?>" loading="lazy"></figure>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($testimonials): ?>
<section class="section section-tint">
  <div class="wrap">
    <div class="section-head center">
      <p class="eyebrow">Kind words</p>
      <h2>What our customers say</h2>
    </div>
    <div class="quote-grid">
      <?php foreach ($testimonials as $t): ?>
        <figure class="quote">
          <div class="stars" aria-label="<?= (int)$t['rating'] ?> out of 5 stars"><?= str_repeat(icon('star'), max(0, min(5, (int)$t['rating']))) ?></div>
          <blockquote>“<?= e($t['quote']) ?>”</blockquote>
          <figcaption><strong><?= e($t['name']) ?></strong><?= $t['role'] ? ' · ' . e($t['role']) : '' ?></figcaption>
        </figure>
      <?php endforeach; ?>
    </div>
    <p class="center"><a class="link-arrow" href="<?= e(base_url('testimonials.php')) ?>">Read more stories <?= icon('arrow') ?></a></p>
  </div>
</section>
<?php endif; ?>

<?php if ($tiktok): ?>
<section class="section">
  <div class="wrap">
    <div class="section-head center">
      <p class="eyebrow">On TikTok</p>
      <h2>See the latest styles</h2>
    </div>
    <div class="social-embed">
      <blockquote class="tiktok-embed" cite="https://www.tiktok.com/@<?= e($tiktok) ?>" data-unique-id="<?= e($tiktok) ?>" data-embed-type="creator" style="max-width:780px;min-width:288px;">
        <section><a target="_blank" rel="noopener" href="https://www.tiktok.com/@<?= e($tiktok) ?>">@<?= e($tiktok) ?></a></section>
      </blockquote>
      <script async src="https://www.tiktok.com/embed.js"></script>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if (setting('facebook_feed') === '1' && setting('facebook_url')): ?>
<section class="section">
  <div class="wrap social-embed">
    <iframe title="Facebook updates" src="https://www.facebook.com/plugins/page.php?href=<?= e(rawurlencode(setting('facebook_url'))) ?>&tabs=timeline&width=500&height=600&small_header=true&hide_cover=false" width="500" height="600" style="border:none;overflow:hidden;max-width:100%" loading="lazy" allow="encrypted-media"></iframe>
  </div>
</section>
<?php endif; ?>

<?php if ($posts): ?>
<section class="section section-tint">
  <div class="wrap">
    <div class="section-head split">
      <div>
        <p class="eyebrow">Style journal</p>
        <h2>Tips &amp; news</h2>
      </div>
      <a class="link-arrow" href="<?= e(base_url('blog.php')) ?>">All articles <?= icon('arrow') ?></a>
    </div>
    <div class="post-grid">
      <?php foreach ($posts as $post): ?>
        <article class="card post-card">
          <a class="card-media" href="<?= e(base_url('post.php?slug=' . rawurlencode($post['slug']))) ?>"><img src="<?= e(img($post['image'])) ?>" alt="" loading="lazy"></a>
          <div class="card-body">
            <p class="eyebrow"><?= e(date('j M Y', strtotime($post['published_at']))) ?></p>
            <h3 class="card-title"><a href="<?= e(base_url('post.php?slug=' . rawurlencode($post['slug']))) ?>"><?= e($post['title']) ?></a></h3>
            <p><?= e($post['excerpt']) ?></p>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="contact-strip">
  <div class="wrap contact-strip-inner">
    <div class="cs-item"><?= icon('pin') ?><div><strong><?= e(strtoupper(setting('city', 'Tamale'))) ?></strong><span><?= e(setting('address')) ?></span></div></div>
    <div class="cs-item"><?= icon('phone') ?><div><strong><?= e(setting('phone_1')) ?></strong><span><?= e(setting('phone_2')) ?></span></div></div>
    <div class="cs-item"><?= icon('mail') ?><div><strong>Email us</strong><span><a href="mailto:<?= e(setting('email')) ?>"><?= e(setting('email')) ?></a></span></div></div>
    <a class="btn btn-navy" href="<?= e(base_url('contact.php')) ?>">Find the shop <?= icon('arrow') ?></a>
  </div>
</section>
<?php require __DIR__ . '/inc/footer.php'; ?>
