<?php
declare(strict_types=1);
if (PHP_SAPI !== 'apache2handler') {
    http_response_code(503);
    exit('Start Apache in XAMPP, then open http://localhost/salone-studio/.');
}
header('Location: public/', true, 302);
exit;
