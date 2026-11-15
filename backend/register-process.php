<?php
require __DIR__ . '/auth.php';
questgo_require_post();
questgo_verify_csrf();

try {
    $name = questgo_text($_POST['full_name'] ?? null, 'Full name', 100);
    $email = strtolower(questgo_text($_POST['email'] ?? null, 'Email address', 254));
    $password = $_POST['password'] ?? null;
    $confirmation = $_POST['confirm_password'] ?? null;

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('Enter a valid email address.');
    }
    if (!is_string($password) || strlen($password) < 8 || strlen($password) > 4096) {
        throw new InvalidArgumentException('Choose a password with at least 8 characters.');
    }
    if (!is_string($confirmation) || !hash_equals($password, $confirmation)) {
        throw new InvalidArgumentException('The passwords do not match.');
    }
    if (($_POST['terms'] ?? '') !== '1') {
        throw new InvalidArgumentException('Please accept the terms and conditions.');
    }

    require __DIR__ . '/db.php';
    $check = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $check->execute([':email' => $email]);
    if ($check->fetch()) {
        throw new InvalidArgumentException('An account already exists for that email address.');
    }

    $insert = $pdo->prepare(
        'INSERT INTO users (name, email, `password`, role, status)
         VALUES (:name, :email, :password, :role, :status)'
    );
    $insert->execute([
        ':name' => $name,
        ':email' => $email,
        ':password' => password_hash($password, PASSWORD_DEFAULT),
        ':role' => 'user',
        ':status' => 'active',
    ]);

    questgo_flash('success', 'Your account is ready. Log in to start planning.');
    questgo_redirect('pages/login.php');
} catch (Throwable $exception) {
    questgo_action_error($exception, 'pages/register.php');
}
