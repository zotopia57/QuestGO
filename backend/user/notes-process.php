<?php
require_once __DIR__ . '/../auth.php';
$user = questgo_require_role('user');
require_once __DIR__ . '/../db.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
    try {
        $sql = 'SELECT n.id, n.journey_id, n.title, n.content, n.created_at, n.updated_at, j.title AS journey_title
                FROM travel_notes n LEFT JOIN journeys j ON j.id = n.journey_id
                WHERE n.user_id = :user_id';
        $parameters = [':user_id' => $user['id']];
        if (isset($_GET['id'])) {
            $sql .= ' AND n.id = :id';
            $parameters[':id'] = questgo_id($_GET['id'], 'Note ID');
        }
        if (!empty($_GET['journey_id'])) {
            $sql .= ' AND n.journey_id = :journey_id';
            $parameters[':journey_id'] = questgo_id($_GET['journey_id'], 'Journey ID');
        }
        $sql .= ' ORDER BY n.updated_at DESC, n.id DESC';
        $statement = $pdo->prepare($sql);
        $statement->execute($parameters);
        $notes = $statement->fetchAll();
        if (isset($_GET['id']) && !$notes) {
            questgo_json(['error' => 'Note not found.'], 404);
        }
        questgo_json(['notes' => $notes]);
    } catch (Throwable $exception) {
        questgo_action_error($exception);
    }
}

questgo_require_post();
questgo_verify_csrf();
$redirect = 'pages/user/notes.php';
try {
    $action = questgo_text($_POST['action'] ?? null, 'Action', 20);
    if ($action === 'delete') {
        $id = questgo_id($_POST['note_id'] ?? null, 'Note ID');
        $delete = $pdo->prepare('DELETE FROM travel_notes WHERE id = :id AND user_id = :user_id');
        $delete->execute([':id' => $id, ':user_id' => $user['id']]);
        if (!$delete->rowCount()) {
            throw new InvalidArgumentException('Note not found.');
        }
        questgo_action_success('Travel note deleted.', $redirect);
    }

    $title = questgo_text($_POST['title'] ?? null, 'Note title', 150);
    $content = questgo_text($_POST['content'] ?? null, 'Note content', 20000);
    $journeyId = empty($_POST['journey_id']) ? null : questgo_id($_POST['journey_id'], 'Journey ID');
    if ($journeyId !== null) {
        require_note_journey_owner($pdo, $journeyId, (int) $user['id']);
    }

    if ($action === 'create') {
        $statement = $pdo->prepare(
            'INSERT INTO travel_notes (user_id, journey_id, title, content)
             VALUES (:user_id, :journey_id, :title, :content)'
        );
        $statement->execute([
            ':user_id' => $user['id'],
            ':journey_id' => $journeyId,
            ':title' => $title,
            ':content' => $content,
        ]);
        $successMessage = 'Travel note saved.';
    } elseif ($action === 'update') {
        $id = questgo_id($_POST['note_id'] ?? null, 'Note ID');
        $statement = $pdo->prepare(
            'UPDATE travel_notes SET journey_id = :journey_id, title = :title, content = :content
             WHERE id = :id AND user_id = :user_id'
        );
        $statement->execute([
            ':journey_id' => $journeyId,
            ':title' => $title,
            ':content' => $content,
            ':id' => $id,
            ':user_id' => $user['id'],
        ]);
        if (!$statement->rowCount() && !note_owned($pdo, $id, (int) $user['id'])) {
            throw new InvalidArgumentException('Note not found.');
        }
        $successMessage = 'Travel note updated.';
    } else {
        throw new InvalidArgumentException('Unknown note action.');
    }
    questgo_action_success($successMessage, $redirect);
} catch (Throwable $exception) {
    questgo_action_error($exception, $redirect);
}

function require_note_journey_owner(PDO $pdo, int $journeyId, int $userId): void
{
    $statement = $pdo->prepare('SELECT 1 FROM journeys WHERE id = :id AND user_id = :user_id');
    $statement->execute([':id' => $journeyId, ':user_id' => $userId]);
    if (!$statement->fetchColumn()) {
        throw new InvalidArgumentException('Journey not found.');
    }
}

function note_owned(PDO $pdo, int $noteId, int $userId): bool
{
    $statement = $pdo->prepare('SELECT 1 FROM travel_notes WHERE id = :id AND user_id = :user_id');
    $statement->execute([':id' => $noteId, ':user_id' => $userId]);
    return (bool) $statement->fetchColumn();
}
