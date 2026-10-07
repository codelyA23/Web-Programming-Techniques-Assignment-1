<?php
require __DIR__ . '/../app/bootstrap.php'; $user = require_user(); $errors = [];
$name = $user['name']; $goal = $user['goal'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf(); $name = post('name'); $goal = post('goal');
    if (mb_strlen($name) < 2 || mb_strlen($name) > 80 || preg_match('/[\x00-\x1F\x7F]/u', $name)) $errors['name'] = 'Enter your name using 2–80 characters.';
    if (!in_array($goal, goals(), true)) $errors['goal'] = 'Choose one of the learning goals.';
    if (!$errors) {
        query('UPDATE users SET name = ?, goal = ? WHERE id = ?', [$name, $goal, $user['id']]);
        record_activity((int)$user['id'], 'Updated your profile and learning goal'); flash('Your profile is updated. Make this journey your own.'); redirect('profile.php');
    }
    http_response_code(422);
}
render_header('My profile', 'profile', $user);
?>
<main class="workspace container" id="main"><?php workspace_nav('profile', $user); ?><div class="workspace-main"><p class="eyebrow">MAKE YOURSELF AT HOME</p><h1>Your name. Your direction.</h1><p class="lead">A little about you, and what you want to make possible.</p><div class="profile-layout"><section class="profile-card"><h2>My profile</h2><?php form_errors($errors); ?><form method="post" action="profile.php" class="stack-form"><?= csrf_field() ?><label for="name">Full name</label><input name="name" id="name" value="<?= e($name) ?>" required minlength="2" maxlength="80" autocomplete="name"<?= field_error($errors, 'name') ?>><?php error_hint($errors, 'name'); ?><label for="goal">My learning goal</label><select id="goal" name="goal"<?= field_error($errors, 'goal') ?>><?php foreach (goals() as $option): ?><option <?= $goal === $option ? 'selected' : '' ?>><?= e($option) ?></option><?php endforeach; ?></select><?php error_hint($errors, 'goal'); ?><label for="email">Email address</label><input id="email" type="email" value="<?= e($user['email']) ?>" readonly aria-describedby="email-help"><small id="email-help">Your email identifies your account and stays the same.</small><button type="submit" class="button full">Save my profile <?= icon('check') ?></button></form></section><aside class="profile-note"><span class="tiny-star" aria-hidden="true">✳</span><h2>You’re writing<br>your own story.</h2><p>Goals can change. Curiosity can grow. Your workspace is here for the next chapter.</p><dl><dt>Member since</dt><dd><?= e(date('j F Y', strtotime($user['created_at'] . ' UTC'))) ?></dd><dt>Workspace</dt><dd>Salone Studio · Sierra Leone</dd><dt>Account protection</dt><dd>Hashed password · Private session</dd></dl><p class="small-print">Your session expires after 30 minutes of inactivity. Always log out on a shared device.</p></aside></div></div></main>
<?php render_footer(); ?>
