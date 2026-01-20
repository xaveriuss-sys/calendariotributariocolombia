<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/src/config.php';
require_once dirname(__DIR__) . '/src/Auth.php';

use App\Auth;

$pdo = getConnection();
if ($pdo) {
    $auth = new Auth($pdo);
    $auth->logout();
} else {
    session_start();
    session_destroy();
}

header('Location: login.php');
exit;
