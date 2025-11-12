<?php
require_once __DIR__ . '/../core/config.php';
require_once ROOT_PATH . '/core/functions.php';
require_once ROOT_PATH . '/models/AuthToken.php';

if (isset($_COOKIE['remember_me'])) {
    $token = $_COOKIE['remember_me'];
    $db = getDb();
    $authTokenModel = new AuthToken($db);
    $authTokenModel->delete($token);
    setcookie('remember_me', '', time() - 3600, '/');
}

// Unset all of the session variables.
$_SESSION = array();

// If it's desired to kill the session, also delete the session cookie.
// Note: This will destroy the session, and not just the session data!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session.
session_destroy();

redirect('/admin/login.php');
