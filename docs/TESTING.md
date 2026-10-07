# Verification record

## Automated checks

`tests/smoke.py` passed all **39 HTTP integration checks** during development. It uses a separate temporary database and starts its own PHP server. These checks verify behaviour rather than mirroring individual lines of implementation.

Covered: logo presence; cookie protections; content security policy; authentication guards on four pages; private database location; POST-only logout; invalid CSRF rejection; invalid registration; successful registration; duplicate emails; stored password hash; wrong and correct passwords; session rotation; personalised dashboard; wrong and correct lesson answers; CSRF protection for progress; duplicate completion; dashboard updates; all four lessons; completed-path state; missing lessons; session-owned profile updates; output escaping; account isolation; invalid and valid logout; access after logout; replay of a destroyed cookie; persistent progress; idle expiry; repeated failure throttling; and SQL injection resistance.

All PHP source files also passed PHP's syntax check.

## Browser checks

See the saved screenshots in `docs/screenshots/` for the home page, signup, login, and dashboard. Browser inspection covers desktop and mobile layouts and the main navigation, form labels, password visibility control, knowledge check, profile, and logout workflow.

## Practical limits

The automated suite is repeatable evidence for the coursework workflow, not a production audit. The local development server uses HTTP, so the cookie's Secure flag is enabled only when the request is served through HTTPS. Server deployments must expose only `public/`, keep storage private, and use HTTPS.

The email field identifies an account but is not verified through an email service. Rate limiting protects against repeated failures with account and IP buckets; a production system would need a broader abuse strategy. No password-recovery or administrator feature is included in the brief.
