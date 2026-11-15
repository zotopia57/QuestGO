<?php
require_once __DIR__ . '/../auth.php';
$user = questgo_require_role('user');
require_once __DIR__ . '/../db.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    http_response_code(405);
    header('Allow: GET');
    exit('Useful-stop search uses GET filters.');
}

try {
    $location = questgo_text($_GET['location'] ?? '', 'Destination', 120, false);
    $city = questgo_text($_GET['city'] ?? '', 'City', 120, false);
    $category = questgo_text($_GET['category'] ?? '', 'Category', 80, false);
    $journeyId = isset($_GET['journey_id']) && $_GET['journey_id'] !== ''
        ? questgo_id($_GET['journey_id'], 'Journey')
        : null;

    $where = [];
    $parameters = [':user_id' => (int) $user['id']];
    if ($location !== '') {
        $where[] = '(s.name LIKE :name_search OR s.city LIKE :city_search OR s.state_or_ut LIKE :state_search OR p.name LIKE :place_search)';
        $search = '%' . $location . '%';
        $parameters[':name_search'] = $search;
        $parameters[':city_search'] = $search;
        $parameters[':state_search'] = $search;
        $parameters[':place_search'] = $search;
    }
    if ($city !== '') {
        $where[] = 's.city LIKE :city_filter';
        $parameters[':city_filter'] = '%' . $city . '%';
    }
    if ($category !== '') {
        $where[] = 'sc.category_name = :category_name';
        $parameters[':category_name'] = $category;
    }
    if ($journeyId !== null) {
        $journey = $pdo->prepare(
            'SELECT start_location, destination FROM journeys WHERE id = :id AND user_id = :user_id'
        );
        $journey->execute([':id' => $journeyId, ':user_id' => (int) $user['id']]);
        $journeyDetails = $journey->fetch();
        if (!$journeyDetails) {
            throw new InvalidArgumentException('Choose one of your own journeys.');
        }
        $where[] = '(s.city LIKE :start_city OR s.city LIKE :end_city)';
        $parameters[':start_city'] = '%' . $journeyDetails['start_location'] . '%';
        $parameters[':end_city'] = '%' . $journeyDetails['destination'] . '%';
    }

    $sql = 'SELECT s.id, s.name, s.address, s.city, s.state_or_ut, s.description, s.image_url,
                   s.contact_phone, s.distance_km, s.source_url, s.source_name,
                   sc.category_name, p.name AS destination_name,
                   EXISTS(SELECT 1 FROM saved_useful_stops ss
                          WHERE ss.useful_stop_id = s.id AND ss.user_id = :user_id) AS saved
            FROM useful_stops s
            JOIN useful_stop_categories sc ON sc.id = s.category_id
            LEFT JOIN places p ON p.id = s.place_id' .
            ($where ? ' WHERE ' . implode(' AND ', $where) : '') .
            ' ORDER BY s.state_or_ut, s.city, s.name LIMIT 100';
    $statement = $pdo->prepare($sql);
    $statement->execute($parameters);

    $categories = $pdo->query('SELECT category_name FROM useful_stop_categories ORDER BY category_name')
        ->fetchAll(PDO::FETCH_COLUMN);
    questgo_json(['stops' => $statement->fetchAll(), 'categories' => $categories]);
} catch (Throwable $exception) {
    questgo_action_error($exception);
}
