<?php
require __DIR__ . '/auth.php';
questgo_require_post();
questgo_verify_csrf();

$_SESSION = [];
$cookie = session_get_cookie_params();
if (ini_get('session.use_cookies')) {
    setcookie(session_name(), '', [
        'expires' => time() - 42000,
        'path' => $cookie['path'],
        'domain' => $cookie['domain'],
        'secure' => $cookie['secure'],
        'httponly' => $cookie['httponly'],
        'samesite' => $cookie['samesite'] ?? 'Lax',
    ]);
}
session_destroy();

$base = rtrim(dirname(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/')), '/');
$base = preg_replace('~/(backend(?:/(?:user|admin))?|pages(?:/(?:user|admin))?)$~', '', $base);
header('Location: ' . rtrim((string) $base, '/') . '/pages/login.php', true, 303);
exit;
