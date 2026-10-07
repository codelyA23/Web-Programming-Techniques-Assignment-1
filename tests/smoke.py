"""HTTP integration checks against a disposable PHP server and SQLite database.

Run: python tests/smoke.py --php C:/xampp/php/php.exe
No account, database, or cookie from the real app is used.
"""
import argparse
import http.cookiejar
import os
from pathlib import Path
import re
import socket
import sqlite3
import subprocess
import tempfile
import time
import urllib.error
import urllib.parse
import urllib.request

parser = argparse.ArgumentParser()
parser.add_argument('--php', default='php')
args = parser.parse_args()
root = Path(__file__).resolve().parents[1]
checks = 0

def check(condition, message):
    global checks
    if not condition:
        raise AssertionError(message)
    checks += 1
    print('PASS:', message)

class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, *args, **kwargs):
        return None

class Client:
    def __init__(self):
        self.cookies = http.cookiejar.CookieJar()
        self.opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(self.cookies), NoRedirect())
    def request(self, path, data=None):
        request = urllib.request.Request(base + path, data=urllib.parse.urlencode(data).encode() if data is not None else None)
        try:
            response = self.opener.open(request, timeout=10)
        except urllib.error.HTTPError as error:
            response = error
        with response:
            return response.code, response.read().decode(), response.headers
    def token(self, path):
        status, html, _ = self.request(path)
        assert status == 200
        return re.search(r'name="csrf" value="([a-f0-9]+)"', html).group(1)
    def session(self):
        return next(cookie.value for cookie in self.cookies if cookie.name == 'salone_session')

with socket.socket() as sock:
    sock.bind(('127.0.0.1', 0))
    port = sock.getsockname()[1]
