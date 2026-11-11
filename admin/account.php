<?php
require __DIR__ . '/_inc.php';
$me = require_admin();

if (is_post()) {
    csrf_check();
    $action = $_POST['action'] ?? '';
    if ($action === 'password') {
        $full = row('SELECT * FROM admins WHERE id = ?', [$me['id']]);
        $new = (string)($_POST['new'] ?? '');
        if (!password_verify((string)($_POST['current'] ?? ''), $full['password_hash'])) flash('error', 'Your current password is wrong.');
        elseif (strlen($new) < 10) flash('error', 'New password must be at least 10 characters.');
        elseif ($new !== (string)($_POST['new2'] ?? '')) flash('error', 'The new passwords do not match.');
        else {
            q('UPDATE admins SET password_hash = ? WHERE id = ?', [password_hash($new, PASSWORD_DEFAULT), $me['id']]);
            session_regenerate_id(true);
            flash('success', 'Password changed.');
        }
    } elseif ($action === 'name') {
        q('UPDATE admins SET display_name = ? WHERE id = ?', [mb_substr(post('display_name'), 0, 80), $me['id']]);
        flash('success', 'Name updated.');
    } elseif ($action === 'add') {
        $u = strtolower(post('username'));
        $pw = (string)($_POST['password'] ?? '');
        if (!preg_match('/^[a-z0-9_.-]{3,40}$/', $u)) flash('error', 'Username must be 3–40 letters, numbers, dots, dashes or underscores.');
        elseif (row('SELECT id FROM admins WHERE username = ?', [$u])) flash('error', 'That username is taken.');
        elseif (strlen($pw) < 10) flash('error', 'Password must be at least 10 characters.');
        else {
            q('INSERT INTO admins (username, display_name, password_hash) VALUES (?,?,?)', [$u, mb_substr(post('display_name_new'), 0, 80), password_hash($pw, PASSWORD_DEFAULT)]);
            flash('success', "Added admin “{$u}”.");
        }
    } elseif ($action === 'remove') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id === (int)$me['id']) flash('error', 'You can’t remove yourself.');
        else { q('DELETE FROM admins WHERE id = ?', [$id]); flash('success', 'Admin removed.'); }
    }
    redirect('admin/account.php');
}

$admins = rows('SELECT id, username, display_name, created_at FROM admins ORDER BY id');
admin_header('My account', 'account');
?>
<form method="post" class="a-card a-form">
  <h2>Your name</h2>
  <?= csrf_field() ?><input type="hidden" name="action" value="name">
  <div class="a-field"><label for="display_name">Display name</label><input id="display_name" name="display_name" value="<?= e($me['display_name']) ?>"></div>
  <button class="a-btn a-btn-sm">Save</button>
</form>

<form method="post" class="a-card a-form">
  <h2>Change password</h2>
  <?= csrf_field() ?><input type="hidden" name="action" value="password">
  <input type="text" name="username" value="<?= e($me['username']) ?>" autocomplete="username" hidden>
  <div class="a-field"><label for="current">Current password</label><input id="current" name="current" type="password" autocomplete="current-password" required></div>
  <div class="a-grid-2">
    <div class="a-field"><label for="new">New password (10+ characters)</label><input id="new" name="new" type="password" autocomplete="new-password" minlength="10" required></div>
    <div class="a-field"><label for="new2">Repeat new password</label><input id="new2" name="new2" type="password" autocomplete="new-password" minlength="10" required></div>
  </div>
  <button class="a-btn a-btn-sm">Change password</button>
</form>

<section class="a-card">
  <h2>Who can sign in</h2>
  <ul class="a-items a-items-flat">
    <?php foreach ($admins as $a): ?>
      <li class="a-item">
        <div class="a-item-main"><span class="a-item-title"><?= e($a['display_name'] ?: $a['username']) ?></span><span class="a-sub">Username: <?= e($a['username']) ?></span></div>
        <?php if ($a['id'] != $me['id']): ?>
          <form method="post" data-confirm="Remove <?= e($a['username']) ?>’s access?"><?= csrf_field() ?><input type="hidden" name="action" value="remove"><input type="hidden" name="id" value="<?= $a['id'] ?>"><button class="a-btn a-btn-danger a-btn-sm">Remove</button></form>
        <?php else: ?><span class="a-tag">You</span><?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ul>
  <details class="a-details">
    <summary>Add another person</summary>
    <form method="post" class="a-form">
      <?= csrf_field() ?><input type="hidden" name="action" value="add">
      <div class="a-grid-2">
        <div class="a-field"><label for="display_name_new">Their name</label><input id="display_name_new" name="display_name_new"></div>
        <div class="a-field"><label for="nu">Username</label><input id="nu" name="username" autocapitalize="none" required></div>
      </div>
      <div class="a-field"><label for="np">Temporary password (10+ characters)</label><input id="np" name="password" type="password" autocomplete="new-password" minlength="10" required></div>
      <button class="a-btn a-btn-sm">Add admin</button>
    </form>
  </details>
</section>
<?php admin_footer(); ?>
