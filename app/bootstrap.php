<?php
declare(strict_types=1);

const APP_ROOT = __DIR__ . '/..';
// This coursework runs through XAMPP Apache, not PHP's standalone server or CLI.
if (PHP_SAPI !== 'apache2handler') {
    http_response_code(503);
    exit('Start Apache and MySQL in XAMPP, then open http://localhost/salone-studio/.');
}
$site = require APP_ROOT . '/config/site.php';
date_default_timezone_set('Africa/Freetown');

// Accounts live in MySQL. Apache denies direct access to the session directory.
$storage = getenv('APP_STORAGE') ?: APP_ROOT . '/storage';
if (!is_dir($storage . '/sessions') && !mkdir($storage . '/sessions', 0700, true) && !is_dir($storage . '/sessions')) {
    throw new RuntimeException('Cannot create private storage. Check folder permissions.');
}
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.gc_maxlifetime', '1800');
session_save_path($storage . '/sessions');
session_name('salone_session');
session_set_cookie_params([
    'lifetime' => 0, 'path' => '/', 'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true, 'samesite' => 'Lax',
]);
session_start();
header('Content-Type: text/html; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; style-src 'self'; script-src 'none'; img-src 'self'; font-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'");
header('Cache-Control: no-store, private');

set_exception_handler(function (Throwable $error): void {
    error_log((string)$error);
    http_response_code($error instanceof PDOException ? 503 : 500);
    $message = $error instanceof PDOException
        ? 'Start MySQL in the XAMPP Control Panel and import database/schema.sql through phpMyAdmin. Account reads and writes require the MySQL server.'
        : 'Please try again shortly. If the problem continues, check the server configuration.';
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Workspace unavailable</title><link rel="stylesheet" href="assets/style.css"><link rel="stylesheet" href="assets/theme.css?v=20261013r2"></head><body><main class="container error-page"><p class="eyebrow">XAMPP SERVICE CHECK</p><h1>We could not open your workspace.</h1><p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p><a class="button" href="index.php">Return home</a></main></body></html>';
});

function e(mixed $value): string { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function redirect(string $path): never { header('Location: ' . $path, true, 303); exit; }
function flash(string $message): void { $_SESSION['flash'] = $message; }
function post(string $key): string { return isset($_POST[$key]) && is_string($_POST[$key]) ? trim($_POST[$key]) : ''; }
function csrf_token(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(32)); }
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">'; }
function verify_csrf(): void {
    if (!hash_equals(csrf_token(), post('csrf'))) {
        http_response_code(403);
        render_header('Session check', '');
        echo '<main class="container error-page"><p class="eyebrow">Session check</p><h1>Let’s try that again.</h1><p>Your form expired or could not be verified. Reload the page and submit it again.</p><a class="button" href="index.php">Return home</a></main>';
        render_footer(); exit;
    }
}

function db(): PDO {
    static $pdo;
    if ($pdo instanceof PDO) return $pdo;
    $config = require APP_ROOT . '/config/database.php';
    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $config['host'], $config['port'], $config['database']);
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 3,
    ]);
    return $pdo;
}
function query(string $sql, array $params = []): PDOStatement { $stmt = db()->prepare($sql); $stmt->execute($params); return $stmt; }
function record_activity(int $id, string $message): void { query('INSERT INTO activity (user_id, description, created_at) VALUES (?, ?, ?)', [$id, $message, gmdate('Y-m-d H:i:s')]); }
function current_user(): ?array {
    if (!isset($_SESSION['user_id'])) return null;
    if (time() - ($_SESSION['last_active'] ?? 0) > 1800) {
        unset($_SESSION['user_id'], $_SESSION['last_active']);
        session_regenerate_id(true); unset($_SESSION['csrf']);
        flash('Your session timed out. Sign in again to continue.'); return null;
    }
    $_SESSION['last_active'] = time();
    $user = query('SELECT id, name, email, goal, created_at, last_login FROM users WHERE id = ?', [$_SESSION['user_id']])->fetch();
    return $user ?: null;
}
function require_user(): array { $user = current_user(); if (!$user) redirect('login.php'); return $user; }
function sign_in(array $user): void {
    session_regenerate_id(true);
    $_SESSION = ['user_id' => (int)$user['id'], 'last_active' => time()];
    query('UPDATE users SET last_login = ? WHERE id = ?', [gmdate('Y-m-d H:i:s'), $user['id']]);
    record_activity((int)$user['id'], 'Signed in to your workspace');
}
function auth_buckets(string $email): array {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'local';
    return [hash('sha256', 'account:' . $email), hash('sha256', 'ip:' . $ip)];
}
function is_throttled(array $buckets): bool {
    foreach ($buckets as $i => $bucket) {
        $entry = query('SELECT failures, started_at FROM attempts WHERE bucket = ?', [$bucket])->fetch();
        if ($entry && (int)$entry['started_at'] > time() - 900 && (int)$entry['failures'] >= ($i === 0 ? 5 : 20)) return true;
    }
    return false;
}
function failed_attempt(array $buckets): void {
    query('DELETE FROM attempts WHERE started_at < ?', [time() - 900]);
    foreach ($buckets as $bucket) {
        query('INSERT INTO attempts (bucket, failures, started_at) VALUES (?, 1, ?) ON DUPLICATE KEY UPDATE failures = failures + 1', [$bucket, time()]);
    }
}
function goals(): array { return ['Build my first website', 'Explore a career in tech', 'Support my community']; }

require APP_ROOT . '/app/lessons.php';
require APP_ROOT . '/app/views.php';
