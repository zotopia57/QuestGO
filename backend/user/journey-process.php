<?php
require_once __DIR__ . '/../auth.php';
$user = questgo_require_role('user');
require_once __DIR__ . '/../db.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
    try {
        if (isset($_GET['stops_for'])) {
            $journeyId = questgo_id($_GET['stops_for'], 'Journey ID');
            $owner = $pdo->prepare('SELECT 1 FROM journeys WHERE id = :id AND user_id = :user_id');
            $owner->execute([':id' => $journeyId, ':user_id' => $user['id']]);
            if (!$owner->fetchColumn()) {
                throw new InvalidArgumentException('Journey not found.');
            }
            $stops = $pdo->prepare(
                'SELECT js.id, js.visit_date, js.stop_order, js.notes, p.name, p.city, p.state_or_ut
                 FROM journey_stops js JOIN places p ON p.id = js.place_id
                 WHERE js.journey_id = :journey_id ORDER BY js.stop_order'
            );
            $stops->execute([':journey_id' => $journeyId]);
            questgo_json(['stops' => $stops->fetchAll()]);
        }

        $sql = 'SELECT j.*,
                       (SELECT COUNT(*) FROM journey_stops js WHERE js.journey_id = j.id) AS stop_count
                FROM journeys j WHERE j.user_id = :user_id';
        $params = [':user_id' => $user['id']];
        if (isset($_GET['id'])) {
            $sql .= ' AND j.id = :id';
            $params[':id'] = questgo_id($_GET['id'], 'Journey ID');
        }
        $sql .= ' ORDER BY j.departure_date ASC, j.id DESC';
        $statement = $pdo->prepare($sql);
        $statement->execute($params);
        $journeys = $statement->fetchAll();
        questgo_json(['journeys' => $journeys]);
    } catch (Throwable $exception) {
        questgo_action_error($exception);
    }
}

questgo_require_post();
questgo_verify_csrf();
$redirect = 'pages/user/journey-planner.php';

