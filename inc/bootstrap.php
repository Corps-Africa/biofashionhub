<?php
/**
 * Bio Fashion Hub — core bootstrap.
 * Loaded by every public and admin page: session, database, helpers.
 */
declare(strict_types=1);

define('ROOT', dirname(__DIR__));
define('DATA_DIR', ROOT . '/data');
define('UPLOAD_DIR', ROOT . '/uploads');
define('DB_FILE', DATA_DIR . '/site.db');
define('MAX_UPLOAD_BYTES', 6 * 1024 * 1024);

date_default_timezone_set('Africa/Accra');
error_reporting(E_ALL);
ini_set('display_errors', '0');

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('bfh_sess');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-Frame-Options: SAMEORIGIN');

/* ---------- Database ---------- */

function db(): PDO
{
    static $pdo = null;
    if ($pdo) return $pdo;
    if (!is_dir(DATA_DIR)) mkdir(DATA_DIR, 0775, true);
    $fresh = !file_exists(DB_FILE);
    $pdo = new PDO('sqlite:' . DB_FILE, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('PRAGMA foreign_keys = ON; PRAGMA journal_mode = WAL;');
    require_once __DIR__ . '/schema.php';
    migrate($pdo);
    if ($fresh) seed($pdo);
    return $pdo;
}

function q(string $sql, array $params = []): PDOStatement
{
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st;
}

function row(string $sql, array $params = []): ?array
{
    $r = q($sql, $params)->fetch();
    return $r === false ? null : $r;
}

function rows(string $sql, array $params = []): array
{
    return q($sql, $params)->fetchAll();
}

/* ---------- Settings ---------- */

function settings(): array
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        foreach (rows('SELECT key, value FROM settings') as $r) $cache[$r['key']] = $r['value'];
    }
    return $cache;
}

function setting(string $key, string $default = ''): string
{
    $s = settings();
    return isset($s[$key]) && $s[$key] !== '' ? $s[$key] : $default;
}

/* ---------- Output helpers ---------- */

function e(?string $s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Plain text -> paragraphs. Supports blank-line paragraphs, single line breaks and **bold**. */
function paras(?string $text): string
{
    $text = trim(str_replace("\r\n", "\n", (string)$text));
    if ($text === '') return '';
    $out = '';
    foreach (preg_split("/\n{2,}/", $text) as $p) {
        $p = e($p);
        $p = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $p);
        $out .= '<p>' . nl2br($p, false) . '</p>';
    }
    return $out;
}

function slugify(string $s): string
{
    $s = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $s), '-'));
    return $s !== '' ? $s : 'item';
}

function unique_slug(string $table, string $base, ?int $ignoreId = null): string
{
    $slug = slugify($base);
    $try = $slug;
    $i = 2;
    while (row("SELECT id FROM $table WHERE slug = ?" . ($ignoreId ? ' AND id != ?' : ''),
        $ignoreId ? [$try, $ignoreId] : [$try])) {
        $try = $slug . '-' . $i++;
    }
    return $try;
}

function money(?string $price): string
{
    if ($price === null || $price === '') return 'Price on request';
    return 'GH₵ ' . number_format((float)$price, (float)$price == floor((float)$price) ? 0 : 2);
}

/** Image path stored in DB -> URL relative to site root. */
function img(?string $path, string $fallback = 'assets/img/placeholder/hero.jpg'): string
{
    $path = (string)$path;
    if ($path === '') return base_url($fallback);
    return base_url($path);
}

/** Base path the site is installed under (supports installing in a subfolder). */
function base_path(): string
{
    static $base = null;
    if ($base !== null) return $base;
    $docRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: '';
    $root = realpath(ROOT) ?: ROOT;
    $base = ($docRoot && str_starts_with($root, $docRoot)) ? str_replace('\\', '/', substr($root, strlen($docRoot))) : '';
    return rtrim($base, '/');
}

function base_url(string $path = ''): string
{
    return base_path() . '/' . ltrim($path, '/');
}

function full_url(string $path = ''): string
{
    global $isHttps;
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return ($isHttps ? 'https' : 'http') . '://' . $host . base_url($path);
}

function redirect(string $path): never
{
    header('Location: ' . (preg_match('#^https?://#', $path) ? $path : base_url($path)));
    exit;
}

/* ---------- WhatsApp ---------- */

/** Normalise a Ghana number like "054 847 3309" to international "233548473309". */
function wa_number(string $raw): string
{
    $d = preg_replace('/\D+/', '', $raw);
    if (str_starts_with($d, '0')) $d = '233' . substr($d, 1);
    return $d;
}

function wa_link(string $message = ''): string
{
    $num = wa_number(setting('whatsapp_number', '0548473309'));
    return 'https://wa.me/' . $num . ($message !== '' ? '?text=' . rawurlencode($message) : '');
}

/* ---------- Flash + CSRF ---------- */

function flash(string $type, string $msg): void
{
    $_SESSION['flash'][] = [$type, $msg];
}

