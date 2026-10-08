<?php
declare(strict_types=1);
// Run against the installed XAMPP application. This test never starts a server.
// Only uniquely named test accounts are removed; other accounts remain untouched.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$base = rtrim($argv[1] ?? 'http://localhost/salone-studio/public/', '/') . '/';
$root = dirname(__DIR__);
$checks = 0;
function check(bool $condition, string $message): void {
    global $checks;
    if (!$condition) throw new RuntimeException($message);
    $checks++; echo "PASS: $message\n";
}
final class Client {
    public string $jar;
    public function __construct() { $this->jar = tempnam(sys_get_temp_dir(), 'salone-cookie-'); }
    public function request(string $path, ?array $data = null, ?string $cookie = null): array {
        global $base;
        $curl = curl_init($base . $path);
        curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 8, CURLOPT_FOLLOWLOCATION => false, CURLOPT_COOKIEFILE => $this->jar, CURLOPT_COOKIEJAR => $this->jar]);
        if ($data !== null) { curl_setopt($curl, CURLOPT_POST, true); curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data)); }
        if ($cookie !== null) curl_setopt($curl, CURLOPT_COOKIE, 'salone_session=' . $cookie);
        $response = curl_exec($curl);
        if ($response === false) { $message = curl_error($curl); curl_close($curl); throw new RuntimeException('Cannot reach XAMPP Apache: ' . $message); }
        $status = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE); $headerSize = (int)curl_getinfo($curl, CURLINFO_HEADER_SIZE); curl_close($curl);
        return [$status, substr($response, $headerSize), substr($response, 0, $headerSize)];
    }
    public function token(string $path): string {
        [$status, $body] = $this->request($path);
        if ($status !== 200 || !preg_match('/name="csrf" value="([a-f0-9]+)"/', $body, $match)) throw new RuntimeException('Cannot read form on ' . $path);
        return $match[1];
    }
    public function session(): string {
        foreach (file($this->jar, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
            $fields = explode("\t", $line);
            if (count($fields) === 7 && $fields[5] === 'salone_session') return $fields[6];
        }
        throw new RuntimeException('No session cookie');
    }
    public function __destruct() { if (is_file($this->jar)) unlink($this->jar); }
}
$c = new Client(); $pdo = null; $accountEmails = []; $failureBuckets = [];
$ipBucket = hash('sha256', 'ip:127.0.0.1'); $ipFailuresAdded = 0; $ipWindow = null;
try {
    [$status, $html, $headers] = $c->request('index.php');
    check($status === 200 && str_contains(strtolower($headers), 'server: apache'), 'Application is served by XAMPP Apache');
    check(str_contains($html, 'django-girls.svg'), 'Home page displays the Django Girls logo');
    check(!str_contains($html, '<script'), 'Application requires no JavaScript');
    check(str_contains($headers, 'HttpOnly') && str_contains($headers, 'SameSite=Lax'), 'Session cookie protections are present');
    check(str_contains($headers, "script-src 'none'"), 'Browser scripts are disabled by the content policy');
    $config = require $root . '/config/database.php';
    $pdo = new PDO(sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $config['host'], $config['port'], $config['database']), $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false]);
    check($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql', 'All accounts are stored in the running MySQL server');
    foreach (['dashboard.php','profile.php','resources.php','lesson.php?id=first-page'] as $path) check($c->request($path)[0] === 303, "$path requires login");
    foreach (['../config/database.php','../database/schema.sql','../storage/','../app/bootstrap.php'] as $path) check($c->request($path)[0] === 403, "$path is protected by Apache");
    check($c->request('logout.php')[0] === 405, 'GET cannot log out a user');
    $suffix = bin2hex(random_bytes(8)); $email = "smoke-$suffix@example.test"; $otherEmail = "smoke-other-$suffix@example.test"; $accountEmails = [$email, $otherEmail];
    $failureBuckets[] = hash('sha256', 'account:' . $email);
    $password = 'Practice-only-2026!';
    $user = ['csrf'=>$c->token('signup.php'),'name'=>'Aminata Test','email'=>$email,'goal'=>'Build my first website','password'=>$password,'confirmation'=>$password];
    check($c->request('signup.php', array_replace($user, ['csrf'=>'bad']))[0] === 403, 'Registration rejects invalid CSRF');
    check($c->request('signup.php', array_replace($user, ['name'=>'A','email'=>'bad','password'=>'short','confirmation'=>'different']))[0] === 422, 'Server validates registration fields');
    check($c->request('signup.php', $user)[0] === 303, 'Registration writes a new MySQL account');
    check($c->request('signup.php', array_replace($user, ['email'=>strtoupper($email)]))[0] === 422, 'Duplicate email is rejected regardless of case');
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?'); $stmt->execute([$email]); $row = $stmt->fetch();
    check($row && $row['password_hash'] !== $password && password_verify($password, $row['password_hash']), 'MySQL stores a verifiable password hash');
    $id = (int)$row['id'];
    $token = $c->token('login.php'); $before = $c->session();
    check($c->request('login.php',['csrf'=>$token,'email'=>$email,'password'=>'wrong'])[0] === 422, 'Wrong password is rejected');
    $ipFailuresAdded++;
    $stmt = $pdo->prepare('SELECT started_at FROM attempts WHERE bucket = ?'); $stmt->execute([$ipBucket]); $ipWindow = $stmt->fetchColumn();
    check($c->request('login.php',['csrf'=>$token,'email'=>$email,'password'=>$password])[0] === 303, 'Correct password logs in');
    check($before !== $c->session(), 'Login rotates the session identifier');
    $html = $c->request('dashboard.php')[1];
    check(str_contains($html,'Hello, Aminata') && str_contains($html,'0 of 4 lessons'), 'Dashboard reads the signed-in user from MySQL');
    $token = $c->token('lesson.php?id=first-page');
    check($c->request('lesson.php?id=first-page',['csrf'=>$token,'answer'=>'0'])[0] === 422, 'Wrong lesson answer cannot save progress');
    check($c->request('lesson.php?id=first-page',['csrf'=>'bad','answer'=>'1'])[0] === 403, 'Progress writes require CSRF');
    check($c->request('lesson.php?id=first-page',['csrf'=>$token,'answer'=>'1'])[0] === 303, 'Correct answer writes learning progress to MySQL');
    $c->request('lesson.php?id=first-page',['csrf'=>$token,'answer'=>'1']);
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM progress WHERE user_id = ?'); $stmt->execute([$id]);
    check((int)$stmt->fetchColumn() === 1, 'Repeated completion does not duplicate a record');
    check(str_contains($c->request('dashboard.php')[1], '1 of 4 lessons'), 'Dashboard shows saved progress');
    foreach (['make-it-yours'=>'0','hello-server'=>'1','safe-by-design'=>'1'] as $lesson => $answer) check($c->request('lesson.php?id='.$lesson,['csrf'=>$c->token('lesson.php?id='.$lesson),'answer'=>$answer])[0] === 303, "$lesson saves progress");
    $html = $c->request('dashboard.php')[1]; check(str_contains($html,'4 of 4 lessons') && str_contains($html,'You made it through the path.'), 'Completed path displays the finished state');
    check($c->request('lesson.php?id=unknown')[0] === 404, 'Unknown lessons return 404');
    check($c->request('profile.php',['csrf'=>$c->token('profile.php'),'name'=>'<script>alert(1)</script>','goal'=>'Support my community','user_id'=>'999'])[0] === 303, 'Profile updates use the session-owned account');
    $html = $c->request('profile.php')[1]; check(str_contains($html,'&lt;script&gt;') && !str_contains($html,'<script>alert(1)</script>'), 'Stored HTML is escaped');
    $other = new Client(); $otherUser = array_replace($user,['csrf'=>$other->token('signup.php'),'email'=>$otherEmail,'name'=>'Second Learner']);
    $other->request('signup.php',$otherUser); $other->request('login.php',['csrf'=>$other->token('login.php'),'email'=>$otherEmail,'password'=>$password]);
    $html = $other->request('dashboard.php')[1]; check(str_contains($html,'0 of 4 lessons') && str_contains($html,'Hello, Second') && !str_contains($html,'alert(1)'), 'Accounts cannot read another user’s data');
    check($c->request('logout.php',['csrf'=>'bad'])[0] === 403, 'Logout rejects invalid CSRF');
    $oldSession = $c->session(); check($c->request('logout.php',['csrf'=>$c->token('dashboard.php')])[0] === 303, 'POST logout destroys the session');
    check($c->request('dashboard.php')[0] === 303, 'Dashboard is inaccessible after logout');
    $replay = new Client(); check($replay->request('dashboard.php', null, $oldSession)[0] === 303, 'Destroyed session cookies cannot be replayed');
    $c->request('login.php',['csrf'=>$c->token('login.php'),'email'=>$email,'password'=>$password]);
    check(str_contains($c->request('dashboard.php')[1],'4 of 4 lessons'), 'MySQL progress persists between sessions');
    $sessionPath = $root . '/storage/sessions/sess_' . $c->session();
    $sessionData = file_get_contents($sessionPath);
    file_put_contents($sessionPath,preg_replace('/last_active\|i:\d+;/', 'last_active|i:'.(time()-1900).';',$sessionData));
    check($c->request('dashboard.php')[0] === 303, 'Idle sessions expire after 30 minutes');
    $attackEmail = "smoke-unknown-$suffix@example.test"; $injection = "smoke-$suffix' OR 1=1 --";
    $failureBuckets = [hash('sha256','account:'.$email),hash('sha256','account:'.$attackEmail),hash('sha256','account:'.$injection)];
    $attacker = new Client();
    for($i=0;$i<5;$i++) {
        check($attacker->request('login.php',['csrf'=>$attacker->token('login.php'),'email'=>$attackEmail,'password'=>'bad'])[0] === 422, 'Failed login ' . ($i + 1) . ' is recorded');
        $ipFailuresAdded++;
    }
    check($attacker->request('login.php',['csrf'=>$attacker->token('login.php'),'email'=>$attackEmail,'password'=>'bad'])[0] === 429, 'MySQL-backed login throttling blocks repeated failures');
    check(str_contains($attacker->request('login.php',['csrf'=>$attacker->token('login.php'),'email'=>$injection,'password'=>'bad'])[1],'The email or password is incorrect'), 'SQL injection cannot bypass login');
    $ipFailuresAdded++;
    echo "\n$checks XAMPP checks passed.\n";
} catch (Throwable $error) {
    fwrite(STDERR, 'FAIL: '.$error->getMessage()."\n");
    $failed = true;
} finally {
    if($pdo && $accountEmails) {
        $stmt = $pdo->prepare('DELETE FROM users WHERE email = ?'); foreach($accountEmails as $email) $stmt->execute([$email]);
        $stmt = $pdo->prepare('DELETE FROM attempts WHERE bucket = ?'); foreach($failureBuckets as $bucket) $stmt->execute([$bucket]);
        if ($ipWindow !== null && $ipFailuresAdded > 0) {
            // Remove only this suite's contribution, preserving other local login failures.
            $stmt = $pdo->prepare('UPDATE attempts SET failures = GREATEST(CAST(failures AS SIGNED) - ?, 0) WHERE bucket = ? AND started_at = ?');
            $stmt->execute([$ipFailuresAdded, $ipBucket, $ipWindow]);
            $stmt = $pdo->prepare('DELETE FROM attempts WHERE bucket = ? AND failures = 0'); $stmt->execute([$ipBucket]);
        }
        echo "Test accounts removed; other accounts preserved.\n";
    }
}
exit(isset($failed) ? 1 : 0);
