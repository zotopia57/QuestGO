<?php
require_once __DIR__ . '/../auth.php';
questgo_require_role('admin');
require_once __DIR__ . '/../db.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
    try {
        $sql = 'SELECT p.*, c.category_name FROM places p JOIN categories c ON c.id = p.category_id';
        $parameters = [];
        if (!empty($_GET['q'])) {
            $sql .= ' WHERE p.name LIKE :search_name OR p.city LIKE :search_city OR p.state_or_ut LIKE :search_state';
            $search = '%' . questgo_text($_GET['q'], 'Search', 120, false) . '%';
            $parameters[':search_name'] = $search;
            $parameters[':search_city'] = $search;
            $parameters[':search_state'] = $search;
        }
        $sql .= ' ORDER BY p.name LIMIT 300';
        $statement = $pdo->prepare($sql);
        $statement->execute($parameters);
        questgo_json(['places' => $statement->fetchAll()]);
    } catch (Throwable $exception) {
        questgo_action_error($exception);
    }
}

questgo_require_post();
questgo_verify_csrf();
$redirect = 'pages/admin/places.php';
try {
    $action = questgo_text($_POST['action'] ?? null, 'Action', 20);
    if ($action === 'delete') {
        $id = questgo_id($_POST['place_id'] ?? null, 'Place ID');
        $delete = $pdo->prepare('DELETE FROM places WHERE id = :id');
        $delete->execute([':id' => $id]);
        if (!$delete->rowCount()) {
            throw new InvalidArgumentException('Destination not found.');
        }
        questgo_action_success('Destination deleted.', $redirect);
    }

    $name = questgo_text($_POST['name'] ?? null, 'Destination name', 150);
    $city = questgo_text($_POST['city'] ?? null, 'City', 120);
    $state = questgo_text($_POST['state_or_ut'] ?? null, 'State or Union Territory', 120);
    $region = questgo_text($_POST['region'] ?? null, 'Region', 20);
    if (!in_array($region, ['North', 'South', 'East', 'West', 'Central', 'Northeast'], true)) {
        throw new InvalidArgumentException('Choose a valid India region.');
    }
    if (!in_array($state, questgo_india_states(), true)) {
        throw new InvalidArgumentException('Choose a valid Indian state or Union Territory.');
    }
    $categoryId = questgo_id($_POST['category_id'] ?? null, 'Destination category');
    $description = questgo_text($_POST['description'] ?? null, 'Description', 10000);
    $imageUrl = questgo_text($_POST['image_url'] ?? '', 'Image URL', 2048, false);
    $validImageUrl = filter_var($imageUrl, FILTER_VALIDATE_URL) &&
        in_array(strtolower((string) parse_url($imageUrl, PHP_URL_SCHEME)), ['https', 'http'], true);
    $validLocalImage = preg_match('~^assets/images/[A-Za-z0-9_./-]+\.(?:jpg|jpeg|png|webp)$~i', $imageUrl) &&
        !str_contains($imageUrl, '..');
    if ($imageUrl !== '' && !$validImageUrl && !$validLocalImage) {
        throw new InvalidArgumentException('Use an HTTP(S) image URL or a project-local assets/images path.');
    }
    $cost = $_POST['estimated_cost'] ?? 0;
    if (!is_numeric($cost) || (float) $cost < 0 || (float) $cost > 9999999999.99) {
        throw new InvalidArgumentException('Enter a non-negative estimated cost in INR.');
    }
    $duration = questgo_text($_POST['suggested_duration'] ?? null, 'Suggested duration', 80);
    $distance = ($_POST['distance'] ?? '') === '' ? null : $_POST['distance'];
    if ($distance !== null && (!is_numeric($distance) || (float) $distance < 0 || (float) $distance > 999999.99)) {
        throw new InvalidArgumentException('Distance must be a non-negative number of kilometres.');
    }
    $season = questgo_text($_POST['best_visiting_season'] ?? '', 'Best visiting season', 120, false);
    $isHiddenGem = ($_POST['is_hidden_gem'] ?? '') === '1' ? 1 : 0;
    $discoveryStatus = questgo_text($_POST['discovery_status'] ?? 'lesser_known', 'Discovery status', 20);
    if (!in_array($discoveryStatus, ['lesser_known', 'established'], true)) {
        throw new InvalidArgumentException('Choose a valid discovery status.');
    }
    $thingsToDo = questgo_text($_POST['things_to_do'] ?? '', 'Things to do', 10000, false);
    $travelTips = questgo_text($_POST['travel_tips'] ?? '', 'Travel tips', 10000, false);
    $accessNotes = questgo_text($_POST['access_notes'] ?? '', 'Access and safety information', 10000, false);

    $category = $pdo->prepare('SELECT 1 FROM categories WHERE id = :id AND category_type = "destination"');
    $category->execute([':id' => $categoryId]);
    if (!$category->fetchColumn()) {
        throw new InvalidArgumentException('Choose an existing destination category.');
    }

    $values = [
        ':name' => $name, ':city' => $city, ':state_or_ut' => $state, ':region' => $region,
        ':category_id' => $categoryId, ':description' => $description, ':image_url' => $imageUrl ?: null,
        ':estimated_cost' => number_format((float) $cost, 2, '.', ''), ':suggested_duration' => $duration,
        ':distance' => $distance, ':best_visiting_season' => $season ?: null,
        ':is_hidden_gem' => $isHiddenGem,
        ':discovery_status' => $isHiddenGem ? $discoveryStatus : 'lesser_known',
        ':things_to_do' => $isHiddenGem && $thingsToDo !== '' ? $thingsToDo : null,
        ':travel_tips' => $isHiddenGem && $travelTips !== '' ? $travelTips : null,
        ':access_notes' => $isHiddenGem && $accessNotes !== '' ? $accessNotes : null,
    ];
    if ($action === 'add') {
        $statement = $pdo->prepare(
            'INSERT INTO places (name, city, state_or_ut, region, category_id, description, image_url,
             estimated_cost, suggested_duration, distance, best_visiting_season, is_hidden_gem,
             discovery_status, things_to_do, travel_tips, access_notes)
             VALUES (:name, :city, :state_or_ut, :region, :category_id, :description, :image_url,
             :estimated_cost, :suggested_duration, :distance, :best_visiting_season, :is_hidden_gem,
             :discovery_status, :things_to_do, :travel_tips, :access_notes)'
        );
        $statement->execute($values);
        $successMessage = 'Destination added.';
    } elseif ($action === 'update') {
        $values[':id'] = questgo_id($_POST['place_id'] ?? null, 'Place ID');
        $statement = $pdo->prepare(
            'UPDATE places SET name = :name, city = :city, state_or_ut = :state_or_ut, region = :region,
             category_id = :category_id, description = :description, image_url = :image_url,
             estimated_cost = :estimated_cost, suggested_duration = :suggested_duration,
             distance = :distance, best_visiting_season = :best_visiting_season, is_hidden_gem = :is_hidden_gem,
             discovery_status = :discovery_status, things_to_do = :things_to_do,
             travel_tips = :travel_tips, access_notes = :access_notes WHERE id = :id'
        );
        $statement->execute($values);
        $successMessage = 'Destination updated.';
    } else {
        throw new InvalidArgumentException('Unknown destination action.');
    }
    questgo_action_success($successMessage, $redirect);
} catch (Throwable $exception) {
    questgo_action_error($exception, $redirect);
}

function questgo_india_states(): array
{
    return [
        'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chhattisgarh', 'Goa', 'Gujarat',
        'Haryana', 'Himachal Pradesh', 'Jharkhand', 'Karnataka', 'Kerala', 'Madhya Pradesh',
        'Maharashtra', 'Manipur', 'Meghalaya', 'Mizoram', 'Nagaland', 'Odisha', 'Punjab', 'Rajasthan',
        'Sikkim', 'Tamil Nadu', 'Telangana', 'Tripura', 'Uttar Pradesh', 'Uttarakhand', 'West Bengal',
        'Andaman and Nicobar Islands', 'Chandigarh', 'Dadra and Nagar Haveli and Daman and Diu', 'Delhi',
        'Jammu and Kashmir', 'Ladakh', 'Lakshadweep', 'Puducherry',
    ];
}
