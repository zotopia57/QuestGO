<?php
require_once __DIR__ . '/../auth.php';
questgo_require_role('admin');
require_once __DIR__ . '/../db.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
    try {
        $sql = 'SELECT s.*, sc.category_name, p.name AS destination_name
                FROM useful_stops s
                JOIN useful_stop_categories sc ON sc.id = s.category_id
                LEFT JOIN places p ON p.id = s.place_id';
        $conditions = [];
        $parameters = [];
        if (!empty($_GET['q'])) {
            $search = '%' . questgo_text($_GET['q'], 'Search', 120, false) . '%';
            $conditions[] = '(s.name LIKE :name_search OR s.city LIKE :city_search OR s.state_or_ut LIKE :state_search)';
            $parameters = [':name_search' => $search, ':city_search' => $search, ':state_search' => $search];
        }
        if (!empty($_GET['category'])) {
            $conditions[] = 'sc.category_name = :category_name';
            $parameters[':category_name'] = questgo_text($_GET['category'], 'Category', 80);
        }
        $statement = $pdo->prepare(
            $sql . ($conditions ? ' WHERE ' . implode(' AND ', $conditions) : '') .
            ' ORDER BY s.state_or_ut, s.city, s.name LIMIT 500'
        );
        $statement->execute($parameters);
        $categories = $pdo->query('SELECT id, category_name FROM useful_stop_categories ORDER BY category_name')->fetchAll();
        questgo_json(['stops' => $statement->fetchAll(), 'categories' => $categories]);
    } catch (Throwable $exception) {
        questgo_action_error($exception);
    }
}

questgo_require_post();
questgo_verify_csrf();
$redirect = 'pages/admin/useful-stops.php';

try {
    $action = questgo_text($_POST['action'] ?? null, 'Action', 20);
    if ($action === 'delete') {
        $id = questgo_id($_POST['stop_id'] ?? null, 'Stop ID');
        $delete = $pdo->prepare('DELETE FROM useful_stops WHERE id = :id');
        $delete->execute([':id' => $id]);
        if (!$delete->rowCount()) {
            throw new InvalidArgumentException('Useful stop not found.');
        }
        questgo_action_success('Useful stop deleted.', $redirect);
    }

    $name = questgo_text($_POST['name'] ?? null, 'Stop name', 180);
    $address = questgo_text($_POST['address'] ?? null, 'Address', 500);
    $city = questgo_text($_POST['city'] ?? null, 'City', 120);
    $state = questgo_text($_POST['state_or_ut'] ?? null, 'State or Union Territory', 120);
    if (!in_array($state, questgo_admin_india_states(), true)) {
        throw new InvalidArgumentException('Choose a valid Indian state or Union Territory.');
    }
    $description = questgo_text($_POST['description'] ?? null, 'Description', 5000);
    $sourceUrl = questgo_text($_POST['source_url'] ?? null, 'Source URL', 2048);
    if (!filter_var($sourceUrl, FILTER_VALIDATE_URL) || strtolower((string) parse_url($sourceUrl, PHP_URL_SCHEME)) !== 'https') {
        throw new InvalidArgumentException('Provide a verified HTTPS source URL for this listing.');
    }
    $sourceName = questgo_text($_POST['source_name'] ?? null, 'Source name', 120);
    $imageUrl = questgo_text($_POST['image_url'] ?? '', 'Photograph path', 2048, false);
    questgo_validate_admin_image($imageUrl);
    $phone = questgo_text($_POST['contact_phone'] ?? '', 'Contact number', 40, false);
    if ($phone !== '' && !preg_match('/^\+?[0-9][0-9 ()-]{5,25}$/', $phone)) {
        throw new InvalidArgumentException('Enter a valid contact number or leave it blank.');
    }
    $distance = ($_POST['distance_km'] ?? '') === '' ? null : $_POST['distance_km'];
    if ($distance !== null && (!is_numeric($distance) || (float) $distance < 0 || (float) $distance > 999999.99)) {
        throw new InvalidArgumentException('Recorded distance must be a non-negative number of kilometres.');
    }
    $categoryId = questgo_id($_POST['category_id'] ?? null, 'Stop category');
    $placeId = ($_POST['place_id'] ?? '') === '' ? null : questgo_id($_POST['place_id'], 'Destination');
    $categoryCheck = $pdo->prepare('SELECT 1 FROM useful_stop_categories WHERE id = :id');
    $categoryCheck->execute([':id' => $categoryId]);
    if (!$categoryCheck->fetchColumn()) {
        throw new InvalidArgumentException('Choose a valid stop category.');
    }
    if ($placeId !== null) {
        $placeCheck = $pdo->prepare('SELECT 1 FROM places WHERE id = :id');
        $placeCheck->execute([':id' => $placeId]);
        if (!$placeCheck->fetchColumn()) {
            throw new InvalidArgumentException('Choose an existing destination or leave it blank.');
        }
    }

    $values = [
        ':category_id' => $categoryId,
        ':place_id' => $placeId,
        ':name' => $name,
        ':address' => $address,
        ':city' => $city,
        ':state_or_ut' => $state,
        ':description' => $description,
        ':image_url' => $imageUrl ?: null,
        ':contact_phone' => $phone ?: null,
        ':distance_km' => $distance,
        ':source_url' => $sourceUrl,
        ':source_name' => $sourceName,
    ];

    if ($action === 'add') {
        $statement = $pdo->prepare(
            'INSERT INTO useful_stops
             (category_id, place_id, name, address, city, state_or_ut, description, image_url,
              contact_phone, distance_km, source_url, source_name)
             VALUES (:category_id, :place_id, :name, :address, :city, :state_or_ut, :description,
              :image_url, :contact_phone, :distance_km, :source_url, :source_name)'
        );
        $statement->execute($values);
        $message = 'Useful stop added.';
    } elseif ($action === 'update') {
        $values[':id'] = questgo_id($_POST['stop_id'] ?? null, 'Stop ID');
        $statement = $pdo->prepare(
            'UPDATE useful_stops SET category_id = :category_id, place_id = :place_id, name = :name,
             address = :address, city = :city, state_or_ut = :state_or_ut, description = :description,
             image_url = :image_url, contact_phone = :contact_phone, distance_km = :distance_km,
             source_url = :source_url, source_name = :source_name WHERE id = :id'
        );
        $statement->execute($values);
        $message = 'Useful stop updated.';
    } else {
        throw new InvalidArgumentException('Unknown useful-stop action.');
    }
    questgo_action_success($message, $redirect);
} catch (Throwable $exception) {
    questgo_action_error($exception, $redirect);
}

function questgo_admin_india_states(): array
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

function questgo_validate_admin_image(string $image): void
{
    if ($image === '') {
        return;
    }
    if (filter_var($image, FILTER_VALIDATE_URL) &&
        in_array(strtolower((string) parse_url($image, PHP_URL_SCHEME)), ['https', 'http'], true)) {
        return;
    }
    if (!preg_match('~^assets/images/[A-Za-z0-9_./-]+\.(?:jpg|jpeg|png|webp)$~i', $image) || str_contains($image, '..')) {
        throw new InvalidArgumentException('Use an HTTP(S) image URL or a project-local assets/images path.');
    }
}
