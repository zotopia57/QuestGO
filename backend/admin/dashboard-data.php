<?php
require_once __DIR__ . '/../auth.php';
questgo_require_role('admin');
require_once __DIR__ . '/../db.php';

try {
    $counts = ['users' => (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn()];
    foreach (['places', 'activities', 'journeys'] as $table) {
        $counts[$table] = (int) $pdo->query('SELECT COUNT(*) FROM ' . $table)->fetchColumn();
    }

    $recent = $pdo->query(
        'SELECT name, email, status, created_at FROM users WHERE role = "user" ORDER BY created_at DESC LIMIT 5'
    )->fetchAll();
    $regions = $pdo->query(
        'SELECT region, COUNT(*) AS place_count FROM places GROUP BY region ORDER BY region'
    )->fetchAll();
    $categories = $pdo->query(
        'SELECT c.category_name, COUNT(p.id) AS place_count
         FROM categories c LEFT JOIN places p ON p.category_id = c.id
         WHERE c.category_type = "destination"
         GROUP BY c.id ORDER BY place_count DESC, c.category_name LIMIT 8'
    )->fetchAll();

    questgo_json([
        'counts' => $counts,
        'recent_users' => $recent,
        'regions' => $regions,
        'categories' => $categories,
    ]);
} catch (Throwable $exception) {
    questgo_action_error($exception);
}
