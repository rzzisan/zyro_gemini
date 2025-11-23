<?php
require_once __DIR__ . '/../models/AuthToken.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/functions.php';

function check_remember_me() {
    // If session is already set, no need to check cookie
    if (isset($_SESSION['user_id']) || isset($_SESSION['admin_id'])) {
        return;
    }

    if (isset($_COOKIE['remember_me'])) {
        $token = $_COOKIE['remember_me'];
        $db = getDb();
        $authTokenModel = new AuthToken($db);
        $token_data = $authTokenModel->findByToken($token);

        if ($token_data) {
            $userModel = new User($db);
            $user = $userModel->find($token_data['user_id']);

            if ($user) {
                session_regenerate_id(true);

                // Always set User Session (Required for User Dashboard access even for admins)
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];

                // If Admin, set Admin Session variables as well
                if ($user['role'] === 'admin') {
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['admin_name'] = $user['name'];
                    $_SESSION['is_admin'] = true;
                }
            }
        }
    }
}
