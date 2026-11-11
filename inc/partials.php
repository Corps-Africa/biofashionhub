<?php
require_once __DIR__ . '/icons.php';

function product_order_message(array $p): string
{
    return "Hello Bio Fashion Hub, I'd like to order:\n*" . $p['name'] . '*'
        . ($p['price'] !== null && $p['price'] !== '' ? ' (' . money($p['price']) . ')' : '')
        . "\n" . full_url('product.php?slug=' . $p['slug']);
}

function product_card(array $p): string
{
    $url = base_url('product.php?slug=' . rawurlencode($p['slug']));
    ob_start(); ?>
    <article class="card product-card">
      <a class="card-media" href="<?= e($url) ?>">
        <img src="<?= e(img($p['image'])) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
        <?php if (!$p['in_stock']): ?><span class="badge">Made to order</span><?php endif; ?>
      </a>
      <div class="card-body">
        <?php if (!empty($p['category_name'])): ?><p class="eyebrow"><?= e($p['category_name']) ?></p><?php endif; ?>
        <h3 class="card-title"><a href="<?= e($url) ?>"><?= e($p['name']) ?></a></h3>
        <p class="price"><?= e(money($p['price'])) ?></p>
        <a class="btn btn-wa btn-block" href="<?= e(wa_link(product_order_message($p))) ?>" target="_blank" rel="noopener"><?= icon('whatsapp') ?> Order on WhatsApp</a>
      </div>
    </article>
    <?php return ob_get_clean();
}

/**
 * Process the contact / custom-order form. Returns null on GET, otherwise
 * ['ok' => bool, 'errors' => [], 'old' => [...], 'wa' => link].
 */
function handle_enquiry(string $type): ?array
{
    if (!is_post()) return null;
    csrf_check();
    $old = [
        'name' => mb_substr(post('name'), 0, 120),
        'phone' => mb_substr(post('phone'), 0, 40),
        'email' => mb_substr(post('email'), 0, 160),
        'occasion' => mb_substr(post('occasion'), 0, 120),
        'preferred_date' => mb_substr(post('preferred_date'), 0, 20),
        'message' => mb_substr(post('message'), 0, 3000),
    ];
    $errors = [];
    if ($old['name'] === '') $errors['name'] = 'Please tell us your name.';
    if ($old['phone'] === '' && $old['email'] === '') $errors['phone'] = 'Please give a phone number or email so we can reply.';
    if ($old['email'] !== '' && !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'That email address doesn’t look right.';
    if ($old['phone'] !== '' && !preg_match('/^[0-9+\s()\-]{9,20}$/', $old['phone'])) $errors['phone'] = 'Please enter a valid phone number.';
    if ($old['message'] === '') $errors['message'] = 'Please add a short message.';
    if ($old['preferred_date'] !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $old['preferred_date'])) $old['preferred_date'] = '';

    $_SESSION['enq_times'] = array_filter($_SESSION['enq_times'] ?? [], fn($t) => $t > time() - 3600);
    if (count($_SESSION['enq_times']) >= 5) $errors['form'] = 'You’ve sent several messages already — please reach us on WhatsApp instead.';

    if ($errors) return ['ok' => false, 'errors' => $errors, 'old' => $old, 'wa' => wa_link()];

    // Spam traps: the hidden field must stay empty, and real people take more than 3 seconds
    // to fill in a valid form. Bots get a fake "success" and nothing is saved.
    $started = (int)($_POST['ts'] ?? 0);
    if (post('website') !== '' || ($started && time() - $started < 3)) {
        return ['ok' => true, 'errors' => [], 'old' => [], 'wa' => wa_link()];
    }

    q('INSERT INTO enquiries (type, name, phone, email, occasion, preferred_date, message) VALUES (?,?,?,?,?,?,?)',
        [$type, $old['name'], $old['phone'], $old['email'], $old['occasion'], $old['preferred_date'], $old['message']]);
    $_SESSION['enq_times'][] = time();
    notify_enquiry(['type' => $type] + $old);

    $msg = "Hello Bio Fashion Hub, I'm " . $old['name'] . '. '
        . ($type === 'custom' ? 'I just sent a custom order request' . ($old['occasion'] ? ' for ' . $old['occasion'] : '') . ' on your website.' : 'I just sent a message on your website.')
        . "\n\n" . $old['message'];
    return ['ok' => true, 'errors' => [], 'old' => [], 'wa' => wa_link($msg)];
}

function field_error(?array $result, string $key): string
{
    $err = $result['errors'][$key] ?? '';
    return $err ? '<p class="field-error" id="err-' . e($key) . '">' . e($err) . '</p>' : '';
}

function old(?array $result, string $key): string
{
    return e($result['old'][$key] ?? '');
}

function spam_fields(): string
{
    return csrf_field()
        . '<input type="hidden" name="ts" value="' . time() . '">'
        . '<div class="hp" aria-hidden="true"><label>Leave this empty <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>';
}

function page_hero(string $eyebrow, string $title, string $text = ''): string
{
    return '<section class="page-hero"><div class="wrap"><p class="eyebrow">' . e($eyebrow) . '</p><h1>' . e($title) . '</h1>'
        . ($text ? '<p class="lead">' . e($text) . '</p>' : '') . '<span class="rule" aria-hidden="true"></span></div></section>';
}
