<?php
require __DIR__ . '/../app/bootstrap.php';
if (current_user()) redirect('dashboard.php');
$errors = []; $name = ''; $email = ''; $goal = goals()[0];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf(); $name = post('name'); $email = mb_strtolower(post('email')); $goal = post('goal');
    $password = isset($_POST['password']) && is_string($_POST['password']) ? $_POST['password'] : '';
    $confirmation = isset($_POST['confirmation']) && is_string($_POST['confirmation']) ? $_POST['confirmation'] : '';
    if (mb_strlen($name) < 2 || mb_strlen($name) > 80 || preg_match('/[\x00-\x1F\x7F]/u', $name)) $errors['name'] = 'Enter your name using 2–80 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) $errors['email'] = 'Enter a valid email address.';
    if (strlen($password) < 10 || strlen($password) > 72) $errors['password'] = 'Use a password between 10 and 72 bytes. A long passphrase works well.';
    if ($password !== $confirmation) $errors['confirmation'] = 'Your passwords do not match.';
    if (!in_array($goal, goals(), true)) $errors['goal'] = 'Choose one of the learning goals.';
    if (!$errors) {
        try {
            // The UNIQUE constraint also protects against simultaneous duplicate registrations.
            query('INSERT INTO users (name, email, password_hash, goal, created_at) VALUES (?, ?, ?, ?, ?)', [$name, $email, password_hash($password, PASSWORD_DEFAULT), $goal, gmdate('Y-m-d H:i:s')]);
            $id = (int)db()->lastInsertId(); record_activity($id, 'Created your studio account');
            flash('Your account is ready. Log in to begin your journey.'); redirect('login.php');
        } catch (PDOException $error) {
            if ((string)$error->getCode() !== '23000') throw $error;
            $errors['email'] = 'An account already uses this email. Log in instead.';
        }
    }
    http_response_code(422);
}
render_header('Join the studio', 'signup');
?>
<main class="auth-layout container" id="main"><section class="auth-story"><p class="eyebrow">YOUR NEXT CHAPTER</p><h1>Big ideas start<br>with a <em>small step.</em></h1><p>A space to learn, experiment, and find your voice in technology. Let’s make your first step a good one.</p><div class="auth-art" aria-hidden="true"><span>{</span><span class="asterisk">✳</span><span>}</span></div><div class="story-caption"><span class="line"></span> FROM SIERRA LEONE, WITH POSSIBILITY</div></section>
<section class="auth-card"><div class="card-kicker"><span class="dot"></span> CREATE YOUR WORKSPACE</div><h2>You’re welcome here.</h2><p class="muted">No experience needed. Just bring your curiosity.</p><?php form_errors($errors); ?>
<form method="post" action="signup.php" class="stack-form"><?= csrf_field() ?>
<label for="name">Full name</label><input id="name" name="name" autocomplete="name" required minlength="2" maxlength="80" value="<?= e($name) ?>" placeholder="What should we call you?"<?= field_error($errors, 'name') ?>><?php error_hint($errors, 'name'); ?>
<label for="email">Email address</label><input id="email" name="email" type="email" autocomplete="email" required maxlength="254" value="<?= e($email) ?>" placeholder="you@example.com"<?= field_error($errors, 'email') ?>><?php error_hint($errors, 'email'); ?>
<label for="goal">What brings you here?</label><select name="goal" id="goal"<?= field_error($errors, 'goal') ?>><?php foreach (goals() as $option): ?><option <?= $goal === $option ? 'selected' : '' ?>><?= e($option) ?></option><?php endforeach; ?></select><?php error_hint($errors, 'goal'); ?>
<label for="password">Password <span class="label-hint">10–72 bytes</span></label><div class="password-field"><input id="password" name="password" type="password" autocomplete="new-password" required minlength="10" maxlength="72" aria-describedby="password-help<?= isset($errors['password']) ? ' password-error' : '' ?>" <?= isset($errors['password']) ? 'aria-invalid="true"' : '' ?>></div><small id="password-help">Choose a long, memorable passphrase.</small><?php error_hint($errors, 'password'); ?>
<label for="confirmation">Confirm password</label><input id="confirmation" name="confirmation" type="password" autocomplete="new-password" required minlength="10" maxlength="72"<?= field_error($errors, 'confirmation') ?>><?php error_hint($errors, 'confirmation'); ?>
<button class="button full" type="submit">Create my account <?= icon('arrow') ?></button><p class="form-note"><?= icon('shield') ?> Your password is stored as a secure hash.</p></form><p class="auth-switch">Already part of the studio? <a href="login.php">Log in</a></p></section></main>
<?php render_footer(); ?>
