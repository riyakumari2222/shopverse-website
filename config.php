<?php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
define('DB_HOST', 'localhost');
define('DB_NAME', 'shopverse');
define('DB_USER', 'root');
define('DB_PASS', '');
define('SITE_NAME', 'ShopVerse');
$projectRoot = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
$basePath = basename($projectRoot);
if ($documentRoot !== false) {
    $documentRoot = rtrim($documentRoot, '\\/');
    if (strcasecmp($projectRoot, $documentRoot) === 0) {
        $basePath = '';
    } else {
        $documentRootPrefix = $documentRoot . DIRECTORY_SEPARATOR;
        if (strncasecmp($projectRoot, $documentRootPrefix, strlen($documentRootPrefix)) === 0) {
            $basePath = substr($projectRoot, strlen($documentRootPrefix));
        }
    }
}
define('BASE_URL', $basePath === '' ? '' : '/' . str_replace('\\', '/', trim($basePath, '\\/')));

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
    return $pdo;
}
function e(?string $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
function money(float|int|string $amount): string {
    return '₹' . number_format((float)$amount, 2);
}
function redirect(string $path): never {
    header('Location: ' . BASE_URL . $path);
    exit;
}
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
function verify_csrf(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(419);
        exit('Invalid or expired form token. Go back, refresh, and try again.');
    }
}
function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}
function require_login(): void {
    if (!current_user()) {
        $_SESSION['flash'] = 'Please sign in to continue.';
        redirect('/login.php');
    }
}
function require_admin(): void {
    if (!current_user() || (current_user()['role'] ?? '') !== 'admin') {
        http_response_code(403);
        exit('403 — Admin access required.');
    }
}
function flash(?string $message = null): ?string {
    if ($message !== null) {
        $_SESSION['flash'] = $message;
        return null;
    }
    $value = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $value;
}
function cart_count(): int {
    return array_sum(array_map('intval', $_SESSION['cart'] ?? []));
}
