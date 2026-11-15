<?php
require_once __DIR__ . '/../auth.php';
$user = questgo_require_role('user');
require_once __DIR__ . '/../db.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
    try {
        $activities = $pdo->prepare(
            'SELECT a.id, a.title, a.description, a.duration_minutes, a.estimated_cost,
                    COALESCE(a.image_url, p.image_url) AS image_url, a.best_time,
                    p.city, p.state_or_ut, c.category_name
             FROM saved_activities sa
             JOIN activities a ON a.id = sa.activity_id
             JOIN places p ON p.id = a.place_id
             JOIN categories c ON c.id = a.category_id
             WHERE sa.user_id = :user_id
             ORDER BY sa.created_at DESC'
        );
        $activities->execute([':user_id' => (int) $user['id']]);
        $stops = $pdo->prepare(
            'SELECT s.id, s.name, s.address, s.city, s.state_or_ut, s.description, s.image_url,
                    s.contact_phone, s.distance_km, s.source_url, sc.category_name
             FROM saved_useful_stops ss
             JOIN useful_stops s ON s.id = ss.useful_stop_id
             JOIN useful_stop_categories sc ON sc.id = s.category_id
             WHERE ss.user_id = :user_id
             ORDER BY ss.created_at DESC'
        );
        $stops->execute([':user_id' => (int) $user['id']]);
        questgo_json(['activities' => $activities->fetchAll(), 'stops' => $stops->fetchAll()]);
    } catch (Throwable $exception) {
        questgo_action_error($exception);
    }
}

questgo_require_post();
questgo_verify_csrf();

try {
    $action = questgo_text($_POST['action'] ?? null, 'Action', 20);
    $type = questgo_text($_POST['item_type'] ?? null, 'Item type', 20);
    $id = questgo_id($_POST['item_id'] ?? null, 'Item ID');
    if (!in_array($type, ['activity', 'stop'], true) || !in_array($action, ['save', 'remove'], true)) {
        throw new InvalidArgumentException('Choose a valid discovery item and action.');
    }

    $table = $type === 'activity' ? 'activities' : 'useful_stops';
    $linkTable = $type === 'activity' ? 'saved_activities' : 'saved_useful_stops';
    $itemColumn = $type === 'activity' ? 'activity_id' : 'useful_stop_id';
    $exists = $pdo->prepare('SELECT 1 FROM ' . $table . ' WHERE id = :id');
    $exists->execute([':id' => $id]);
    if (!$exists->fetchColumn()) {
        throw new InvalidArgumentException('That discovery item is no longer available.');
    }

    if ($action === 'save') {
        $statement = $pdo->prepare(
            'INSERT INTO ' . $linkTable . ' (user_id, ' . $itemColumn . ')
             VALUES (:user_id, :item_id)
             ON DUPLICATE KEY UPDATE created_at = created_at'
        );
        $statement->execute([':user_id' => (int) $user['id'], ':item_id' => $id]);
        $message = 'Discovery item saved.';
    } else {
        $statement = $pdo->prepare(
            'DELETE FROM ' . $linkTable . ' WHERE user_id = :user_id AND ' . $itemColumn . ' = :item_id'
        );
        $statement->execute([':user_id' => (int) $user['id'], ':item_id' => $id]);
        if ($statement->rowCount() === 0) {
            throw new InvalidArgumentException('Saved discovery item not found.');
        }
        $message = 'Discovery item removed from your saved list.';
    }

    questgo_action_success($message, 'pages/user/saved-places.php');
} catch (Throwable $exception) {
    questgo_action_error($exception, 'pages/user/saved-places.php');
}
