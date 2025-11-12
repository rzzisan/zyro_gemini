<?php
require_once __DIR__ . '/../models/AuthToken.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/functions.php';

function check_remember_me() {
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
                if ($user['role'] === 'admin') {
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['admin_name'] = $user['name'];
                    $_SESSION['is_admin'] = true;
                } else {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                }
            }
        }
    }
}
