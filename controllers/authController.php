<?php
require_once __DIR__ . '/../core/config.php';
require_once ROOT_PATH . '/core/functions.php';
require_once ROOT_PATH . '/models/User.php';
require_once ROOT_PATH . '/models/Plan.php';
require_once ROOT_PATH . '/models/Subscription.php';
require_once ROOT_PATH . '/models/SmsCredit.php';
require_once ROOT_PATH . '/models/AuthToken.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    switch ($_POST['action']) {
        case 'admin_login':
            $email = $_POST['email'] ?? null;
            $password = $_POST['password'] ?? null;
            $remember_me = isset($_POST['remember_me']);

            if (!$email || !$password) {
                // Optional: Set a flash message
                $_SESSION['flash_message'] = 'Email and password are required.';
                redirect('/admin/login.php');
            }

            $db = getDb();
            $userModel = new User($db);
            $user = $userModel->findByEmail($email);

            if ($user && password_verify($password, $user['password']) && $user['role'] === 'admin') {
                // Regenerate session ID to prevent session fixation
                session_regenerate_id(true);

                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_name'] = $user['name'];
                $_SESSION['is_admin'] = true;

                if ($remember_me) {
                    $token = bin2hex(random_bytes(32));
                    $expires_at = date('Y-m-d H:i:s', strtotime('+30 days'));
                    
                    $authTokenModel = new AuthToken($db);
                    $authTokenModel->create($user['id'], $token, $expires_at);

                    setcookie('remember_me', $token, [
                        'expires' => strtotime('+30 days'),
                        'path' => '/',
                        'domain' => '',
                        'secure' => isset($_SERVER['HTTPS']),
                        'httponly' => true,
                        'samesite' => 'Lax'
                    ]);
                }

                redirect('/admin/index.php');
            } else {
                // Optional: Set a flash message
                $_SESSION['flash_message'] = 'Invalid credentials or not an admin.';
                redirect('/admin/login.php');
            }
            break;
        
        case 'register':
            $name = $_POST['name'] ?? null;
            $email = $_POST['email'] ?? null;
            $password = $_POST['password'] ?? null;
            $password_confirm = $_POST['password_confirm'] ?? null;

            if (!$name || !$email || !$password || !$password_confirm) {
                set_message('All fields are required.', 'danger');
                redirect('/views/auth/register.php');
            }

            if ($password !== $password_confirm) {
                set_message('Passwords do not match.', 'danger');
                redirect('/views/auth/register.php');
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                set_message('Invalid email format.', 'danger');
                redirect('/views/auth/register.php');
            }

            $db = getDb();
            $userModel = new User($db);

            $existing = $userModel->findByEmail($email);
            if ($existing) {
                set_message('Email already in use.', 'danger');
                redirect('/views/auth/register.php');
            }

            $user_id = $userModel->createUser($name, $email, $password);

            $planModel = new Plan($db);
            $freePlan = $planModel->findByName('Free');

            if ($freePlan) {
                (new Subscription($db))->create($user_id, $freePlan['id']);
                (new SmsCredit($db))->create($user_id, $freePlan['sms_credit_bonus']);
            }

            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_name'] = $name;

            redirect('/views/dashboard/index.php');
            break;

        case 'login':
            $email = $_POST['email'] ?? null;
            $password = $_POST['password'] ?? null;
            $remember_me = isset($_POST['remember_me']);

            if (!$email || !$password) {
                set_message('Email and password are required.', 'danger');
                redirect('/views/auth/login.php');
            }

            $db = getDb();
            $userModel = new User($db);
            $user = $userModel->findByEmail($email);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];

                if ($remember_me) {
                    $token = bin2hex(random_bytes(32));
                    $expires_at = date('Y-m-d H:i:s', strtotime('+30 days'));
                    
                    $authTokenModel = new AuthToken($db);
                    $authTokenModel->create($user['id'], $token, $expires_at);

                    setcookie('remember_me', $token, [
                        'expires' => strtotime('+30 days'),
                        'path' => '/',
                        'domain' => '',
                        'secure' => isset($_SERVER['HTTPS']),
                        'httponly' => true,
                        'samesite' => 'Lax'
                    ]);
                }

                redirect('/views/dashboard/index.php');
            } else {
                set_message('Invalid credentials.', 'danger');
                redirect('/views/auth/login.php');
            }
            break;
        
        default:
            // Optional: Handle unknown action
            redirect('/index.php');
            break;
    }
} else {
    // Redirect if accessed directly without POST method
    redirect('/index.php');
}
