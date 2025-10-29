<?php
require_once __DIR__ . '/session.php';

// Unset all session variables
$_SESSION = [];

// Delete the session cookie if set
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'] ?? '/', $params['domain'] ?? '', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on', true);
}

// Destroy the session
if (session_status() === PHP_SESSION_ACTIVE) {
    session_destroy();
}

// Redirect to login (fallback to index if login not present)
$target = file_exists(__DIR__ . '/login.php') ? 'login.php' : 'index.php';
header('Location: ' . $target);
exit;

