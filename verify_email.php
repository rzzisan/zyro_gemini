<?php
require_once __DIR__ . '/core/config.php';
require_once ROOT_PATH . '/core/db.php';
require_once ROOT_PATH . '/core/functions.php';
require_once ROOT_PATH . '/models/User.php';

$token = $_GET['token'] ?? null;

if (!$token) {
    redirect('/views/auth/login.php');
}

$db = getDb();
$userModel = new User($db);

if ($userModel->verifyEmailByToken($token)) {
    set_message('Email verified successfully!', 'success');
} else {
    set_message('Invalid or expired token.', 'danger');
}

redirect('/views/dashboard/index.php');