function flashes(): string
{
    $out = '';
    foreach ($_SESSION['flash'] ?? [] as [$type, $msg]) {
        $out .= '<div class="flash flash-' . e($type) . '" role="status">' . e($msg) . '</div>';
    }
    unset($_SESSION['flash']);
    return $out;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    if (!hash_equals(csrf_token(), (string)($_POST['csrf'] ?? ''))) {
        http_response_code(400);
        exit('Your session expired. Please go back, refresh the page and try again.');
    }
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function post(string $key, string $default = ''): string
{
    return trim((string)($_POST[$key] ?? $default));
}

/* ---------- Auth ---------- */

function current_admin(): ?array
{
    if (empty($_SESSION['admin_id'])) return null;
    return row('SELECT id, username, display_name FROM admins WHERE id = ?', [$_SESSION['admin_id']]);
}

function require_admin(): array
{
    $a = current_admin();
    if (!$a) redirect('admin/login.php');
    return $a;
}

function client_ip(): string
{
    return substr((string)($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'), 0, 45);
}

/* ---------- Uploads ---------- */

/**
 * Validate an uploaded image, resize it and re-encode it (which strips anything hidden in the file).
 * Returns the stored relative path (e.g. "uploads/2026/11/abc.jpg"), null if no file was sent.
 * Throws RuntimeException with a friendly message on failure.
 */
function handle_image_upload(string $field, int $maxDim = 1600): ?string
{
    $f = $_FILES[$field] ?? null;
    if (!$f || ($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if ($f['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('The image upload failed. Try a smaller photo.');
    if ($f['size'] > MAX_UPLOAD_BYTES) throw new RuntimeException('That photo is too large (max 6 MB).');
    if (!is_uploaded_file($f['tmp_name'])) throw new RuntimeException('Invalid upload.');

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    $info = @getimagesize($f['tmp_name']);
    $loaders = ['image/jpeg' => 'imagecreatefromjpeg', 'image/png' => 'imagecreatefrompng', 'image/webp' => 'imagecreatefromwebp'];
    if (!$info || !isset($loaders[$mime]) || !function_exists($loaders[$mime])) {
        throw new RuntimeException('Please upload a JPG, PNG or WebP photo.');
    }
    $src = @$loaders[$mime]($f['tmp_name']);
    if (!$src) throw new RuntimeException('That image could not be read.');

    // Respect phone camera orientation
    if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
        $exif = @exif_read_data($f['tmp_name']);
        $o = $exif['Orientation'] ?? 1;
        if ($o == 3) $src = imagerotate($src, 180, 0);
        elseif ($o == 6) $src = imagerotate($src, -90, 0);
        elseif ($o == 8) $src = imagerotate($src, 90, 0);
    }

    $w = imagesx($src);
    $h = imagesy($src);
    $scale = min(1, $maxDim / max($w, $h));
    $nw = (int)round($w * $scale);
    $nh = (int)round($h * $scale);
    $dst = imagecreatetruecolor($nw, $nh);
    $bg = imagecolorallocate($dst, 250, 248, 243); // flatten transparency onto off-white
    imagefill($dst, 0, 0, $bg);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);

    $sub = date('Y/m');
    $dir = UPLOAD_DIR . '/' . $sub;
    if (!is_dir($dir) && !mkdir($dir, 0775, true)) throw new RuntimeException('Upload folder is not writable.');
    $name = bin2hex(random_bytes(10)) . '.jpg';
    if (!imagejpeg($dst, $dir . '/' . $name, 84)) throw new RuntimeException('Could not save the image.');
    imagedestroy($src);
    imagedestroy($dst);
    return 'uploads/' . $sub . '/' . $name;
}

/** Delete a previously uploaded file (only inside uploads/). */
function delete_upload(?string $path): void
{
    if (!$path || !str_starts_with($path, 'uploads/')) return;
    $full = realpath(ROOT . '/' . $path);
    $up = realpath(UPLOAD_DIR);
    if ($full && $up && str_starts_with($full, $up . DIRECTORY_SEPARATOR) && is_file($full)) @unlink($full);
}

/* ---------- Enquiry notifications ---------- */

function notify_enquiry(array $data): void
{
    $to = setting('notify_email');
    if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) return;
    $body = "New enquiry from the Bio Fashion Hub website\n\n";
    foreach ($data as $k => $v) {
        if ($v !== '' && $v !== null) $body .= ucfirst(str_replace('_', ' ', $k)) . ": $v\n";
    }
    $body .= "\nView all enquiries: " . full_url('admin/enquiries.php') . "\n";
    $headers = 'From: Bio Fashion Hub Website <no-reply@' . preg_replace('/[^a-z0-9.\-]/i', '', $_SERVER['HTTP_HOST'] ?? 'localhost') . ">\r\n";
    if (!empty($data['email']) && filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $headers .= 'Reply-To: ' . $data['email'] . "\r\n";
    @mail($to, 'New website enquiry: ' . ($data['type'] ?? 'general'), $body, $headers);
}
