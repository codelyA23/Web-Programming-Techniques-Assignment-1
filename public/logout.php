<?php
require __DIR__ . '/../app/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST'); http_response_code(405); render_header('Log out');
    echo '<main class="container error-page" id="main"><h1>Log out from your workspace.</h1><p>Use the Log out button to securely end your session.</p><a class="button" href="dashboard.php">Open workspace</a></main>';
    render_footer(); exit;
}
verify_csrf();
$user = current_user(); if ($user) record_activity((int)$user['id'], 'Logged out securely');
$_SESSION = [];
$params = session_get_cookie_params();
setcookie(session_name(), '', ['expires' => time() - 42000, 'path' => $params['path'], 'secure' => $params['secure'], 'httponly' => true, 'samesite' => 'Lax']);
session_destroy();
redirect('login.php?logged_out=1');
