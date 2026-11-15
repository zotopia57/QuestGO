<?php
require_once __DIR__ . '/../auth.php';
questgo_require_role('admin');
require_once __DIR__ . '/../db.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
    try {
        $statement = $pdo->query(
            'SELECT c.id, c.category_name, c.category_type,
             (SELECT COUNT(*) FROM places p WHERE p.category_id = c.id) AS place_count,
             (SELECT COUNT(*) FROM activities a WHERE a.category_id = c.id) AS activity_count
             FROM categories c ORDER BY c.category_type, c.category_name'
        );
        questgo_json(['categories' => $statement->fetchAll()]);
    } catch (Throwable $exception) {
        questgo_action_error($exception);
    }
}

questgo_require_post();
questgo_verify_csrf();
$redirect = 'pages/admin/categories.php';
try {
    $action = questgo_text($_POST['action'] ?? null, 'Action', 20);
    if ($action === 'delete') {
        $id = questgo_id($_POST['category_id'] ?? null, 'Category ID');
        $delete = $pdo->prepare('DELETE FROM categories WHERE id = :id');
        $delete->execute([':id' => $id]);
        if (!$delete->rowCount()) {
            throw new InvalidArgumentException('Category not found.');
        }
        questgo_action_success('Category deleted.', $redirect);
    }

    $name = questgo_text($_POST['category_name'] ?? null, 'Category name', 80);
    $type = questgo_text($_POST['category_type'] ?? null, 'Category type', 20);
    if (!in_array($type, ['destination', 'activity', 'both'], true)) {
        throw new InvalidArgumentException('Choose destination, activity or both.');
    }
    $types = $type === 'both' ? ['destination', 'activity'] : [$type];

    if ($action === 'add') {
        $pdo->beginTransaction();
        $insert = $pdo->prepare('INSERT INTO categories (category_name, category_type) VALUES (:name, :type)');
        foreach ($types as $categoryType) {
            $insert->execute([':name' => $name, ':type' => $categoryType]);
        }
        $pdo->commit();
        $successMessage = 'Category added.';
    } elseif ($action === 'update') {
        $id = questgo_id($_POST['category_id'] ?? null, 'Category ID');
        if (count($types) !== 1) {
            throw new InvalidArgumentException('Choose a single category type when editing an existing category.');
        }
        $usage = $pdo->prepare(
            'SELECT
                (SELECT COUNT(*) FROM places WHERE category_id = :place_category_id) AS place_count,
                (SELECT COUNT(*) FROM activities WHERE category_id = :activity_category_id) AS activity_count'
        );
        $usage->execute([':place_category_id' => $id, ':activity_category_id' => $id]);
        $recordUsage = $usage->fetch();
        if (($recordUsage['place_count'] > 0 && $types[0] !== 'destination') ||
            ($recordUsage['activity_count'] > 0 && $types[0] !== 'activity')) {
            throw new InvalidArgumentException('A category type cannot change while it is assigned to records.');
        }
        $update = $pdo->prepare('UPDATE categories SET category_name = :name, category_type = :type WHERE id = :id');
        $update->execute([':name' => $name, ':type' => $types[0], ':id' => $id]);
        if (!$update->rowCount()) {
            $exists = $pdo->prepare('SELECT 1 FROM categories WHERE id = :id');
            $exists->execute([':id' => $id]);
            if (!$exists->fetchColumn()) {
                throw new InvalidArgumentException('Category not found.');
            }
        }
        $successMessage = 'Category updated.';
    } else {
        throw new InvalidArgumentException('Unknown category action.');
    }
    questgo_action_success($successMessage, $redirect);
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    questgo_action_error($exception, $redirect);
}
