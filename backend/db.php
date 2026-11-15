<?php
$dbHost = 'localhost';
$dbPort = 3306;
$dbName = 'questgo';
$dbUser = 'root';
$dbPassword = '';

if (!extension_loaded('pdo_mysql')) {
    throw new RuntimeException('The pdo_mysql PHP extension is required to connect to QuestGo.');
}

$dsn = sprintf(
    'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
    $dbHost,
    $dbPort,
    $dbName
);

try {
    $pdo = new PDO($dsn, $dbUser, $dbPassword, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $exception) {
    error_log('QuestGo database connection failed: ' . $exception->getMessage());
    throw new RuntimeException('QuestGo could not connect to its database. Confirm MySQL is running and import database/questgo.sql.', 0, $exception);
}
