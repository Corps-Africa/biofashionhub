<?php
require __DIR__ . '/_inc.php';

// Only available until the first admin account exists.
if ((int)q('SELECT COUNT(*) FROM admins')->fetchColumn() > 0) redirect('admin/login.php');

$errors = [];
if (is_post()) {
    csrf_check();
    $username = strtolower(post('username'));
    $display = post('display_name');
    $pw = (string)($_POST['password'] ?? '');
    if (!preg_match('/^[a-z0-9_.-]{3,40}$/', $username)) $errors[] = 'Username must be 3–40 letters, numbers, dots, dashes or underscores.';
    if (strlen($pw) < 10) $errors[] = 'Password must be at least 10 characters.';
    if ($pw !== (string)($_POST['password2'] ?? '')) $errors[] = 'The two passwords do not match.';
    if (!$errors) {
        q('INSERT INTO admins (username, display_name, password_hash) VALUES (?,?,?)', [$username, $display, password_hash($pw, PASSWORD_DEFAULT)]);
        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int)db()->lastInsertId();
        flash('success', 'Welcome! Your admin account is ready.');
        redirect('admin/index.php');
    }
}
admin_header('Set up your admin account');
?>
<div class="a-card a-auth-card">
  <img class="a-auth-logo" src="<?= e(base_url('assets/img/logo-mark.png')) ?>" alt="Bio Fashion Hub" width="90">
  <h1>Create your admin account</h1>
  <p class="a-help">This page only works once. After this, use your username and password to sign in.</p>
  <?php foreach ($errors as $er): ?><div class="flash flash-error"><?= e($er) ?></div><?php endforeach; ?>
  <form method="post">
    <?= csrf_field() ?>
    <div class="a-field"><label for="display_name">Your name</label><input id="display_name" name="display_name" value="<?= e(post('display_name', 'Sumaila Abdulai')) ?>"></div>
    <div class="a-field"><label for="username">Username</label><input id="username" name="username" autocomplete="username" autocapitalize="none" required value="<?= e(post('username')) ?>"></div>
    <div class="a-field"><label for="password">Password (at least 10 characters)</label><input id="password" name="password" type="password" autocomplete="new-password" required minlength="10"></div>
    <div class="a-field"><label for="password2">Repeat password</label><input id="password2" name="password2" type="password" autocomplete="new-password" required minlength="10"></div>
    <button class="a-btn a-btn-block" type="submit">Create account</button>
  </form>
</div>
<?php admin_footer(); ?>
