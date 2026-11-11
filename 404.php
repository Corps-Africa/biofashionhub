<?php
require_once __DIR__ . '/inc/bootstrap.php';
require_once __DIR__ . '/inc/partials.php';
if (!headers_sent()) http_response_code(404);
$pageTitle = 'Page not found';
require __DIR__ . '/inc/header.php';
?>
<section class="section">
  <div class="wrap narrow center">
    <p class="eyebrow">404</p>
    <h1>We couldn’t find that page</h1>
    <p class="lead">It may have moved or sold out. Try the shop, or ask us directly.</p>
    <div class="btn-row center-row">
      <a class="btn btn-navy" href="<?= e(base_url('shop.php')) ?>">Go to shop</a>
      <a class="btn btn-wa" href="<?= e(wa_link('Hello Bio Fashion Hub!')) ?>" target="_blank" rel="noopener"><?= icon('whatsapp') ?> Ask on WhatsApp</a>
    </div>
  </div>
</section>
<?php require __DIR__ . '/inc/footer.php'; ?>
