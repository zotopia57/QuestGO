<?php
require_once __DIR__ . '/auth.php';
$sessionUser = questgo_current_user();
if (!$sessionUser) {
    questgo_flash('error', 'Please log in to continue.');
    questgo_redirect('pages/login.php');
}
$user = questgo_require_role($sessionUser['role']);
require_once __DIR__ . '/db.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
    questgo_json(['profile' => $user]);
}

questgo_require_post();
questgo_verify_csrf();
$redirect = $user['role'] === 'admin' ? 'pages/admin/profile.php' : 'pages/user/profile.php';

try {
    $name = questgo_text($_POST['name'] ?? null, 'Full name', 100);
    $email = strtolower(questgo_text($_POST['email'] ?? null, 'Email address', 254));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('Enter a valid email address.');
    }

    $statement = $pdo->prepare('UPDATE users SET name = :name, email = :email WHERE id = :id');
    $statement->execute([':name' => $name, ':email' => $email, ':id' => $user['id']]);
    $_SESSION['user']['name'] = $name;
    $_SESSION['user']['email'] = $email;
    questgo_action_success('Profile updated.', $redirect);
} catch (Throwable $exception) {
    questgo_action_error($exception, $redirect);
}
