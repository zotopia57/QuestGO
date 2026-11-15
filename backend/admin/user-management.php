<?php
require_once __DIR__ . '/../auth.php';
$admin = questgo_require_role('admin');
require_once __DIR__ . '/../db.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
    try {
        $sql = 'SELECT u.id, u.name, u.email, u.role, u.status, u.created_at,
                       COUNT(DISTINCT j.id) AS journey_count
                FROM users u LEFT JOIN journeys j ON j.user_id = u.id';
        $parameters = [];
        if (!empty($_GET['q'])) {
            $sql .= ' WHERE u.name LIKE :search_name OR u.email LIKE :search_email';
            $search = '%' . questgo_text($_GET['q'], 'Search', 120, false) . '%';
            $parameters[':search_name'] = $search;
            $parameters[':search_email'] = $search;
        }
        $sql .= ' GROUP BY u.id ORDER BY u.created_at DESC LIMIT 200';
        $statement = $pdo->prepare($sql);
        $statement->execute($parameters);
        questgo_json(['users' => $statement->fetchAll()]);
    } catch (Throwable $exception) {
        questgo_action_error($exception);
    }
}

questgo_require_post();
questgo_verify_csrf();
$redirect = 'pages/admin/users.php';
try {
    $action = questgo_text($_POST['action'] ?? null, 'Action', 30);
    $targetId = questgo_id($_POST['user_id'] ?? null, 'User ID');

    if ($action === 'status') {
        $status = $_POST['status'] ?? '';
        if (!in_array($status, ['active', 'inactive'], true)) {
            throw new InvalidArgumentException('Choose a valid account status.');
        }
        if ($targetId === (int) $admin['id'] && $status !== 'active') {
            throw new InvalidArgumentException('You cannot deactivate your own administrator account.');
        }

        $pdo->beginTransaction();
        $lockAdmins = $pdo->query("SELECT id, status FROM users WHERE role = 'admin' FOR UPDATE");
        $admins = $lockAdmins->fetchAll();
        $target = $pdo->prepare('SELECT id, role, status FROM users WHERE id = :id FOR UPDATE');
        $target->execute([':id' => $targetId]);
        $record = $target->fetch();
        if (!$record) {
            throw new InvalidArgumentException('User not found.');
        }
        if ($record['role'] === 'admin' && $record['status'] === 'active' && $status === 'inactive') {
            $activeCount = count(array_filter($admins, static fn(array $row): bool => $row['status'] === 'active'));
            if ($activeCount <= 1) {
                throw new InvalidArgumentException('The last active administrator cannot be deactivated.');
            }
        }
        $update = $pdo->prepare('UPDATE users SET status = :status WHERE id = :id');
        $update->execute([':status' => $status, ':id' => $targetId]);
        $pdo->commit();
        $successMessage = 'Account status updated.';
    } elseif ($action === 'delete') {
        if ($targetId === (int) $admin['id']) {
            throw new InvalidArgumentException('You cannot delete your own administrator account.');
        }

        $pdo->beginTransaction();
        $lockedAdmins = $pdo->query("SELECT id, status FROM users WHERE role = 'admin' FOR UPDATE")->fetchAll();
        $target = $pdo->prepare('SELECT id, role, status FROM users WHERE id = :id FOR UPDATE');
        $target->execute([':id' => $targetId]);
        $record = $target->fetch();
        if (!$record) {
            throw new InvalidArgumentException('User not found.');
        }
        if ($record['role'] === 'admin' && $record['status'] === 'active') {
            $activeCount = count(array_filter($lockedAdmins, static fn(array $row): bool => $row['status'] === 'active'));
            if ($activeCount <= 1) {
                throw new InvalidArgumentException('The last active administrator cannot be deleted.');
            }
        }
        $delete = $pdo->prepare('DELETE FROM users WHERE id = :id');
        $delete->execute([':id' => $targetId]);
        $pdo->commit();
        $successMessage = 'User account removed.';
    } else {
        throw new InvalidArgumentException('Unknown user-management action.');
    }
    questgo_action_success($successMessage, $redirect);
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    questgo_action_error($exception, $redirect);
}
