# Salone Studio

A PHP authentication system and personalised learning dashboard for **Web Programming Assignment 1**, inspired by Django Girls Freetown in Sierra Leone.

![Home page](docs/screenshots/home-desktop.jpg)

## Run with XAMPP

1. Copy the project's contents into **`C:\xampp\htdocs\salone-studio`**, including the `.htaccess` files.
2. Start **Apache and MySQL** in the XAMPP Control Panel.
3. Open **http://localhost/phpmyadmin**, choose Import, and select `database/schema.sql`. This creates `salone_studio` and four tables without dropping existing data.
4. Open **http://localhost/salone-studio/**, create an account, and log in.

XAMPP's PHP needs `pdo_mysql` and `mbstring`. The database is MySQL/MariaDB. Default local credentials are **root with an empty password**, as confirmed for this installation. Change `config/database.php` for another classroom setup, or create an ignored `config/database.local.php` returning an array of overrides. Keep credentials out of GitHub.

Apache must serve the application. PHP's standalone server and CLI page execution are rejected. MySQL must be running for account and progress reads and writes. When MySQL stops, database-dependent requests return HTTP 503 with setup guidance. The public homepage can still display with Apache running because it does not query account data. There is no SQLite, browser storage, mock API or fallback database.

PHP renders HTML and processes every form. Local CSS and SVG files provide the appearance. No Composer, npm, Node server, JavaScript or frontend framework is required. Installation creates no default account.

## Features

- Original responsive homepage with the official Django Girls logo and custom Freetown-inspired illustration.
- Registration with server validation, unique normalised emails, password confirmation and hashing.
- Login, session rotation, 30-minute idle expiry and POST-only secure logout.
- Personal dashboard showing actual MySQL account details, goals, progress and activity.
- Four original lessons with PHP-validated questions and saved completion records.
- Profile editing, official learning resources, keyboard focus and labelled controls.

## Assignment checklist

| Criterion | Evidence |
| --- | --- |
| Logo | Official logo in the shared header |
| Login and signup | PHP forms, validation, password hashing and verification |
| Logout | POST, CSRF check and session destruction |
| Dashboard | Real account-specific goals, progress and activity |
| Security | Native prepared statements, escaping, CSRF, protected sessions and MySQL rate limits |
| Footer | Name, programme, semester, ID and year from `config/site.php` |
| Documentation | Five-page Tahoma 12 report and homepage screenshot in `docs/` |

Student details are configured as **Mohamed Sow, BSEM, Semester 5, ID 905004376, 2026**. Before submission, review these details, print the report, and personally complete and sign the lecturer's assessment form.

## Structure

```text
index.php     Apache-only entry forwarding to public/
app/          Shared PHP security, database, views and lessons
config/       MySQL connection and student footer settings
database/     MySQL schema for phpMyAdmin import
public/       PHP pages, shared layouts, custom CSS theme and SVG assets
storage/      Private PHP sessions; runtime data excluded from Git
docs/         Report, walkthrough, design notes and screenshots
tests/        PHP HTTP checks through running XAMPP services
```

Apache denies HTTP access to private folders using `.htaccess`. Directory listings are disabled. Database records live in MySQL, not `storage/`.

## Verify

With both services running and the schema imported:

```powershell
C:\xampp\php\php.exe C:\xampp\htdocs\salone-studio\tests\smoke.php
```

The PHP suite passed **50 checks** against Apache and MySQL. It submits real HTTP forms, checks database records, and removes only its own test accounts and rate-limit contributions. It never starts services or creates a replacement database. See `docs/TESTING.md`.

## Design and attribution

No purchased theme, UI framework, font CDN or third-party plugin is used. The custom CSS uses cream backgrounds, navy headings, orange pill buttons, illustrated cards and layered shadows. Local Segoe UI/Tahoma provide typography, with a Georgia accent in the footer wordmark. Hover effects respect reduced-motion preferences.

- Logo: [Official Django Girls resources](https://github.com/DjangoGirls/resources/blob/master/Design/Logo/logo-level-black.svg), upstream commit `85f81166359a90fe4b52f80904875288d66ceefc`.
- Organisation: [Django Girls](https://djangogirls.org/en/).
- Local context: [Freetown workshop](https://djangogirls.org/de/freetown1/), an October 2017 event. The project does not announce a current event.
- Official resource terms ask non-organisers to obtain permission before reuse. The logo remains theirs; confirm permission before external reuse.
- Artwork, layouts and lessons are original to this independent student prototype.

AI assistance was used. Review the source and be ready to explain its authentication and design choices. Follow the university's assistance policy. This local coursework prototype does not include email verification, password recovery, administrator roles or a production audit. Public deployment would require HTTPS and a dedicated restricted database user.