try {
    $action = questgo_text($_POST['action'] ?? null, 'Action', 30);

    if ($action === 'add' || $action === 'update') {
        $title = questgo_text($_POST['title'] ?? null, 'Journey title', 150);
        $startCity = questgo_text($_POST['start_city'] ?? null, 'Starting city', 100);
        $startState = questgo_text($_POST['start_state'] ?? null, 'Starting state or UT', 120);
        $destinationCity = questgo_text($_POST['destination_city'] ?? null, 'Destination city', 100);
        $destinationState = questgo_text($_POST['destination_state'] ?? null, 'Destination state or UT', 120);
        $departure = questgo_date($_POST['departure_date'] ?? null, 'Departure date');
        $return = questgo_date($_POST['return_date'] ?? null, 'Return date');
        $travellers = filter_var($_POST['travellers'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 50]]);
        $budget = $_POST['budget'] ?? null;

        if ($return < $departure) {
            throw new InvalidArgumentException('Return date must be on or after departure date.');
        }
        if ($travellers === false || !is_numeric($budget) || (float) $budget < 0 || (float) $budget > 9999999999.99) {
            throw new InvalidArgumentException('Enter a valid traveller count and non-negative INR budget.');
        }

        $values = [
            ':title' => $title,
            ':start_location' => $startCity . ', ' . $startState,
            ':destination' => $destinationCity . ', ' . $destinationState,
            ':departure_date' => $departure,
            ':return_date' => $return,
            ':travellers' => $travellers,
            ':estimated_budget' => number_format((float) $budget, 2, '.', ''),
        ];

        if ($action === 'add') {
            $values[':user_id'] = $user['id'];
            $statement = $pdo->prepare(
                'INSERT INTO journeys
                 (user_id, title, start_location, destination, departure_date, return_date, travellers, estimated_budget, status)
                 VALUES (:user_id, :title, :start_location, :destination, :departure_date, :return_date, :travellers, :estimated_budget, "planning")'
            );
            $statement->execute($values);
            $successMessage = 'Journey added to your planner.';
        } else {
            $id = questgo_id($_POST['journey_id'] ?? null, 'Journey ID');
            $values[':id'] = $id;
            $values[':user_id'] = $user['id'];
            $statement = $pdo->prepare(
                'UPDATE journeys SET title = :title, start_location = :start_location, destination = :destination,
                 departure_date = :departure_date, return_date = :return_date, travellers = :travellers,
                 estimated_budget = :estimated_budget WHERE id = :id AND user_id = :user_id'
            );
            $statement->execute($values);
            if ($statement->rowCount() === 0 && !journey_owned($pdo, $id, (int) $user['id'])) {
                throw new InvalidArgumentException('Journey not found.');
            }
            $successMessage = 'Journey details updated.';
        }
    } elseif ($action === 'delete') {
        $id = questgo_id($_POST['journey_id'] ?? null, 'Journey ID');
        $statement = $pdo->prepare('DELETE FROM journeys WHERE id = :id AND user_id = :user_id');
        $statement->execute([':id' => $id, ':user_id' => $user['id']]);
        if ($statement->rowCount() === 0) {
            throw new InvalidArgumentException('Journey not found.');
        }
        $successMessage = 'Journey deleted.';
    } elseif ($action === 'add_stop') {
        $journeyId = questgo_id($_POST['journey_id'] ?? null, 'Journey ID');
        $placeId = questgo_id($_POST['place_id'] ?? null, 'Place ID');
        $pdo->beginTransaction();
        $owner = $pdo->prepare('SELECT id FROM journeys WHERE id = :id AND user_id = :user_id FOR UPDATE');
        $owner->execute([':id' => $journeyId, ':user_id' => $user['id']]);
        if (!$owner->fetch()) {
            throw new InvalidArgumentException('Journey not found.');
        }
        $nextOrder = $pdo->prepare('SELECT COALESCE(MAX(stop_order), 0) + 1 FROM journey_stops WHERE journey_id = :journey_id');
        $nextOrder->execute([':journey_id' => $journeyId]);
        $insert = $pdo->prepare(
            'INSERT INTO journey_stops (journey_id, place_id, visit_date, stop_order, notes)
             VALUES (:journey_id, :place_id, :visit_date, :stop_order, :notes)'
        );
        $insert->execute([
            ':journey_id' => $journeyId,
            ':place_id' => $placeId,
            ':visit_date' => empty($_POST['visit_date']) ? null : questgo_date($_POST['visit_date'], 'Visit date'),
            ':stop_order' => $nextOrder->fetchColumn(),
            ':notes' => questgo_text($_POST['notes'] ?? '', 'Notes', 2000, false) ?: null,
        ]);
        $pdo->commit();
        $successMessage = 'Place added as a journey stop.';
    } elseif ($action === 'remove_stop') {
        $journeyId = questgo_id($_POST['journey_id'] ?? null, 'Journey ID');
        $stopId = questgo_id($_POST['stop_id'] ?? null, 'Stop ID');
        $statement = $pdo->prepare(
            'DELETE js FROM journey_stops js
             INNER JOIN journeys j ON j.id = js.journey_id
             WHERE js.id = :stop_id AND js.journey_id = :journey_id AND j.user_id = :user_id'
        );
        $statement->execute([':stop_id' => $stopId, ':journey_id' => $journeyId, ':user_id' => $user['id']]);
        if ($statement->rowCount() === 0) {
            throw new InvalidArgumentException('Journey stop not found.');
        }
        $successMessage = 'Journey stop removed.';
    } else {
        throw new InvalidArgumentException('Unknown journey action.');
    }

    questgo_action_success($successMessage, $redirect);
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    questgo_action_error($exception, $redirect);
}

function journey_owned(PDO $pdo, int $journeyId, int $userId): bool
{
    $statement = $pdo->prepare('SELECT 1 FROM journeys WHERE id = :id AND user_id = :user_id');
    $statement->execute([':id' => $journeyId, ':user_id' => $userId]);

    return (bool) $statement->fetchColumn();
}
