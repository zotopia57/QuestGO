<?php
require_once __DIR__ . '/../auth.php';
$user = questgo_require_role('user');
require_once __DIR__ . '/../db.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    http_response_code(405);
    header('Allow: GET');
    exit('Activity matching uses GET filters.');
}

try {
    $location = questgo_text($_GET['location'] ?? '', 'Location', 120, false);
    $minutes = filter_var($_GET['minutes'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 10080]]);
    $budget = $_GET['budget'] ?? 0;
    if ($minutes === false || !is_numeric($budget) || (float) $budget < 0 || (float) $budget > 9999999999.99) {
        throw new InvalidArgumentException('Enter valid available time and INR budget values.');
    }

    $sql = 'SELECT a.id, a.title, a.description, a.duration_minutes, a.estimated_cost, a.interests,
                   COALESCE(a.image_url, p.image_url) AS image_url, a.best_time,
                   EXISTS(SELECT 1 FROM saved_activities sa
                          WHERE sa.activity_id = a.id AND sa.user_id = :saved_user_id) AS saved,
                   p.id AS place_id, p.name AS place_name, p.city, p.state_or_ut,
                   c.category_name
            FROM activities a
            JOIN places p ON p.id = a.place_id
            JOIN categories c ON c.id = a.category_id
            WHERE a.duration_minutes <= :minutes
              AND a.estimated_cost <= :budget';
    $parameters = [
        ':minutes' => $minutes,
        ':budget' => number_format((float) $budget, 2, '.', ''),
        ':saved_user_id' => (int) $user['id'],
    ];

    if ($location !== '') {
        $sql .= ' AND (p.city LIKE :city_location OR p.name LIKE :place_location OR p.state_or_ut LIKE :state_location)';
        $searchLocation = '%' . $location . '%';
        $parameters[':city_location'] = $searchLocation;
        $parameters[':place_location'] = $searchLocation;
        $parameters[':state_location'] = $searchLocation;
    }
    if (!empty($_GET['category'])) {
        $sql .= ' AND c.category_name = :category';
        $parameters[':category'] = questgo_text($_GET['category'], 'Category', 80);
    }
    $interests = [];
    if (!empty($_GET['interests'])) {
        $rawInterests = is_array($_GET['interests']) ? $_GET['interests'] : explode(',', (string) $_GET['interests']);
        foreach (array_slice($rawInterests, 0, 10) as $interest) {
            if (is_string($interest) && trim($interest) !== '') {
                $interests[] = mb_strtolower(questgo_text(trim($interest), 'Interest', 40));
            }
        }
    }
    if ($interests) {
        $interestConditions = [];
        foreach ($interests as $index => $interest) {
            $key = ':interest_' . $index;
            $interestConditions[] = 'LOWER(COALESCE(a.interests, "")) LIKE ' . $key;
            $parameters[$key] = '%' . $interest . '%';
        }
        $sql .= ' AND (' . implode(' OR ', $interestConditions) . ')';
    }

    $sql .= ' ORDER BY a.duration_minutes ASC, a.estimated_cost ASC LIMIT 50';
    $statement = $pdo->prepare($sql);
    $statement->execute($parameters);
    questgo_json(['activities' => $statement->fetchAll()]);
} catch (Throwable $exception) {
    questgo_action_error($exception);
}
