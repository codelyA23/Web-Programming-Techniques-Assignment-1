# Salone Studio

An original PHP authentication system and personalised learning dashboard for **Web Programming Assignment 1**, inspired by the Django Girls Freetown community in Sierra Leone.

![Home page](docs/screenshots/home-desktop.jpg)

## Run locally

Requirements: PHP 8.1+ with `pdo_sqlite` and `mbstring`. XAMPP's PHP includes these extensions. No Composer, npm, database server, or Internet connection is required to run the app.

From this project folder:

```powershell
C:\xampp\php\php.exe -S 127.0.0.1:8087 -t public
```

Open **http://127.0.0.1:8087**. Create an account and log in. The first database operation automatically creates the SQLite database in `storage/`. Stop the server with Ctrl+C.

For a presentation account, optionally run:

```powershell
C:\xampp\php\php.exe bin/create-demo.php
```

Then log in with `demo@example.test` / `Demo-only-2026!`. This is a disposable local demonstration account. **Do not run this optional command on a public server.** There is no automatic seeded login in the application.

### Apache or XAMPP

Configure the Apache document root or a virtual host to point to this project's **public** directory. Do not serve the project root: `app/`, `config/`, `bin/`, and `storage/` must remain outside the web root. The PHP development server command above is the simplest local setup. SQLite is intentional: the supplied rubric requires PHP authentication but does not require MySQL.

For public hosting, use HTTPS, ensure PHP errors are logged rather than displayed, disable the optional demo account, and provide a writable private storage directory. The PHP development server is for local use only.

## Features

- Original home page with an official Django Girls logo and custom Freetown-inspired SVG illustration.
- Registration with server validation, normalised unique email addresses, password confirmation, and password hashing.
- Login, session ID rotation, 30-minute idle timeout, and POST-only secure logout.
- Dashboard with the user's actual name, goal, saved progress, and activity history.
- Four original short lessons with server-checked questions and persistent completion records.
- Editable profile and a resource shelf linking to official learning materials.
- Responsive layouts, labelled controls, keyboard focus styles, reduced-motion support, and validation feedback.
- Local assets and progressive enhancement: the core workflow works without JavaScript.

## Assignment checklist

| Criterion | Evidence |
| --- | --- |
| Logo display | Official Django Girls logo in the header on every page |
| Login | `public/login.php` with `password_verify` |
| Sign up | `public/signup.php`, validation and a unique database constraint |
| Logout | `public/logout.php`, CSRF check and session destruction |
| Dashboard | `public/dashboard.php`, real account-specific information |
| Security | Password hashes, prepared statements, escaped output, CSRF, session protection and rate limits |
| User experience | Responsive design, form feedback, visible focus and accessible labels |
| Footer | `config/site.php` supplies name, programme, semester, ID and year |
| Documentation | Five-page report and home-page screenshots in `docs/` |

**Before submission:** replace the name and student ID placeholders in `config/site.php`, regenerate screenshots and the report, print the report, and personally complete and sign the university's assessment form. No assessment form was supplied with the brief; this project does not substitute a signed form.

## Project structure

```text
app/          Shared security, database, layout helpers and lesson content
bin/          Optional local presentation account tool
config/       Student footer details
public/       Only web-accessible PHP pages and local assets
storage/      Private runtime database and sessions, excluded from Git
docs/         Report, design notes, walkthrough and screenshots
tests/        HTTP integration tests using disposable data
```

## Verify

```powershell
python tests/smoke.py --php C:/xampp/php/php.exe
```

The suite starts its own local server on an available port, creates a temporary database and sessions, runs **39 HTTP checks**, and removes the temporary data. Python 3 uses only its standard library. See `docs/TESTING.md` for the recorded checks and browser verification.

## Design and attribution

No theme, UI framework, font CDN, or third-party plugin is used. Segoe UI/Tahoma provide a reliable local sans serif; Georgia supplies the editorial accent. Warm orange is inspired by Django Girls; sage green and a coast-and-hills illustration establish the Sierra Leone context.

- Logo: [Django Girls official resources](https://github.com/DjangoGirls/resources/blob/master/Design/Logo/logo-level-black.svg), upstream commit `85f81166359a90fe4b52f80904875288d66ceefc`.
- Organisation: [Django Girls](https://djangogirls.org/en/).
- Local context: [Django Girls Freetown workshop page](https://djangogirls.org/de/freetown1/), describing the October 2017 event. This project does not claim a current event.
- Asset terms: the official resources README asks non-organisers to obtain permission from Django Girls before reuse. The logo remains theirs; it is not covered by any project licence. Confirm permission before external reuse.
- The illustration, layouts, and lesson text are original to this student prototype. It is not an official Django Girls service.

## Learning and authorship

This project was developed with AI assistance. Review the source, run the tests, and make sure you can explain the authentication flow and design choices. Follow your university's policy on assistance and attribution. Do not present an invented development history or claim unaided authorship.

This coursework prototype does not include email verification, password recovery, administrator roles, or a production security audit. Those features are outside the supplied authentication-and-dashboard brief.
