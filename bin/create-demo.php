<?php
declare(strict_types=1);
// Optional local presentation account. This script cannot be requested through the web.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../app/bootstrap.php';
$email = 'demo@example.test';
if (query('SELECT id FROM users WHERE email = ?', [$email])->fetch()) {
    echo "Demo account already exists. Its password has not been changed.\n"; exit;
}
query('INSERT INTO users (name, email, password_hash, goal, created_at) VALUES (?, ?, ?, ?, ?)', ['Aminata Demo', $email, password_hash('Demo-only-2026!', PASSWORD_DEFAULT), 'Build my first website', gmdate('Y-m-d H:i:s')]);
record_activity((int)db()->lastInsertId(), 'Created your studio account');
echo "Local demonstration account created: demo@example.test / Demo-only-2026!\nUse only on your local presentation server. Never run this on a public deployment.\n";
