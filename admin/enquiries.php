<?php
require __DIR__ . '/_inc.php';
require_admin();

if (is_post()) {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    switch ($_POST['action'] ?? '') {
        case 'delete': q('DELETE FROM enquiries WHERE id = ?', [$id]); flash('success', 'Enquiry deleted.'); redirect('admin/enquiries.php');
        case 'unread': q('UPDATE enquiries SET is_read = 0 WHERE id = ?', [$id]); redirect('admin/enquiries.php');
        case 'read_all': q('UPDATE enquiries SET is_read = 1'); flash('success', 'All marked as read.'); redirect('admin/enquiries.php');
    }
    redirect('admin/enquiries.php');
}

$view = (int)($_GET['view'] ?? 0);
$one = $view ? row('SELECT * FROM enquiries WHERE id = ?', [$view]) : null;
if ($one && !$one['is_read']) q('UPDATE enquiries SET is_read = 1 WHERE id = ?', [$one['id']]);
$filter = in_array($_GET['type'] ?? '', ['custom', 'contact'], true) ? $_GET['type'] : '';
$items = rows('SELECT * FROM enquiries' . ($filter ? ' WHERE type = ?' : '') . ' ORDER BY id DESC LIMIT 300', $filter ? [$filter] : []);

admin_header('Enquiries', 'enquiries');

if ($one):
    $replyNum = wa_number($one['phone']);
    $replyMsg = 'Hello ' . $one['name'] . ', thank you for contacting Bio Fashion Hub. ';
?>
  <p><a href="<?= e(base_url('admin/enquiries.php')) ?>">← All enquiries</a></p>
  <div class="a-card">
    <div class="a-card-head"><h2><?= e($one['name']) ?></h2><span class="a-tag"><?= $one['type'] === 'custom' ? 'Custom order' : 'Contact' ?></span></div>
    <dl class="a-dl">
      <dt>Received</dt><dd><?= e(date('l j F Y, g:ia', strtotime($one['created_at'] . ' UTC'))) ?></dd>
      <?php if ($one['phone']): ?><dt>Phone</dt><dd><a href="tel:<?= e(preg_replace('/\s+/', '', $one['phone'])) ?>"><?= e($one['phone']) ?></a></dd><?php endif; ?>
      <?php if ($one['email']): ?><dt>Email</dt><dd><a href="mailto:<?= e($one['email']) ?>"><?= e($one['email']) ?></a></dd><?php endif; ?>
      <?php if ($one['occasion']): ?><dt>Occasion</dt><dd><?= e($one['occasion']) ?></dd><?php endif; ?>
      <?php if ($one['preferred_date']): ?><dt>Preferred date</dt><dd><?= e(date('l j F Y', strtotime($one['preferred_date']))) ?></dd><?php endif; ?>
    </dl>
    <div class="a-message"><?= nl2br(e($one['message'])) ?></div>
    <div class="a-actions">
      <?php if (strlen($replyNum) >= 10): ?><a class="a-btn a-btn-wa" href="https://wa.me/<?= e($replyNum) ?>?text=<?= e(rawurlencode($replyMsg)) ?>" target="_blank" rel="noopener"><?= icon('whatsapp') ?> Reply on WhatsApp</a><?php endif; ?>
      <?php if ($one['email']): ?><a class="a-btn a-btn-ghost" href="mailto:<?= e($one['email']) ?>?subject=<?= e(rawurlencode('Your enquiry with Bio Fashion Hub')) ?>">Reply by email</a><?php endif; ?>
      <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $one['id'] ?>"><input type="hidden" name="action" value="unread"><button class="a-btn a-btn-ghost">Mark unread</button></form>
      <form method="post" data-confirm="Delete this enquiry?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $one['id'] ?>"><input type="hidden" name="action" value="delete"><button class="a-btn a-btn-danger">Delete</button></form>
    </div>
  </div>
<?php else: ?>
  <div class="a-toolbar">
    <nav class="a-tabs">
      <a href="?"<?= !$filter ? ' aria-current="page"' : '' ?>>All</a>
      <a href="?type=custom"<?= $filter === 'custom' ? ' aria-current="page"' : '' ?>>Custom orders</a>
      <a href="?type=contact"<?= $filter === 'contact' ? ' aria-current="page"' : '' ?>>Contact</a>
    </nav>
    <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="read_all"><button class="a-btn a-btn-ghost a-btn-sm">Mark all read</button></form>
  </div>
  <?php if ($items): ?>
    <ul class="a-list a-card">
      <?php foreach ($items as $q): ?>
        <li class="<?= $q['is_read'] ? '' : 'unread' ?>">
          <a href="?view=<?= $q['id'] ?>">
            <strong><?= e($q['name']) ?></strong> <span class="a-tag"><?= $q['type'] === 'custom' ? 'Custom order' : 'Contact' ?></span><?= $q['is_read'] ? '' : ' <span class="a-tag a-tag-gold">New</span>' ?>
            <span class="a-sub"><?= e(mb_strimwidth($q['message'], 0, 110, '…')) ?></span>
            <span class="a-sub"><?= e($q['phone'] ?: $q['email']) ?> · <?= e(date('j M, g:ia', strtotime($q['created_at'] . ' UTC'))) ?></span>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <div class="a-card"><p class="a-help">No enquiries yet.</p></div>
  <?php endif; ?>
<?php endif; ?>
<?php admin_footer(); ?>
