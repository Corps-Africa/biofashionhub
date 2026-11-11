<?php
require __DIR__ . '/_inc.php';

if ((int)q('SELECT COUNT(*) FROM admins')->fetchColumn() === 0) redirect('admin/setup.php');
if (current_admin()) redirect('admin/index.php');

const MAX_ATTEMPTS = 5;
const WINDOW_SECONDS = 900;

$error = '';
$ip = client_ip();
q('DELETE FROM login_attempts WHERE attempted_at < ?', [time() - WINDOW_SECONDS]);
$attempts = (int)q('SELECT COUNT(*) FROM login_attempts WHERE ip = ?', [$ip])->fetchColumn();

if (is_post()) {
    csrf_check();
    if ($attempts >= MAX_ATTEMPTS) {
        $error = 'Too many attempts. Please wait 15 minutes and try again.';
    } else {
        $user = row('SELECT * FROM admins WHERE username = ?', [strtolower(post('username'))]);
        $pw = (string)($_POST['password'] ?? '');
        if ($user && password_verify($pw, $user['password_hash'])) {
            if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
                q('UPDATE admins SET password_hash = ? WHERE id = ?', [password_hash($pw, PASSWORD_DEFAULT), $user['id']]);
            }
            q('DELETE FROM login_attempts WHERE ip = ?', [$ip]);
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int)$user['id'];
            redirect('admin/index.php');
        }
        q('INSERT INTO login_attempts (ip, attempted_at) VALUES (?, ?)', [$ip, time()]);
        usleep(400000);
        $error = 'Wrong username or password.';
    }
}
admin_header('Sign in');
?>
<div class="a-card a-auth-card">
  <img class="a-auth-logo" src="<?= e(base_url('assets/img/logo-mark.png')) ?>" alt="Bio Fashion Hub" width="90">
  <h1>Admin sign in</h1>
  <?php if ($error): ?><div class="flash flash-error" role="alert"><?= e($error) ?></div><?php endif; ?>
  <form method="post">
    <?= csrf_field() ?>
    <div class="a-field"><label for="username">Username</label><input id="username" name="username" autocomplete="username" autocapitalize="none" required autofocus value="<?= e(post('username')) ?>"></div>
    <div class="a-field"><label for="password">Password</label><input id="password" name="password" type="password" autocomplete="current-password" required></div>
    <button class="a-btn a-btn-block" type="submit">Sign in</button>
  </form>
  <p class="a-help center"><a href="<?= e(base_url('index.php')) ?>">← Back to website</a></p>
</div>
<?php admin_footer(); ?>
