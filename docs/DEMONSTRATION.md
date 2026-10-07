# Presentation walkthrough

1. Start PHP with `-t public` and open the home page. Point out the Django Girls logo, Sierra Leone detail, original illustration, three-step journey and student footer.
2. Create an account with a test email and a password of at least 10 bytes. Show the name, email, goal and confirmation fields. Explain that browser checks improve feedback while the server validates again.
3. Log in with a wrong password to show the generic error, then log in correctly. Explain `password_hash`, `password_verify` and session ID rotation.
4. Show the dashboard. A new account has zero completed lessons. Its greeting and goal come from the database rather than static text.
5. Open Your first page, read the lesson, and submit the knowledge check. Return to the dashboard to show 25% progress and a new activity entry.
6. Update the learning goal on My profile. Show the change on the dashboard. The form derives the account ID from the session.
7. Open the resource shelf and explain that each guide links to its official publisher.
8. Use Log out. Try opening `dashboard.php` again to demonstrate the authentication guard.
9. Log in once more to show that the learning progress persists. Narrow the browser to demonstrate responsive layouts.

## Explain these source files

- `app/bootstrap.php`: sessions, security headers, SQLite tables, PDO queries, CSRF, authentication guards and login attempt limits.
- `public/signup.php`: validation, unique email handling and password hashing.
- `public/login.php`: password verification, failure limits and session rotation.
- `public/logout.php`: POST-only handling, CSRF check and session destruction.
- `public/dashboard.php`: session-specific queries, loops and conditional presentation.
- `public/lesson.php`: server-side question validation and progress persistence.
- `app/lessons.php`: arrays containing the learning content.
- `app/views.php`: reusable functions, output escaping and shared page structure.

## Before handing in

- Replace the student name and ID in `config/site.php`.
- Regenerate the homepage screenshots after changing the footer.
- Update the matching name and ID in the editable report content, then regenerate its PDF.
- Print the five-page report in Tahoma 12.
- Complete and sign the lecturer's original assessment form yourself.
- Review the university's AI-assistance policy and disclose assistance as required.
