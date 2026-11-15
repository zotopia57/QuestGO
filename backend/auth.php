<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    $sessionLifetime = isset($_POST['remember']) && $_POST['remember'] === 'on' ? 60 * 60 * 24 * 30 : 0;
    ini_set('session.gc_maxlifetime', (string) (60 * 60 * 24 * 30));
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_trans_sid', '0');
    $isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'lifetime' => $sessionLifetime,
        'path' => '/',
        'secure' => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

if (!empty($_SESSION['remember_me']) && ini_get('session.use_cookies')) {
    $cookie = session_get_cookie_params();
    setcookie(session_name(), session_id(), [
        'expires' => time() + (60 * 60 * 24 * 30),
        'path' => $cookie['path'],
        'domain' => $cookie['domain'],
        'secure' => $cookie['secure'],
        'httponly' => $cookie['httponly'],
        'samesite' => $cookie['samesite'] ?? 'Lax',
    ]);
}

function questgo_csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function questgo_csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' .
        htmlspecialchars(questgo_csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function questgo_verify_csrf(): void
{
    $provided = $_POST['csrf_token'] ?? '';
    $stored = $_SESSION['csrf_token'] ?? '';

    if (!is_string($provided) || !is_string($stored) || $stored === '' || !hash_equals($stored, $provided)) {
        http_response_code(403);
        if (str_contains(strtolower($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json')) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Your form session expired. Reload the page and try again.']);
            exit;
        }
        exit('Your form session expired. Go back, reload the page and try again.');
    }
}

function questgo_flash(string $type, string $message): void
{
    $_SESSION['flash_message'] = ['type' => $type, 'message' => $message];
}

function questgo_take_flash(): ?array
{
    $message = $_SESSION['flash_message'] ?? null;
    unset($_SESSION['flash_message']);

    return is_array($message) ? $message : null;
}

function questgo_redirect(string $path): void
{
    $base = rtrim(dirname(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    $base = preg_replace('~/(backend(?:/(?:user|admin))?|pages(?:/(?:user|admin))?)$~', '', $base);
    $location = rtrim((string) $base, '/') . '/' . ltrim($path, '/');
    header('Location: ' . $location, true, 303);
    exit;
}

function questgo_current_user(): ?array
{
    return isset($_SESSION['user']) && is_array($_SESSION['user']) ? $_SESSION['user'] : null;
}

function questgo_require_role(string $role): array
{
    $user = questgo_current_user();
    if (!$user) {
        questgo_flash('error', 'Please log in to continue.');
        questgo_redirect('pages/login.php');
    }

    global $pdo;
    require_once __DIR__ . '/db.php';
    $statement = $pdo->prepare('SELECT id, name, email, role, status FROM users WHERE id = :id LIMIT 1');
    $statement->execute([':id' => $user['id'] ?? 0]);
    $current = $statement->fetch();
    if (!$current || $current['status'] !== 'active') {
        $_SESSION = [];
        session_regenerate_id(true);
        questgo_flash('error', 'Your account is unavailable. Please log in again.');
        questgo_redirect('pages/login.php');
    }

    $_SESSION['user'] = [
        'id' => (int) $current['id'],
        'name' => $current['name'],
        'email' => $current['email'],
        'role' => $current['role'],
    ];
    if ($current['role'] !== $role) {
        http_response_code(403);
        exit('You do not have permission to access this page.');
    }

    return $_SESSION['user'];
}

function questgo_require_post(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        http_response_code(405);
        header('Allow: POST');
        exit('This action requires a POST request.');
    }
}

function questgo_json(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function questgo_action_success(string $message, string $redirectPath, array $data = []): void
{
    if (str_contains(strtolower($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json')) {
        questgo_json(['message' => $message] + $data);
    }

    questgo_flash('success', $message);
    questgo_redirect($redirectPath);
}

function questgo_id(mixed $value, string $label = 'ID'): int
{
    $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($id === false) {
        throw new InvalidArgumentException($label . ' is invalid.');
    }

    return $id;
}

function questgo_text(mixed $value, string $label, int $maxLength, bool $required = true): string
{
    if (!is_string($value)) {
        throw new InvalidArgumentException($label . ' is required.');
    }
    $value = trim($value);
    if (($required && $value === '') || mb_strlen($value) > $maxLength) {
        throw new InvalidArgumentException($label . ' is required and must be no more than ' . $maxLength . ' characters.');
    }

    return $value;
}

function questgo_date(mixed $value, string $label): string
{
    if (!is_string($value)) {
        throw new InvalidArgumentException($label . ' is invalid.');
    }
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    if (!$date || $date->format('Y-m-d') !== $value) {
        throw new InvalidArgumentException($label . ' must be a valid date.');
    }

    return $value;
}

function questgo_action_error(Throwable $exception, ?string $redirectPath = null): void
{
    if ($exception instanceof InvalidArgumentException) {
        $message = $exception->getMessage();
        $status = 422;
    } elseif ($exception instanceof PDOException && $exception->getCode() === '23000') {
        $message = 'This record conflicts with existing data or is still in use.';
        $status = 409;
    } else {
        error_log('QuestGo request failed: ' . $exception->getMessage());
        $message = 'The request could not be completed. Please try again.';
        $status = 500;
    }

    if (str_contains(strtolower($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json')) {
        questgo_json(['error' => $message], $status);
    }

    if ($redirectPath !== null) {
        questgo_flash('error', $message);
        questgo_redirect($redirectPath);
    }

    questgo_json(['error' => $message], $status);
}
