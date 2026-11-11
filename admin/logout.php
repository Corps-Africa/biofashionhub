<?php
require __DIR__ . '/_inc.php';
if (is_post()) {
    csrf_check();
    $_SESSION = [];
    session_regenerate_id(true);
    session_destroy();
}
redirect('admin/login.php');
