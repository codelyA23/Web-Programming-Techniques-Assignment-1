<?php
require __DIR__ . '/../app/bootstrap.php';
if (current_user()) redirect('dashboard.php');
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['logged_out'])) flash('You’re safely logged out. See you for the next chapter.');
$errors = []; $email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf(); $email = mb_strtolower(post('email'));
    $password = isset($_POST['password']) && is_string($_POST['password']) ? $_POST['password'] : '';
    $buckets = auth_buckets($email);
    if (is_throttled($buckets)) { $errors['login'] = 'Too many attempts. Please wait 15 minutes before trying again.'; http_response_code(429); }
    else {
        $user = query('SELECT * FROM users WHERE email = ?', [$email])->fetch();
        // A fixed dummy hash avoids skipping password work when the email is unknown.
        $hash = $user['password_hash'] ?? '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.';
        $valid = password_verify($password, $hash);
        if (!$user || !$valid || strlen($password) > 72) {
            failed_attempt($buckets); $errors['login'] = 'The email or password is incorrect. Please try again.'; http_response_code(422);
        } else {
            query('DELETE FROM attempts WHERE bucket = ?', [$buckets[0]]);
            if (password_needs_rehash($hash, PASSWORD_DEFAULT)) query('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash($password, PASSWORD_DEFAULT), $user['id']]);
            sign_in($user); flash('Welcome back. Your next small step is waiting.'); redirect('dashboard.php');
        }
    }
}
render_header('Log in', 'login');
?>
<main class="auth-layout container" id="main"><section class="auth-story"><p class="eyebrow">PICK UP WHERE YOU LEFT OFF</p><h1>Your ideas.<br>Your pace.<br><em>Your studio.</em></h1><p>Come back to your learning path, celebrate the little wins, and keep building something that matters.</p><div class="auth-art compact" aria-hidden="true"><span>&lt;</span><span class="asterisk">✳</span><span>/&gt;</span></div><div class="story-caption"><span class="line"></span> MADE FOR CURIOUS MINDS</div></section>
<section class="auth-card login-card"><div class="card-kicker"><span class="dot"></span> BACK TO YOUR WORKSPACE</div><h2>Good to see you again.</h2><p class="muted">Log in and take the next step.</p><?php form_errors($errors); ?>
<form method="post" action="login.php" class="stack-form"><?= csrf_field() ?><label for="email">Email address</label><input type="email" id="email" name="email" required autocomplete="username" maxlength="254" placeholder="you@example.com" value="<?= e($email) ?>"><label for="password">Password</label><div class="password-field"><input type="password" id="password" name="password" required autocomplete="current-password" maxlength="72"></div><button class="button full" type="submit">Log in to my studio <?= icon('arrow') ?></button><p class="form-note"><?= icon('shield') ?> Your session stays private on this device.</p></form><p class="auth-switch">New around here? <a href="signup.php">Create an account</a></p><p class="small-print">Using a shared computer? Log out when you finish.</p></section></main>
<?php render_footer(); ?>
