<?php
require __DIR__ . '/auth.php';
questgo_require_post();
questgo_verify_csrf();

$emailInput = $_POST['email'] ?? '';
$email = is_string($emailInput) ? strtolower(trim($emailInput)) : '';
$password = $_POST['password'] ?? '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254 || !is_string($password) || $password === '') {
    questgo_flash('error', 'Enter a valid email address and password.');
    questgo_redirect('pages/login.php');
}

try {
    require __DIR__ . '/db.php';
    $statement = $pdo->prepare('SELECT id, name, email, `password`, role, status FROM users WHERE email = :email LIMIT 1');
    $statement->execute([':email' => $email]);
    $user = $statement->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        questgo_flash('error', 'The email or password is incorrect.');
        questgo_redirect('pages/login.php');
    }

    if ($user['status'] !== 'active') {
        questgo_flash('error', 'This account is inactive. Please contact QuestGo support.');
        questgo_redirect('pages/login.php');
    }

    $dashboardByRole = [
        'admin' => 'pages/admin/dashboard.php',
        'user' => 'pages/user/dashboard.php',
    ];
    $dashboardPath = $dashboardByRole[$user['role']] ?? null;
    if ($dashboardPath === null) {
        questgo_flash('error', 'This account has an invalid role. Please contact QuestGo support.');
        questgo_redirect('pages/login.php');
    }

    session_regenerate_id(true);
    unset($user['password'], $_SESSION['csrf_token']);
    $_SESSION['user'] = [
        'id' => (int) $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role'],
    ];
    if (($_POST['remember'] ?? '') === 'on') {
        $_SESSION['remember_me'] = true;
        $cookie = session_get_cookie_params();
        setcookie(session_name(), session_id(), [
            'expires' => time() + (60 * 60 * 24 * 30),
            'path' => $cookie['path'],
            'domain' => $cookie['domain'],
            'secure' => $cookie['secure'],
            'httponly' => $cookie['httponly'],
            'samesite' => $cookie['samesite'] ?? 'Lax',
        ]);
    } else {
        unset($_SESSION['remember_me']);
    }

    questgo_redirect($dashboardPath);
} catch (Throwable $exception) {
    questgo_action_error($exception, 'pages/login.php');
}
