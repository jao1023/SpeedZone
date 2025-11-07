<?php
require_once __DIR__ . '/session.php';

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'] ?? '/', $params['domain'] ?? '', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on', true);
}

if (session_status() === PHP_SESSION_ACTIVE) {
    session_destroy();
}

$target = file_exists(__DIR__ . '/login.php') ? 'login.php' : 'index.php';
header('Location: ' . $target);
exit;
