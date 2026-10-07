<?php
require __DIR__ . '/../app/bootstrap.php'; $user = require_user();
$id = isset($_GET['id']) && is_string($_GET['id']) ? $_GET['id'] : '';
$lesson = lessons()[$id] ?? null;
if (!$lesson) { http_response_code(404); render_header('Lesson not found', '', $user); echo '<main id="main" class="container error-page"><h1>That lesson isn’t on this path.</h1><a class="button" href="dashboard.php">Return to your learning path</a></main>'; render_footer(); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (post('answer') !== $lesson['answer']) { $error = 'Not quite. Revisit the lesson and try another answer.'; http_response_code(422); }
    elseif (!query('SELECT 1 FROM progress WHERE user_id = ? AND lesson_id = ?', [$user['id'], $id])->fetchColumn()) {
        db()->beginTransaction();
        try { query('INSERT INTO progress (user_id, lesson_id, completed_at) VALUES (?, ?, ?)', [$user['id'], $id, gmdate('Y-m-d H:i:s')]); record_activity((int)$user['id'], 'Completed “' . $lesson['title'] . '”'); db()->commit(); }
        catch (Throwable $exception) { db()->rollBack(); throw $exception; }
        flash('Small win! You completed “' . $lesson['title'] . '”.'); redirect('dashboard.php#learning-path');
    } else { flash('You’ve already completed this lesson. Your progress is saved.'); redirect('dashboard.php#learning-path'); }
}
$done = (bool)query('SELECT 1 FROM progress WHERE user_id = ? AND lesson_id = ?', [$user['id'], $id])->fetchColumn();
render_header($lesson['title'], 'dashboard', $user);
?>
<main class="container lesson-page" id="main"><a class="back-link" href="dashboard.php#learning-path">← Back to your learning path</a><div class="lesson-heading"><p class="eyebrow">LESSON <?= e($lesson['number']) ?> / 04 · <?= e($lesson['topic']) ?> · <?= e($lesson['minutes']) ?> MIN</p><h1><?= e($lesson['title']) ?></h1><p class="lead"><?= e($lesson['intro']) ?></p></div><div class="lesson-content"><article class="lesson-reading"><?php foreach ($lesson['sections'] as [$heading, $body]): ?><h2><?= e($heading) ?></h2><p><?= e($body) ?></p><?php endforeach; ?><h2>A little code to try</h2><pre><code><?= e(str_replace('\n', "\n", $lesson['code'])) ?></code></pre><div class="challenge"><p class="eyebrow">MAKE IT REAL</p><h2>Your small challenge</h2><p><?= e($lesson['challenge']) ?></p></div><a href="<?= e($lesson['resource']) ?>" target="_blank" rel="noopener noreferrer" class="text-link">Read the official guide <?= icon('arrow') ?></a></article><aside class="knowledge-card"><span class="tiny-star" aria-hidden="true">✳</span><p class="eyebrow">A QUICK CHECK-IN</p><h2><?= $done ? 'A step you’ve already taken.' : 'Ready for a small win?' ?></h2><?php if ($done): ?><p>You completed this lesson. You can revisit the material whenever you like.</p><a class="button full" href="dashboard.php">Back to my workspace <?= icon('arrow') ?></a><?php else: ?><p>After reading and trying the challenge, answer this question to save your progress.</p><?php if ($error): ?><p class="form-errors" role="alert"><?= e($error) ?></p><?php endif; ?><form method="post" action="lesson.php?id=<?= e($id) ?>"><?= csrf_field() ?><fieldset><legend><?= e($lesson['question']) ?></legend><?php foreach ($lesson['options'] as $index => $option): ?><label class="answer-option"><input type="radio" name="answer" value="<?= $index ?>" required> <span><?= e($option) ?></span></label><?php endforeach; ?></fieldset><button type="submit" class="button full">Check &amp; complete <?= icon('check') ?></button></form><?php endif; ?><p class="small-print">Progress belongs to your account and is saved between sessions.</p></aside></div></main>
<?php render_footer(); ?>
