<?php
session_start();
require_once __DIR__ . '/core/config.php';
require_once __DIR__ . '/core/functions.php';
require_once ROOT_PATH . '/models/AuthToken.php';

if (isset($_COOKIE['remember_me'])) {
    $token = $_COOKIE['remember_me'];
    $db = getDb();
    $authTokenModel = new AuthToken($db);
    $authTokenModel->delete($token);
    setcookie('remember_me', '', time() - 3600, '/');
}

session_destroy();
redirect('/views/auth/login.php');
?>
