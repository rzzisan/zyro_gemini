<?php
require_once ROOT_PATH . '/core/config.php';

function redirect($path) {
    header("Location: " . APP_URL . $path);
    exit();
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

function ensureAdmin() {
    if (!isAdmin()) {
        redirect('/admin/login.php');
    }
}

function get_user() {
    return isset($_SESSION['user']) ? $_SESSION['user'] : null;
}

function set_message($message, $type = 'success') {
    $_SESSION['message'] = ['message' => $message, 'type' => $type];
}

function display_message() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        echo "<div class='alert alert-" . $message['type'] . "'>" . $message['message'] . "</div>";
        unset($_SESSION['message']);
    }
}

function ensure_logged_in() {
    if (!is_logged_in()) {
        redirect('/views/auth/login.php');
    }
}

function get_user_id() {
    return $_SESSION['user_id'] ?? null;
}

function getDb() {
    static $pdo = null;
    if ($pdo === null) {
        require_once ROOT_PATH . '/core/db.php';
        $pdo = $GLOBALS['pdo'];
    }
    return $pdo;
}

function is_active($path) {
    $request_uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $app_url_path = parse_url(APP_URL, PHP_URL_PATH);

    if ($app_url_path !== '/') {
        if (strpos($request_uri_path, $app_url_path) === 0) {
            $request_uri_path = substr($request_uri_path, strlen($app_url_path));
        }
    }

    return $request_uri_path === $path;
}

function formatPhoneNumber($number) {
    // Remove all non-digit characters
    $number = preg_replace('/[^0-9]/', '', $number);

    // If the number starts with 00880, remove the 00
    if (substr($number, 0, 5) === '00880') {
        $number = substr($number, 2);
    }

    // If the number starts with 880, it's already in the correct format
    if (substr($number, 0, 3) === '880') {
        //
    }
    // If the number starts with 01, replace with 8801
    elseif (substr($number, 0, 2) === '01') {
        $number = '88' . $number;
    }
    // If the number starts with 1, and is 10 digits long, prepend 880
    elseif (strlen($number) === 10 && $number[0] === '1') {
        $number = '880' . $number;
    }
    // Otherwise, prepend 88
    else {
        $number = '88' . $number;
    }

    // Final validation for the 8801xxxxxxxxx format
    if (preg_match('/^8801[0-9]{9}$/', $number)) {
        return $number;
    }

    return null;
}

function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        // Log the potential attack or error
        error_log("CSRF Token Mismatch: Session token [" . ($_SESSION['csrf_token'] ?? 'null') . "] vs Provided token [" . ($token ?? 'null') . "]");
        die("Invalid CSRF token.");
    }
    return true;
}

function csrf_field() {
    $token = generate_csrf_token();
    echo '<input type="hidden" name="csrf_token" value="' . $token . '">';
}
