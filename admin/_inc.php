<?php
require_once dirname(__DIR__) . '/inc/bootstrap.php';
require_once dirname(__DIR__) . '/inc/icons.php';
header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');

function admin_header(string $title, string $active = ''): void
{
    $admin = current_admin();
    $unread = (int)q('SELECT COUNT(*) FROM enquiries WHERE is_read = 0')->fetchColumn();
    $nav = [
        'dashboard' => ['Dashboard', 'index.php', 'check'],
        'products' => ['Products', 'products.php', 'bag'],
        'categories' => ['Categories', 'categories.php', 'kaftan'],
        'enquiries' => ['Enquiries', 'enquiries.php', 'chat'],
        'gallery' => ['Lookbook', 'gallery.php', 'instagram'],
        'testimonials' => ['Testimonials', 'testimonials.php', 'star'],
        'posts' => ['Journal / Blog', 'posts.php', 'mail'],
        'settings' => ['Site settings', 'settings.php', 'pin'],
        'account' => ['My account', 'account.php', 'phone'],
    ];
    ?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($title) ?> · Bio Fashion Hub admin</title>
<link rel="icon" type="image/png" href="<?= e(base_url('assets/img/favicon.png')) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700&family=Jost:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(base_url('assets/css/admin.css')) ?>?v=1">
</head>
<body class="admin">
<?php if ($admin): ?>
<header class="a-top">
  <button class="a-menu" type="button" aria-controls="a-side" aria-expanded="false" aria-label="Menu"><?= icon('menu') ?></button>
  <a class="a-brand" href="<?= e(base_url('admin/')) ?>"><img src="<?= e(base_url('assets/img/logo-mark.png')) ?>" alt="" width="34"> <span>Bio Fashion Hub</span></a>
  <a class="a-view" href="<?= e(base_url('index.php')) ?>" target="_blank" rel="noopener">View website ↗</a>
</header>
<div class="a-shell">
  <nav class="a-side" id="a-side" aria-label="Admin">
    <ul>
      <?php foreach ($nav as $key => [$label, $href, $ic]): ?>
        <li><a href="<?= e(base_url('admin/' . $href)) ?>"<?= $active === $key ? ' aria-current="page"' : '' ?>><?= icon($ic) ?> <?= e($label) ?>
          <?php if ($key === 'enquiries' && $unread): ?><span class="pill"><?= $unread ?></span><?php endif; ?></a></li>
      <?php endforeach; ?>
    </ul>
    <form method="post" action="<?= e(base_url('admin/logout.php')) ?>" class="a-logout">
      <?= csrf_field() ?>
      <p>Signed in as <strong><?= e($admin['display_name'] ?: $admin['username']) ?></strong></p>
      <button type="submit" class="a-btn a-btn-ghost a-btn-sm">Sign out</button>
    </form>
  </nav>
  <main class="a-main">
    <h1><?= e($title) ?></h1>
    <?= flashes() ?>
<?php else: ?>
<main class="a-auth">
<?php endif;
}

function admin_footer(): void
{
    if (current_admin()) echo '</main></div>';
    else echo '</main>';
    echo '<script src="' . e(base_url('assets/js/admin.js')) . '?v=1" defer></script></body></html>';
}

/** Shared image picker: shows current image, upload input, and optional remove checkbox. */
function image_input(string $name, string $label, string $current = '', string $help = ''): string
{
    $out = '<div class="a-field"><label for="' . e($name) . '">' . e($label) . '</label>';
    if ($current !== '') {
        $out .= '<div class="a-img-current"><img src="' . e(img($current)) . '" alt="Current image" data-preview-for="' . e($name) . '">'
            . '<label class="a-check"><input type="checkbox" name="remove_' . e($name) . '" value="1"> Remove photo</label></div>';
    } else {
        $out .= '<div class="a-img-current" hidden><img src="" alt="Preview" data-preview-for="' . e($name) . '"></div>';
    }
    $out .= '<input type="file" id="' . e($name) . '" name="' . e($name) . '" accept="image/jpeg,image/png,image/webp" data-preview>';
    $out .= '<p class="a-help">' . e($help ?: 'JPG, PNG or WebP, up to 6 MB. Photos from your phone are fine — they’re resized automatically.') . '</p></div>';
    return $out;
}

/** Old files to delete once the database save has succeeded. */
$GLOBALS['pending_deletes'] = [];

/**
 * Apply an image field from a form: new upload replaces, "remove" clears.
 * Returns the value to store. Replaced files are only deleted by commit_image_changes().
 */
function apply_image(string $name, string $current): string
{
    $new = handle_image_upload($name);
    if ($new !== null) {
        $GLOBALS['pending_deletes'][] = $current;
        $GLOBALS['new_uploads'][] = $new;
        return $new;
    }
    if (!empty($_POST['remove_' . $name])) {
        $GLOBALS['pending_deletes'][] = $current;
        return '';
    }
    return $current;
}

function commit_image_changes(): void
{
    foreach ($GLOBALS['pending_deletes'] as $f) delete_upload($f);
    $GLOBALS['pending_deletes'] = $GLOBALS['new_uploads'] = [];
}

/** Undo uploads from a failed save so no orphan files are left behind. */
function rollback_image_changes(): void
{
    foreach ($GLOBALS['new_uploads'] ?? [] as $f) delete_upload($f);
    $GLOBALS['pending_deletes'] = $GLOBALS['new_uploads'] = [];
}

function admin_icon_options(string $selected): string
{
    $out = '';
    foreach (['kaftan' => 'Kaftan', 'fabric' => 'Fabric roll', 'hat' => 'Hat / cap', 'watch' => 'Watch', 'scissors' => 'Scissors', 'bag' => 'Shopping bag', 'star' => 'Star'] as $k => $l) {
        $out .= '<option value="' . $k . '"' . ($k === $selected ? ' selected' : '') . '>' . $l . '</option>';
    }
    return $out;
}
