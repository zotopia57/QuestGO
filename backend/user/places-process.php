<?php
require_once __DIR__ . '/../auth.php';
$user = questgo_require_role('user');
require_once __DIR__ . '/../db.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
    try {
        $baseQuery = 'SELECT p.id, p.name, p.city, p.state_or_ut, p.region, p.description, p.image_url,
                             p.estimated_cost, p.suggested_duration, p.distance, p.best_visiting_season,
                             p.is_hidden_gem, p.discovery_status, p.things_to_do, p.travel_tips, p.access_notes,
                             c.category_name,
                             EXISTS(SELECT 1 FROM saved_places sp WHERE sp.place_id = p.id AND sp.user_id = :user_id) AS saved
                      FROM places p JOIN categories c ON c.id = p.category_id';
        $conditions = [];
        $parameters = [':user_id' => $user['id']];

        if (isset($_GET['id'])) {
            $conditions[] = 'p.id = :place_id';
            $parameters[':place_id'] = questgo_id($_GET['id'], 'Place ID');
        }
        if (!empty($_GET['q'])) {
            $conditions[] = '(p.name LIKE :search_name OR p.city LIKE :search_city OR p.state_or_ut LIKE :search_state)';
            $search = '%' . questgo_text($_GET['q'], 'Search', 120, false) . '%';
            $parameters[':search_name'] = $search;
            $parameters[':search_city'] = $search;
            $parameters[':search_state'] = $search;
        }
        foreach (['state' => 'p.state_or_ut', 'region' => 'p.region'] as $input => $column) {
            if (!empty($_GET[$input])) {
                $conditions[] = $column . ' = :' . $input;
                $parameters[':' . $input] = questgo_text($_GET[$input], ucfirst($input), 120);
            }
        }
        if (!empty($_GET['category'])) {
            $conditions[] = 'c.category_name = :category_name AND c.category_type = "destination"';
            $parameters[':category_name'] = questgo_text($_GET['category'], 'Category', 80);
        }
        if (!empty($_GET['hidden_gems'])) {
            $conditions[] = 'p.is_hidden_gem = 1';
        }
        if (!empty($_GET['saved'])) {
            $conditions[] = 'EXISTS(SELECT 1 FROM saved_places sp2 WHERE sp2.place_id = p.id AND sp2.user_id = :saved_user)';
            $parameters[':saved_user'] = $user['id'];
        }

        $sql = $baseQuery . ($conditions ? ' WHERE ' . implode(' AND ', $conditions) : '') . ' ORDER BY p.name LIMIT 100';
        $statement = $pdo->prepare($sql);
        $statement->execute($parameters);
        $places = $statement->fetchAll();
        if (isset($_GET['id']) && !$places) {
            questgo_json(['error' => 'Destination not found.'], 404);
        }

        if ($places) {
            $placeIds = array_values(array_unique(array_map(
                static fn(array $place): int => (int) $place['id'],
                $places
            )));
            $placeParameters = [];
            $placePlaceholders = [];
            foreach ($placeIds as $index => $placeId) {
                $placeholder = ':place_id_' . $index;
                $placePlaceholders[] = $placeholder;
                $placeParameters[$placeholder] = $placeId;
            }

            $activitiesQuery = $pdo->prepare(
                'SELECT a.place_id, a.title, a.description, a.duration_minutes, a.estimated_cost,
                        a.best_time, COALESCE(a.image_url, p.image_url) AS image_url, c.category_name
                 FROM activities a
                 JOIN places p ON p.id = a.place_id
                 JOIN categories c ON c.id = a.category_id
                 WHERE a.place_id IN (' . implode(', ', $placePlaceholders) . ')
                 ORDER BY a.duration_minutes, a.title'
            );
            $activitiesQuery->execute($placeParameters);
            $activitiesByPlace = [];
            foreach ($activitiesQuery->fetchAll() as $activity) {
                $activitiesByPlace[(int) $activity['place_id']][] = $activity;
            }
            $stopsQuery = $pdo->prepare(
                'SELECT s.id, s.place_id, s.name, s.address, s.city, s.state_or_ut, s.description,
                        s.source_url, sc.category_name,
                        EXISTS(SELECT 1 FROM saved_useful_stops ss
                               WHERE ss.useful_stop_id = s.id AND ss.user_id = :saved_user_id) AS saved
                 FROM useful_stops s
                 JOIN useful_stop_categories sc ON sc.id = s.category_id
                 WHERE s.place_id IN (' . implode(', ', $placePlaceholders) . ')
                 ORDER BY s.name'
            );
            $stopParameters = $placeParameters;
            $stopParameters[':saved_user_id'] = (int) $user['id'];
            $stopsQuery->execute($stopParameters);
            $stopsByPlace = [];
            foreach ($stopsQuery->fetchAll() as $stop) {
                $stopsByPlace[(int) $stop['place_id']][] = $stop;
            }
            foreach ($places as &$place) {
                $place['activities'] = $activitiesByPlace[(int) $place['id']] ?? [];
                $place['useful_stops'] = $stopsByPlace[(int) $place['id']] ?? [];
            }
            unset($place);
        }

        questgo_json(['places' => $places]);
    } catch (Throwable $exception) {
        questgo_action_error($exception);
    }
}

questgo_require_post();
questgo_verify_csrf();
try {
    $action = questgo_text($_POST['action'] ?? null, 'Action', 20);
    $placeId = questgo_id($_POST['place_id'] ?? null, 'Place ID');

    if ($action === 'save') {
        $statement = $pdo->prepare('INSERT INTO saved_places (user_id, place_id) VALUES (:user_id, :place_id)');
        $statement->execute([':user_id' => $user['id'], ':place_id' => $placeId]);
    } elseif ($action === 'remove') {
        $statement = $pdo->prepare('DELETE FROM saved_places WHERE user_id = :user_id AND place_id = :place_id');
        $statement->execute([':user_id' => $user['id'], ':place_id' => $placeId]);
        if ($statement->rowCount() === 0) {
            throw new InvalidArgumentException('Saved place not found.');
        }
    } else {
        throw new InvalidArgumentException('Unknown saved-place action.');
    }

    questgo_action_success(
        $action === 'save' ? 'Place saved to your list.' : 'Place removed from your saved list.',
        'pages/user/saved-places.php'
    );
} catch (Throwable $exception) {
    questgo_action_error($exception, 'pages/user/explore.php');
}
