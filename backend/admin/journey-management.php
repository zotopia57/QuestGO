<?php
require_once __DIR__ . '/../auth.php';
questgo_require_role('admin');
require_once __DIR__ . '/../db.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    http_response_code(405);
    header('Allow: GET');
    exit('This endpoint is read-only.');
}

try {
    $statement = $pdo->query(
        'SELECT j.id, j.title, j.start_location, j.destination, j.departure_date, j.return_date,
                j.travellers, j.estimated_budget, j.status, u.name AS user_name, u.email AS user_email
         FROM journeys j JOIN users u ON u.id = j.user_id
         ORDER BY j.created_at DESC LIMIT 500'
    );
    questgo_json(['journeys' => $statement->fetchAll()]);
} catch (Throwable $exception) {
    questgo_action_error($exception);
}
