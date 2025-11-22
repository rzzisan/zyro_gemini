<?php
require_once __DIR__ . '/../core/config.php';
require_once ROOT_PATH . '/core/functions.php';
require_once ROOT_PATH . '/models/User.php';
require_once ROOT_PATH . '/models/Plan.php';
require_once ROOT_PATH . '/models/Subscription.php';
require_once ROOT_PATH . '/models/SmsCredit.php';
require_once ROOT_PATH . '/models/AuthToken.php';
require_once ROOT_PATH . '/controllers/smsController.php';

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
            $phone_number = $_POST['phone_number'] ?? null;
            $district = $_POST['district'] ?? null;
            $upazila = $_POST['upazila'] ?? null;
            $password = $_POST['password'] ?? null;
            $password_confirm = $_POST['password_confirm'] ?? null;

            if (!$name || !$email || !$phone_number || !$password || !$password_confirm) {
                set_message('All required fields must be filled.', 'danger');
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

            $formatted_phone = formatPhoneNumber($phone_number);
            if (!$formatted_phone) {
                set_message('Invalid phone number format. Please use a valid Bangladeshi number.', 'danger');
                redirect('/views/auth/register.php');
            }

            // Generate a random 4-digit OTP
            $otp = rand(1000, 9999);

            // Store user data and OTP temporarily in session until OTP verification
            $tempUserData = [
                'name' => $name,
                'email' => $email,
                'phone_number' => $formatted_phone,
                'district' => $district,
                'upazila' => $upazila,
                'password' => password_hash($password, PASSWORD_DEFAULT), // Hash password before storing
                'otp' => $otp,
                'otp_timestamp' => time() // Store timestamp to check for OTP expiry
            ];
            $_SESSION['temp_registration'] = $tempUserData;

            // Send OTP to the user's phone
            SmsController::sendSms($formatted_phone, "Your OTP is: $otp");

            set_message('OTP sent to your phone.', 'success');
            redirect('/views/auth/verify_otp.php');
            break;

        case 'verify_otp':
            $user_otp = $_POST['otp'] ?? null;

            if (!isset($_SESSION['temp_registration'])) {
                set_message('Session expired. Please register again.', 'danger');
                redirect('/views/auth/register.php');
            }

            $temp_data = $_SESSION['temp_registration'];
            $stored_otp = $temp_data['otp'];
            $otp_timestamp = $temp_data['otp_timestamp'];

            // OTP expiry time (e.g., 5 minutes)
            $otp_expiry_time = 5 * 60; 

            if ($user_otp != $stored_otp) {
                set_message('Invalid OTP. Please try again.', 'danger');
                redirect('/views/auth/verify_otp.php');
            }

            if ((time() - $otp_timestamp) > $otp_expiry_time) {
                set_message('OTP has expired. Please register again.', 'danger');
                unset($_SESSION['temp_registration']); // Clear expired data
                redirect('/views/auth/register.php');
            }

            // OTP is valid and not expired, proceed with registration
            $db = getDb();
            $userModel = new User($db);
            $planModel = new Plan($db);

            $user_id = $userModel->createUser(
                $temp_data['name'],
                $temp_data['email'],
                $temp_data['password'], // Already hashed
                $temp_data['phone_number'],
                $temp_data['district'],
                $temp_data['upazila']
            );

            $freePlan = $planModel->findByName('Free');

            if ($freePlan) {
                (new Subscription($db))->create($user_id, $freePlan['id']);
                (new SmsCredit($db))->create($user_id, $freePlan['sms_credit_bonus']);
            }

            // Log in the user
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_name'] = $temp_data['name'];

            // Clear temporary registration data
            unset($_SESSION['temp_registration']);

            set_message('Registration successful! Welcome.', 'success');
            redirect('/views/dashboard/index.php');
            break;

        case 'resend_otp':
            if (!isset($_SESSION['temp_registration'])) {
                set_message('Session expired. Please register again.', 'danger');
                redirect('/views/auth/register.php');
            }

            $new_otp = rand(1000, 9999);
            $_SESSION['temp_registration']['otp'] = $new_otp;
            $_SESSION['temp_registration']['otp_timestamp'] = time(); // Reset timestamp

            $phone = $_SESSION['temp_registration']['phone_number'];

            SmsController::sendSms($phone, "Your new OTP is: $new_otp");

            set_message('New OTP sent.', 'success');
            redirect('/views/auth/verify_otp.php');
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