base = f'http://127.0.0.1:{port}/'
with tempfile.TemporaryDirectory(prefix='salone-test-') as directory:
    env = dict(os.environ, APP_STORAGE=directory)
    with open(Path(directory) / 'server.log', 'w') as log:
        server = subprocess.Popen([args.php, '-S', f'127.0.0.1:{port}', '-t', str(root / 'public')], cwd=root, env=env, stdout=log, stderr=log)
        try:
            for _ in range(50):
                try:
                    with socket.create_connection(('127.0.0.1', port), timeout=.2):
                        break
                except OSError:
                    time.sleep(.1)
            c = Client()
            status, html, headers = c.request('index.php')
            check(status == 200 and 'django-girls.svg' in html, 'Home page includes the Django Girls logo')
            check('HttpOnly' in headers.get('Set-Cookie', '') and 'SameSite=Lax' in headers.get('Set-Cookie', ''), 'Session cookie has HttpOnly and SameSite protections')
            check("script-src 'self'" in headers.get('Content-Security-Policy', ''), 'Content security policy restricts scripts')
            for page in ['dashboard.php', 'profile.php', 'resources.php', 'lesson.php?id=first-page']:
                status, _, headers = c.request(page)
                check(status == 303 and headers['Location'] == 'login.php', f'{page} requires authentication')
            check(c.request('../storage/portal.sqlite')[0] == 404, 'Database is outside the public document root')
            check(c.request('logout.php')[0] == 405, 'GET requests cannot log out a user')
            token = c.token('signup.php')
            user = {'csrf': token, 'name': 'Aminata Test', 'email': 'aminata@example.test', 'goal': 'Build my first website', 'password': 'Practice-only-2026!', 'confirmation': 'Practice-only-2026!'}
            check(c.request('signup.php', dict(user, csrf='bad'))[0] == 403, 'Invalid registration CSRF token is rejected')
            check(c.request('signup.php', dict(user, name='A', email='bad', password='short', confirmation='different'))[0] == 422, 'Invalid registration data is rejected on the server')
            status, _, headers = c.request('signup.php', user)
            check(status == 303 and headers['Location'] == 'login.php', 'Valid registration creates an account and redirects to login')
            token = c.token('signup.php')
            check(c.request('signup.php', dict(user, csrf=token, email='AMINATA@example.test'))[0] == 422, 'Case-insensitive duplicate registration is rejected')
            connection = sqlite3.connect(Path(directory) / 'portal.sqlite')
            stored = connection.execute('SELECT password_hash FROM users').fetchone()[0]
            check(stored != user['password'] and stored.startswith('$2y$'), 'Database stores a password hash, not the password')
            token = c.token('login.php')
            check(c.request('login.php', {'csrf': token, 'email': user['email'], 'password': 'wrong'})[0] == 422, 'Wrong password is rejected')
            old_session = c.session()
            status, _, headers = c.request('login.php', {'csrf': token, 'email': user['email'], 'password': user['password']})
            check(status == 303 and headers['Location'] == 'dashboard.php', 'Correct password authenticates the account')
            check(old_session != c.session(), 'Login rotates the session identifier')
            status, html, _ = c.request('dashboard.php')
            check(status == 200 and 'Hello, Aminata' in html and '0 of 4 lessons' in html, 'Dashboard shows real personalised account data')
            token = c.token('lesson.php?id=first-page')
            check(c.request('lesson.php?id=first-page', {'csrf': token, 'answer': '0'})[0] == 422, 'Wrong knowledge-check answer does not complete a lesson')
            check(c.request('lesson.php?id=first-page', {'csrf': 'bad', 'answer': '1'})[0] == 403, 'Progress changes require a valid CSRF token')
            check(c.request('lesson.php?id=first-page', {'csrf': token, 'answer': '1'})[0] == 303, 'Correct knowledge-check answer saves progress')
            c.request('lesson.php?id=first-page', {'csrf': token, 'answer': '1'})
            check(connection.execute('SELECT COUNT(*) FROM progress').fetchone()[0] == 1, 'Completing a lesson twice does not duplicate progress')
            check('1 of 4 lessons' in c.request('dashboard.php')[1], 'Dashboard reflects saved learning progress')
            for lesson, answer in [('make-it-yours','0'), ('hello-server','1'), ('safe-by-design','1')]:
                token = c.token('lesson.php?id=' + lesson)
                check(c.request('lesson.php?id=' + lesson, {'csrf': token, 'answer': answer})[0] == 303, f'{lesson} can be completed')
            check('4 of 4 lessons' in c.request('dashboard.php')[1] and 'You made it through the path.' in c.request('dashboard.php')[1], 'Finished path displays completion state')
            check(c.request('lesson.php?id=unknown')[0] == 404, 'Unknown lesson returns 404')
            token = c.token('profile.php')
            check(c.request('profile.php', {'csrf': token, 'name': '<script>alert(1)</script>', 'goal': 'Support my community', 'user_id': '999'})[0] == 303, 'Profile updates only the signed-in account')
            html = c.request('profile.php')[1]
            check('&lt;script&gt;' in html and '<script>alert(1)</script>' not in html, 'Stored HTML in a name is escaped on output')
            outsider = Client()
            other = dict(user, csrf=outsider.token('signup.php'), name='Second Learner', email='second@example.test')
            outsider.request('signup.php', other)
            outsider.request('login.php', {'csrf': outsider.token('login.php'), 'email': other['email'], 'password': other['password']})
            html = outsider.request('dashboard.php')[1]
            check('0 of 4 lessons' in html and 'Hello, Second' in html and 'alert(1)' not in html, 'Accounts cannot see another user’s progress or profile')
            token = c.token('dashboard.php')
            check(c.request('logout.php', {'csrf': 'bad'})[0] == 403, 'Invalid logout CSRF token is rejected')
            old_session = c.session()
            check(c.request('logout.php', {'csrf': token})[0] == 303, 'POST logout ends the session')
            check(c.request('dashboard.php')[0] == 303, 'Dashboard is inaccessible after logout')
            replay = urllib.request.Request(base + 'dashboard.php', headers={'Cookie': 'salone_session=' + old_session})
            try:
                urllib.request.build_opener(NoRedirect()).open(replay)
                replay_status = 200
            except urllib.error.HTTPError as error:
                replay_status = error.code
            check(replay_status == 303, 'Destroyed session cookie cannot be replayed')
            token = c.token('login.php')
            c.request('login.php', {'csrf': token, 'email': user['email'], 'password': user['password']})
            check('4 of 4 lessons' in c.request('dashboard.php')[1], 'Progress persists after logout and a new login')
            session_path = Path(directory) / 'sessions' / ('sess_' + c.session())
            session_text = session_path.read_text()
            session_text = re.sub(r'last_active\|i:\d+;', f'last_active|i:{int(time.time())-1900};', session_text)
            session_path.write_text(session_text)
            check(c.request('dashboard.php')[0] == 303, 'An idle session expires after 30 minutes')
            attacker = Client()
            for _ in range(5):
                attacker.request('login.php', {'csrf': attacker.token('login.php'), 'email': 'unknown@example.test', 'password': 'not-a-password'})
            check(attacker.request('login.php', {'csrf': attacker.token('login.php'), 'email': 'unknown@example.test', 'password': 'not-a-password'})[0] == 429, 'Repeated failed login attempts are rate limited')
            html = attacker.request('login.php', {'csrf': attacker.token('login.php'), 'email': "' OR 1=1 --", 'password': 'not-a-password'})[1]
            check('The email or password is incorrect' in html, 'SQL injection text does not bypass login')
            connection.close()
            print(f'\n{checks} checks passed. Disposable test data removed.')
        finally:
            server.terminate()
            server.wait(timeout=10)
