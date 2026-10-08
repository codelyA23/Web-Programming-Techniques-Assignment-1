<?php
declare(strict_types=1);

// XAMPP local defaults. Set private overrides in database.local.php if needed.
$defaults = [
    'host' => '127.0.0.1',
    'port' => 3306,
    'database' => 'salone_studio',
    'username' => 'root',
    'password' => '',
];
$localFile = __DIR__ . '/database.local.php';
return is_file($localFile) ? array_replace($defaults, require $localFile) : $defaults;
