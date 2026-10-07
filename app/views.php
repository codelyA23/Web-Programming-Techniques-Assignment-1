<?php
declare(strict_types=1);

function icon(string $name): string {
    $paths = [
        'arrow' => '<path d="M5 12h14m-6-6 6 6-6 6"/>',
        'check' => '<path d="m5 12 4 4L19 6"/>',
        'grid' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
        'book' => '<path d="M12 5v15m0-15C9 3 6 3 3 4v15c3-1 6-1 9 1 3-2 6-2 9-1V4c-3-1-6-1-9 1Z"/>',
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/>',
        'logout' => '<path d="M9 4H4v16h5m0-8h12m-4-4 4 4-4 4"/>',
        'shield' => '<path d="m12 3 8 3v6c0 5-8 9-8 9s-8-4-8-9V6l8-3Z"/><path d="m8 12 3 3 5-6"/>',
        'spark' => '<path d="m12 2 3 7 7 3-7 3-3 7-3-7-7-3 7-3 3-7Z"/>',
    ];
    return '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['arrow']) . '</svg>';
}
function render_header(string $title, string $active = 'home', ?array $user = null): void {
    ?><!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="An independent student prototype for a Django Girls Sierra Leone learning community. Create your account and begin your web journey.">
<title><?= e($title) ?> · Salone Studio</title><link rel="icon" href="assets/favicon.svg" type="image/svg+xml"><link rel="stylesheet" href="assets/style.css"><script src="assets/app.js" defer></script></head>
<body class="<?= $user ? 'workspace-body' : 'public-body' ?>"><a class="skip-link" href="#main">Skip to content</a>
<header class="site-header"><div class="header-inner"><a class="brand" href="index.php" aria-label="Django Girls Sierra Leone home"><img src="assets/django-girls.svg" alt="Django Girls" width="172" height="43"><span class="brand-region">SIERRA LEONE <span class="flag" aria-hidden="true"></span></span></a>
<nav aria-label="Main navigation">
<?php if ($user): ?>
<a href="dashboard.php" <?= $active === 'dashboard' ? 'aria-current="page"' : '' ?>>My workspace</a><a href="resources.php" <?= $active === 'resources' ? 'aria-current="page"' : '' ?>>Resources</a><a class="avatar" href="profile.php" aria-label="Your profile" <?= $active === 'profile' ? 'aria-current="page"' : '' ?>><?= e(mb_strtoupper(mb_substr($user['name'], 0, 1))) ?></a>
<?php else: ?>
<a href="index.php#journey">The journey</a><a href="index.php#about">Our purpose</a><a href="login.php" <?= $active === 'login' ? 'aria-current="page"' : '' ?>>Log in</a><a class="button small" href="signup.php">Join the studio <?= icon('arrow') ?></a>
<?php endif; ?></nav></div></header>
<?php if (isset($_SESSION['flash'])): ?><div class="container flash" role="status"><?= e($_SESSION['flash']) ?></div><?php unset($_SESSION['flash']); endif;
}
function render_footer(): void {
    global $site;
    ?><footer class="site-footer"><div class="container footer-top"><a href="index.php" class="studio-name">salone<span>studio.</span></a><p>A little curiosity. A world of possibility.</p><a href="about.php">About this project <?= icon('arrow') ?></a></div><div class="container footer-bottom"><p>© <?= e($site['student_name']) ?>, <?= e($site['programme']) ?>, Semester <?= e($site['semester']) ?>, <?= e($site['student_id']) ?>, <?= e($site['year']) ?></p><p>Independent student prototype · No official affiliation</p></div></footer></body></html><?php
}
function form_errors(array $errors): void {
    if (!$errors) return;
    echo '<div class="form-errors" role="alert" tabindex="-1"><strong>Please check the following:</strong><ul>';
    foreach ($errors as $error) echo '<li>' . e($error) . '</li>';
    echo '</ul></div>';
}
function field_error(array $errors, string $field): string { return isset($errors[$field]) ? ' aria-invalid="true" aria-describedby="' . $field . '-error"' : ''; }
function error_hint(array $errors, string $field): void { if (isset($errors[$field])) echo '<span class="field-error" id="' . $field . '-error">' . e($errors[$field]) . '</span>'; }
function workspace_nav(string $active, array $user): void {
    ?><aside class="workspace-sidebar"><p class="eyebrow">YOUR STUDIO</p><nav aria-label="Workspace"><a href="dashboard.php" <?= $active === 'dashboard' ? 'aria-current="page"' : '' ?>><?= icon('grid') ?> Overview</a><a href="dashboard.php#learning-path"><?= icon('book') ?> Learning path</a><a href="resources.php" <?= $active === 'resources' ? 'aria-current="page"' : '' ?>><?= icon('spark') ?> Resource shelf</a><a href="profile.php" <?= $active === 'profile' ? 'aria-current="page"' : '' ?>><?= icon('user') ?> My profile</a></nav><div class="sidebar-note"><span class="tiny-star">✳</span><h3>You belong here.</h3><p>Start small. Ask questions.<br>Keep making things.</p></div><form action="logout.php" method="post"><?= csrf_field() ?><button class="logout-button" type="submit"><?= icon('logout') ?> Log out</button></form></aside><?php
}
