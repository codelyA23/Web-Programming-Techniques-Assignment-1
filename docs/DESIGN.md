# Design rationale

Salone Studio treats authentication as the entrance to a useful learning space. A user creates an account, chooses a learning goal, then sees a dashboard that changes as they complete lessons. This gives the required personalised information a clear purpose rather than filling the dashboard with invented statistics.

The concept is rooted in Sierra Leone through its name, restrained flag detail, Freetown coordinates, and original coast-and-hills illustration. The official Freetown workshop page establishes the local organisational context. Historical workshop information is not reused as a current event announcement.

The official Django Girls logo appears at the top of every page. A warm orange palette references the organisation's visual identity. Cream backgrounds, deep navy headings and white surfaces echo the supplied visual reference. Orange pill buttons, a floating navigation panel and illustrated learning cards bring its airy style into the coding-community concept. Soft layered shadows distinguish raised controls from page backgrounds while keeping the logo intact. No relationship with a clothing brand is claimed because it could not be verified.

The home page explains the journey before asking for an account. Registration and login use paired editorial panels and compact forms. Orange primary buttons remain consistent across the interface. White cards use a shared shadow scale; the progress card receives a stronger orange treatment to make the learner's actual progress easy to find. The dashboard gives the next lesson priority, followed by progress, the user's goal, lessons, and activity.

Bold Segoe UI headings, readable Tahoma fallbacks and generous spacing establish the hierarchy. The small footer wordmark retains its Georgia accent. Avoiding remote fonts makes the site usable offline and reduces requests. All art is local SVG and remains sharp at different sizes.

Mobile layouts stack the hero, cards, forms, and dashboard content. The workspace sidebar becomes compact navigation. Controls have explicit labels, keyboard focus indicators, error feedback, and accessible descriptions. Colour is supported by text labels such as Completed and Up next. Cards lift on hover for devices with a fine pointer, buttons gain stronger shadows, and action arrows shift gently. Touch layouts avoid hover-dependent content. Reduced-motion preferences disable smooth scrolling and transitions.

No purchased theme or third-party plugin is used. PHP renders HTML and handles all forms; local CSS and SVG assets supply the design. The custom theme.css shares the existing layouts and form behaviours in style.css, keeping visual changes separate from authentication. The application contains no JavaScript.

The database is XAMPP MySQL/MariaDB through PDO with native prepared statements, foreign keys and unique constraints. The project runs from C:/xampp/htdocs/salone-studio through Apache. Import its schema with phpMyAdmin. Apache protects private folders using .htaccess. MySQL must be running for all account reads and writes; there is no fallback database. Runtime sessions and credentials are excluded from Git.

AI assistance should be disclosed according to the university's policy. The student should review the implementation and verify the supplied footer details before submission.
