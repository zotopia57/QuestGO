<?php
require_once __DIR__ . '/../auth.php';
$user = questgo_require_role('user');
require_once __DIR__ . '/../db.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
    try {
        $journeyId = null;
        if (!empty($_GET['journey_id'])) {
            $journeyId = questgo_id($_GET['journey_id'], 'Journey ID');
            require_owned_journey($pdo, $journeyId, (int) $user['id']);
        }
        $sql = 'SELECT e.id, e.journey_id, e.title, e.category, e.amount, e.expense_date, e.notes, j.title AS journey_title
                FROM expenses e LEFT JOIN journeys j ON j.id = e.journey_id
                WHERE e.user_id = :user_id';
        $parameters = [':user_id' => $user['id']];
        if ($journeyId !== null) {
            $sql .= ' AND e.journey_id = :journey_id';
            $parameters[':journey_id'] = $journeyId;
        }
        if (!empty($_GET['category'])) {
            $sql .= ' AND e.category = :category';
            $parameters[':category'] = questgo_text($_GET['category'], 'Category', 80);
        }
        $sql .= ' ORDER BY e.expense_date DESC, e.id DESC';
        $statement = $pdo->prepare($sql);
        $statement->execute($parameters);
        $expenses = $statement->fetchAll();
        $total = array_sum(array_map(static fn(array $expense): float => (float) $expense['amount'], $expenses));
        questgo_json(['expenses' => $expenses, 'total' => number_format($total, 2, '.', '')]);
    } catch (Throwable $exception) {
        questgo_action_error($exception);
    }
}

questgo_require_post();
questgo_verify_csrf();
$redirect = 'pages/user/expenses.php';
try {
    $action = questgo_text($_POST['action'] ?? null, 'Action', 20);
    if ($action === 'delete') {
        $id = questgo_id($_POST['expense_id'] ?? null, 'Expense ID');
        $statement = $pdo->prepare('DELETE FROM expenses WHERE id = :id AND user_id = :user_id');
        $statement->execute([':id' => $id, ':user_id' => $user['id']]);
        if (!$statement->rowCount()) {
            throw new InvalidArgumentException('Expense not found.');
        }
        questgo_action_success('Expense deleted.', $redirect);
    }

    $title = questgo_text($_POST['title'] ?? null, 'Expense title', 150);
    $category = questgo_text($_POST['category'] ?? null, 'Category', 80);
    $amount = $_POST['amount'] ?? null;
    $date = questgo_date($_POST['date'] ?? null, 'Expense date');
    $notes = questgo_text($_POST['notes'] ?? '', 'Notes', 5000, false);
    if (!is_numeric($amount) || (float) $amount <= 0 || (float) $amount > 9999999999.99) {
        throw new InvalidArgumentException('Enter an expense amount greater than ₹0.');
    }
    $journeyId = empty($_POST['journey_id']) ? null : questgo_id($_POST['journey_id'], 'Journey ID');
    if ($journeyId !== null) {
        require_owned_journey($pdo, $journeyId, (int) $user['id']);
    }
    $values = [
        ':user_id' => $user['id'],
        ':journey_id' => $journeyId,
        ':title' => $title,
        ':category' => $category,
        ':amount' => number_format((float) $amount, 2, '.', ''),
        ':expense_date' => $date,
        ':notes' => $notes ?: null,
    ];

    if ($action === 'add') {
        $statement = $pdo->prepare(
            'INSERT INTO expenses (user_id, journey_id, title, category, amount, expense_date, notes)
             VALUES (:user_id, :journey_id, :title, :category, :amount, :expense_date, :notes)'
        );
        $statement->execute($values);
        $successMessage = 'Expense added to your ledger.';
    } elseif ($action === 'update') {
        $values[':id'] = questgo_id($_POST['expense_id'] ?? null, 'Expense ID');
        $statement = $pdo->prepare(
            'UPDATE expenses SET journey_id = :journey_id, title = :title, category = :category,
             amount = :amount, expense_date = :expense_date, notes = :notes
             WHERE id = :id AND user_id = :user_id'
        );
        $statement->execute($values);
        if (!$statement->rowCount() && !expense_owned($pdo, (int) $values[':id'], (int) $user['id'])) {
            throw new InvalidArgumentException('Expense not found.');
        }
        $successMessage = 'Expense updated.';
    } else {
        throw new InvalidArgumentException('Unknown expense action.');
    }
    questgo_action_success($successMessage, $redirect);
} catch (Throwable $exception) {
    questgo_action_error($exception, $redirect);
}

function require_owned_journey(PDO $pdo, int $journeyId, int $userId): void
{
    $statement = $pdo->prepare('SELECT 1 FROM journeys WHERE id = :id AND user_id = :user_id');
    $statement->execute([':id' => $journeyId, ':user_id' => $userId]);
    if (!$statement->fetchColumn()) {
        throw new InvalidArgumentException('Journey not found.');
    }
}

function expense_owned(PDO $pdo, int $expenseId, int $userId): bool
{
    $statement = $pdo->prepare('SELECT 1 FROM expenses WHERE id = :id AND user_id = :user_id');
    $statement->execute([':id' => $expenseId, ':user_id' => $userId]);
    return (bool) $statement->fetchColumn();
}
