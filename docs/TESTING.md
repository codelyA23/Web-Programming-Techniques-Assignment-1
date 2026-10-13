# Verification record

## XAMPP integration checks

`tests/smoke.php` passed **50 checks** on 8 October 2026, and again on 13 October after the visual refresh, against XAMPP Apache and MySQL/MariaDB. It submits real HTTP requests and verifies records through PDO MySQL. It never starts services or uses another database. Unique test accounts, their cascading progress/activity records, and the suite's rate-limit contributions are removed afterwards; other accounts remain untouched.

Coverage: Apache serving; logo; script-free HTML and policy; cookie protections; four authentication guards; four Apache private-folder protections; POST-only logout; invalid CSRF; server validation; registration; duplicate emails; password hash; wrong/correct login; session rotation; personalised dashboard; lesson validation; progress CSRF; completion; duplicate completion; all four lessons; finished state; unknown lesson; session-owned profile updates; output escaping; account isolation; logout CSRF; session destruction; access after logout; cookie replay; persistent MySQL progress; idle expiry; recorded failures; throttling; SQL injection resistance.

All PHP source files passed syntax checks. Direct CLI execution of an application page was rejected with XAMPP startup guidance. Only the test runner uses PHP CLI, to submit HTTP requests to Apache.

## Service dependency

With Apache running and MySQL stopped, an actual login POST returned **HTTP 503**, with instructions to start MySQL. No database fallback exists. After the user restarted MySQL, all 50 integration checks passed. The application rejects PHP's standalone server and CLI because it requires Apache's PHP module. If Apache is stopped, no PHP page can load through localhost. Public static content can display with Apache alone because it does not query account records.

## Browser evidence

Screenshots in `docs/screenshots/` document the home, signup, login and dashboard served through XAMPP Apache. Desktop, tablet and mobile layouts were reviewed at 1365, 768, 390 and 320 pixels. The redesigned navigation, orange actions, raised cards and persistent dashboard progress were checked; no horizontal overflow was observed. The lightest button gradient colour has a measured white-text contrast ratio of 4.71:1. Focus indicators and reduced-motion rules are retained. Password fields remain concealed; there is no JavaScript visibility toggle.

## Practical limits

This is coursework verification rather than a production audit. Local XAMPP uses HTTP; Secure cookies are enabled when served through HTTPS. Public hosting requires HTTPS and a dedicated restricted database user. `.htaccess` protection requires Apache `AllowOverride All`, enabled in the tested XAMPP installation. Email verification, recovery and administrator roles are outside the brief.
