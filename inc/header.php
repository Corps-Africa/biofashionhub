<?php
/**
 * Public page header. Before including, a page may set:
 *   $pageTitle, $pageDescription, $pageImage, $activeNav
 */
require_once __DIR__ . '/icons.php';
$bizName = setting('business_name', 'Bio Fashion Hub');
$pageTitle = isset($pageTitle) ? $pageTitle . ' | ' . $bizName : $bizName . ' — ' . setting('tagline');
$pageDescription = $pageDescription ?? 'Quality kaftans, fabrics, Hausa caps, wristwatches and custom outfits in ' . setting('city', 'Tamale') . '. Order easily on WhatsApp.';
$pageImage = $pageImage ?? setting('hero_image', 'assets/img/placeholder/hero.jpg');
$activeNav = $activeNav ?? '';
$nav = [
    'home' => ['Home', 'index.php'],
    'shop' => ['Shop', 'shop.php'],
    'custom' => ['Custom Orders', 'custom-orders.php'],
    'lookbook' => ['Lookbook', 'lookbook.php'],
    'about' => ['About', 'about.php'],
    'blog' => ['Journal', 'blog.php'],
    'contact' => ['Contact', 'contact.php'],
];
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?></title>
<meta name="description" content="<?= e($pageDescription) ?>">
<meta name="theme-color" content="#071938">
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= e($bizName) ?>">
<meta property="og:title" content="<?= e($pageTitle) ?>">
<meta property="og:description" content="<?= e($pageDescription) ?>">
<meta property="og:image" content="<?= e(full_url($pageImage)) ?>">
<link rel="icon" type="image/png" href="<?= e(base_url('assets/img/favicon.png')) ?>">
<link rel="apple-touch-icon" href="<?= e(base_url('assets/img/favicon.png')) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bodoni+Moda:ital,opsz,wght@0,6..96,400..700;1,6..96,400..600&family=Montserrat:wght@500;600;700&family=Jost:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(base_url('assets/css/site.css')) ?>?v=1">
<script type="application/ld+json"><?= json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'ClothingStore',
    'name' => $bizName,
    'slogan' => setting('tagline'),
    'telephone' => setting('phone_1'),
    'email' => setting('email'),
    'address' => ['@type' => 'PostalAddress', 'streetAddress' => setting('address'), 'addressLocality' => setting('city'), 'addressCountry' => 'GH'],
    'url' => full_url(),
    'image' => full_url($pageImage),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?></script>
</head>
<body>
<a class="skip" href="#main">Skip to content</a>
<header class="site-header">
  <div class="wrap header-inner">
    <a class="brand" href="<?= e(base_url('index.php')) ?>" aria-label="<?= e($bizName) ?> home">
      <img src="<?= e(base_url('assets/img/logo-mark.png')) ?>" alt="" width="54" height="57">
      <span class="brand-word"><span>Bio Fashion</span><em>Hub</em></span>
    </a>
    <nav class="main-nav" id="main-nav" aria-label="Main">
      <ul>
        <?php foreach ($nav as $key => [$label, $href]): ?>
          <li><a href="<?= e(base_url($href)) ?>"<?= $activeNav === $key ? ' aria-current="page"' : '' ?>><?= e($label) ?></a></li>
        <?php endforeach; ?>
      </ul>
      <a class="btn btn-gold nav-cta" href="<?= e(wa_link('Hello ' . $bizName . ', I would like to make an enquiry.')) ?>" target="_blank" rel="noopener"><?= icon('whatsapp') ?> Chat to order</a>
    </nav>
    <button class="nav-toggle" type="button" aria-controls="main-nav" aria-expanded="false" aria-label="Open menu"><?= icon('menu', 'icon i-open') ?><?= icon('close', 'icon i-close') ?></button>
  </div>
</header>
<main id="main">
