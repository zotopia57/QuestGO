<?php
require_once __DIR__ . '/auth.php';
$sessionUser = questgo_current_user();
if (!$sessionUser) {
    questgo_flash('error', 'Please log in to continue.');
    questgo_redirect('pages/login.php');
}
$user = questgo_require_role($sessionUser['role']);
require_once __DIR__ . '/db.php';
questgo_require_post();
questgo_verify_csrf();
$redirect = $user['role'] === 'admin' ? 'pages/admin/profile.php' : 'pages/user/change-password.php';

try {
    $currentPassword = $_POST['current_password'] ?? null;
    $newPassword = $_POST['new_password'] ?? null;
    $confirmation = $_POST['confirm_password'] ?? null;
    if (!is_string($currentPassword) || !is_string($newPassword) || !is_string($confirmation)) {
        throw new InvalidArgumentException('Complete all password fields.');
    }
    if (strlen($newPassword) < 8 || strlen($newPassword) > 4096) {
        throw new InvalidArgumentException('Choose a password with at least 8 characters.');
    }
    if (!hash_equals($newPassword, $confirmation)) {
        throw new InvalidArgumentException('The new passwords do not match.');
    }

    $statement = $pdo->prepare('SELECT `password` FROM users WHERE id = :id');
    $statement->execute([':id' => $user['id']]);
    $storedPassword = $statement->fetchColumn();
    if (!is_string($storedPassword) || !password_verify($currentPassword, $storedPassword)) {
        throw new InvalidArgumentException('The current password is incorrect.');
    }

    $update = $pdo->prepare('UPDATE users SET `password` = :password WHERE id = :id');
    $update->execute([
        ':password' => password_hash($newPassword, PASSWORD_DEFAULT),
        ':id' => $user['id'],
    ]);
    session_regenerate_id(true);
    unset($_SESSION['csrf_token']);
    questgo_flash('success', 'Password changed successfully.');
    questgo_redirect($redirect);
} catch (Throwable $exception) {
    questgo_action_error($exception, $redirect);
}
