<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("This setup tool can only be run from the command line.\n");
}

if (!extension_loaded('pdo_mysql')) {
    fwrite(STDERR, "The pdo_mysql PHP extension is required.\n");
    exit(1);
}

$name = trim((string) readline('Admin full name: '));
$email = trim((string) readline('Admin email: '));

if ($name === '' || strlen($name) > 100) {
    fwrite(STDERR, "Name must contain between 1 and 100 characters.\n");
    exit(1);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
    fwrite(STDERR, "Enter a valid email address (maximum 254 characters).\n");
    exit(1);
}

$adminPassword = (string) readline('Admin password (minimum 12 characters): ');
$passwordConfirmation = (string) readline('Confirm admin password: ');

if (strlen($adminPassword) < 12) {
    fwrite(STDERR, "Choose a password with at least 12 characters.\n");
    exit(1);
}

if (!hash_equals($adminPassword, $passwordConfirmation)) {
    fwrite(STDERR, "The passwords do not match.\n");
    exit(1);
}

try {
    require __DIR__ . '/../backend/db.php';

    $insert = $pdo->prepare(
        'INSERT INTO users (name, email, `password`, role, status)
         VALUES (:name, :email, :password, :role, :status)'
    );
    $insert->execute([
        ':name' => $name,
        ':email' => $email,
        ':password' => password_hash($adminPassword, PASSWORD_DEFAULT),
        ':role' => 'admin',
        ':status' => 'active',
    ]);

    fwrite(STDOUT, "Administrator account created successfully for {$email}.\n");
} catch (Throwable $exception) {
    if ($exception->getCode() === '23000') {
        fwrite(STDERR, "Could not create the administrator: that email address may already be registered.\n");
    } else {
        fwrite(STDERR, "Database operation failed: {$exception->getMessage()}\n");
    }
    exit(1);
} finally {
    if (isset($adminPassword)) {
        $adminPassword = '';
    }
    if (isset($passwordConfirmation)) {
        $passwordConfirmation = '';
    }
}
