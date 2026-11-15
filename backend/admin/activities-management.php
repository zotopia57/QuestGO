<?php
require_once __DIR__ . '/../auth.php';
questgo_require_role('admin');
require_once __DIR__ . '/../db.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
    try {
        $statement = $pdo->query(
            'SELECT a.*, p.name AS place_name, p.city, c.category_name
             FROM activities a JOIN places p ON p.id = a.place_id
             JOIN categories c ON c.id = a.category_id
             ORDER BY p.name, a.title LIMIT 500'
        );
        questgo_json(['activities' => $statement->fetchAll()]);
    } catch (Throwable $exception) {
        questgo_action_error($exception);
    }
}

questgo_require_post();
questgo_verify_csrf();
$redirect = 'pages/admin/activities.php';
try {
    $action = questgo_text($_POST['action'] ?? null, 'Action', 20);
    if ($action === 'delete') {
        $id = questgo_id($_POST['activity_id'] ?? null, 'Activity ID');
        $delete = $pdo->prepare('DELETE FROM activities WHERE id = :id');
        $delete->execute([':id' => $id]);
        if (!$delete->rowCount()) {
            throw new InvalidArgumentException('Activity not found.');
        }
        questgo_action_success('Activity deleted.', $redirect);
    }

    $placeId = questgo_id($_POST['place_id'] ?? null, 'Destination');
    $categoryId = questgo_id($_POST['category_id'] ?? null, 'Activity category');
    $title = questgo_text($_POST['title'] ?? null, 'Activity name', 150);
    $description = questgo_text($_POST['description'] ?? null, 'Description', 10000);
    $duration = filter_var($_POST['duration_minutes'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 10080]]);
    $cost = $_POST['estimated_cost'] ?? null;
    $interests = questgo_text($_POST['interests'] ?? '', 'Interests', 255, false);
    $imageUrl = questgo_text($_POST['image_url'] ?? '', 'Photograph path', 2048, false);
    $bestTime = questgo_text($_POST['best_time'] ?? '', 'Suitable time or season', 160, false);
    if ($duration === false || !is_numeric($cost) || (float) $cost < 0 || (float) $cost > 9999999999.99) {
        throw new InvalidArgumentException('Enter a valid duration and non-negative INR cost.');
    }
    if ($imageUrl !== '' &&
        !((filter_var($imageUrl, FILTER_VALIDATE_URL) &&
            in_array(strtolower((string) parse_url($imageUrl, PHP_URL_SCHEME)), ['https', 'http'], true)) ||
            (preg_match('~^assets/images/[A-Za-z0-9_./-]+\.(?:jpg|jpeg|png|webp)$~i', $imageUrl) && !str_contains($imageUrl, '..')))) {
        throw new InvalidArgumentException('Use an HTTP(S) image URL or a project-local assets/images path.');
    }
    $placeCheck = $pdo->prepare('SELECT 1 FROM places WHERE id = :id');
    $placeCheck->execute([':id' => $placeId]);
    $categoryCheck = $pdo->prepare('SELECT 1 FROM categories WHERE id = :id AND category_type = "activity"');
    $categoryCheck->execute([':id' => $categoryId]);
    if (!$placeCheck->fetchColumn() || !$categoryCheck->fetchColumn()) {
        throw new InvalidArgumentException('Choose an existing destination and activity category.');
    }

    $values = [
        ':place_id' => $placeId,
        ':category_id' => $categoryId,
        ':title' => $title,
        ':description' => $description,
        ':duration_minutes' => $duration,
        ':estimated_cost' => number_format((float) $cost, 2, '.', ''),
        ':interests' => $interests ?: null,
        ':image_url' => $imageUrl ?: null,
        ':best_time' => $bestTime ?: null,
    ];
    if ($action === 'add') {
        $statement = $pdo->prepare(
            'INSERT INTO activities (place_id, category_id, title, description, duration_minutes,
             estimated_cost, interests, image_url, best_time)
             VALUES (:place_id, :category_id, :title, :description, :duration_minutes,
             :estimated_cost, :interests, :image_url, :best_time)'
        );
        $statement->execute($values);
        $successMessage = 'Activity added.';
    } elseif ($action === 'update') {
        $values[':id'] = questgo_id($_POST['activity_id'] ?? null, 'Activity ID');
        $statement = $pdo->prepare(
            'UPDATE activities SET place_id = :place_id, category_id = :category_id, title = :title,
             description = :description, duration_minutes = :duration_minutes,
             estimated_cost = :estimated_cost, interests = :interests, image_url = :image_url,
             best_time = :best_time WHERE id = :id'
        );
        $statement->execute($values);
        $successMessage = 'Activity updated.';
    } else {
        throw new InvalidArgumentException('Unknown activity action.');
    }
    questgo_action_success($successMessage, $redirect);
} catch (Throwable $exception) {
    questgo_action_error($exception, $redirect);
}
