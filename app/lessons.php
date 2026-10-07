<?php
declare(strict_types=1);

// A small original curriculum for this prototype, rather than fabricated workshop data.
function lessons(): array {
    return [
        'first-page' => [
            'number' => '01', 'title' => 'Your first page', 'topic' => 'HTML', 'minutes' => 12,
            'intro' => 'Every website starts with a little structure. HTML tells a browser what each piece of content means: a heading, a paragraph, a link, or an image.',
            'sections' => [
                ['Start with meaning', 'Use one main heading for your page, then smaller headings for sections. A paragraph belongs in a p element. A link belongs in an a element. Semantic markup helps people using screen readers understand your page.'],
                ['Build a tiny community page', 'Create a file called index.html. Give it a title and describe something you love about your community. Open it in your browser, change one sentence, and refresh. That edit-and-check loop is how you learn.'],
                ['Make it accessible', 'Add lang="en" to the html element, a viewport meta tag for mobile devices, and useful alternative text for meaningful images. A descriptive link such as “Read the workshop guide” is clearer than “Click here”.'],
            ],
            'code' => '<!doctype html>\n<html lang="en">\n  <head><meta charset="utf-8"><title>My community</title></head>\n  <body>\n    <h1>Hello from Freetown!</h1>\n    <p>This is the beginning of my web journey.</p>\n  </body>\n</html>',
            'challenge' => 'Create a page introducing a place in Sierra Leone. Include a heading, two paragraphs, and a descriptive link. Check that it still reads clearly without any styling.',
            'question' => 'Which element represents the main heading?', 'options' => ['<p>', '<h1>', '<a>'], 'answer' => '1',
            'resource' => 'https://developer.mozilla.org/en-US/docs/Learn_web_development/Core/Structuring_content',
        ],
        'make-it-yours' => [
            'number' => '02', 'title' => 'Make it yours', 'topic' => 'CSS', 'minutes' => 15,
            'intro' => 'CSS turns your structure into a visual experience. Thoughtful spacing and readable text matter more than adding effects everywhere.',
            'sections' => [
                ['Give your content room', 'Begin with a readable line length and generous spacing. A max-width stops paragraphs from stretching across large screens. Padding keeps content away from the edges on a small phone.'],
                ['Design for a small screen first', 'Build the single-column layout first. Use a media query to introduce columns when there is room. Flexible units and wrapping layouts help your design adapt to different devices.'],
                ['Keep interaction visible', 'Give links, buttons, and focused controls a clear visual state. Never remove the keyboard focus outline without providing an equally visible replacement. Use sufficient contrast between text and background.'],
            ],
            'code' => 'body {\n  margin: 0;\n  background: #faf7ef;\n  color: #262b26;\n  font-family: system-ui, sans-serif;\n}\nmain { max-width: 65ch; margin: auto; padding: 2rem; }\na:focus-visible { outline: 3px solid #765800; }',
            'challenge' => 'Style your community page with two colours and a clear heading hierarchy. Test at a narrow window width and use the Tab key to reach every link.',
            'question' => 'What limits the width of a paragraph on a large screen?', 'options' => ['max-width', 'font-weight', 'border-radius'], 'answer' => '0',
            'resource' => 'https://developer.mozilla.org/en-US/docs/Learn_web_development/Core/Styling_basics',
        ],
        'hello-server' => [
            'number' => '03', 'title' => 'Hello, server', 'topic' => 'PHP', 'minutes' => 18,
            'intro' => 'PHP runs on the server and sends HTML to the browser. It can work with submitted forms, read a database, and personalise the next page.',
            'sections' => [
                ['Follow the request', 'The browser requests a PHP page. The server executes the PHP code, then sends its output to the browser. The browser receives HTML, not your PHP source. A variable starts with a dollar sign.'],
                ['Validate before you store', 'Browser validation is helpful, but the server must validate every submission again. Check required fields, lengths, and email formats. Prepared SQL statements keep user values separate from SQL instructions.'],
                ['Escape before you display', 'Never insert untrusted text directly into HTML. htmlspecialchars converts characters such as angle brackets into safe text. A name should appear as a name, even if someone enters HTML into the field.'],
            ],
            'code' => '<?php\n$name = "Aminata";\n$safeName = htmlspecialchars($name, ENT_QUOTES, "UTF-8");\necho "<h1>Hello, " . $safeName . "!</h1>";\n?>',
            'challenge' => 'Write a PHP page that displays a greeting from a variable. Then build a POST form and validate the submitted name on the server before displaying it safely.',
            'question' => 'Where does PHP execute?', 'options' => ['Only in the browser', 'On the server', 'Inside a CSS file'], 'answer' => '1',
            'resource' => 'https://www.php.net/manual/en/tutorial.php',
        ],
        'safe-by-design' => [
            'number' => '04', 'title' => 'Safe by design', 'topic' => 'SECURITY', 'minutes' => 15,
            'intro' => 'An account is a promise to protect someone’s information. Good authentication checks identity and protects the session that follows.',
            'sections' => [
                ['Hash a password', 'Store a password hash with password_hash, then check the supplied password using password_verify. Hashing is one-way; it is not the same as encryption. Never store or log a plain password.'],
                ['Protect a session', 'After login, regenerate the session ID. Only the signed-in user should be able to reach their dashboard. Expire idle sessions and destroy the session on logout. HttpOnly prevents JavaScript from reading the session cookie; HTTPS protects it during transport.'],
                ['Check the intention', 'State-changing forms need a CSRF token tied to the session. Reject a request with an invalid token. Limit repeated failed login attempts and show a generic failure message that does not confirm whether an email is registered.'],
            ],
            'code' => '<?php\n$hash = password_hash($password, PASSWORD_DEFAULT);\nif (password_verify($submittedPassword, $hash)) {\n    session_regenerate_id(true);\n    $_SESSION["user_id"] = $userId;\n}\n?>',
            'challenge' => 'Trace this portal’s login from the form to password_verify and session_regenerate_id. Explain why logout is a POST request and why opening dashboard.php without a session redirects to login.',
            'question' => 'Which function safely checks a password against its hash?', 'options' => ['md5', 'password_verify', 'base64_encode'], 'answer' => '1',
            'resource' => 'https://www.php.net/manual/en/function.password-hash.php',
        ],
    ];
}
