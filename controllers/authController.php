<?php
require_once __DIR__ . '/../core/config.php';
require_once ROOT_PATH . '/core/functions.php';
require_once ROOT_PATH . '/models/User.php';
require_once ROOT_PATH . '/models/Plan.php';
require_once ROOT_PATH . '/models/Subscription.php';
require_once ROOT_PATH . '/models/SmsCredit.php';
require_once ROOT_PATH . '/models/AuthToken.php';
require_once ROOT_PATH . '/models/PasswordReset.php';
require_once ROOT_PATH . '/controllers/smsController.php';
require_once ROOT_PATH . '/controllers/EmailController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    verify_csrf_token($_POST['csrf_token'] ?? '');

    switch ($_POST['action']) {
        case 'admin_login':
            $email = sanitize_input($_POST['email'] ?? null);
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
            $name = sanitize_input($_POST['name'] ?? null);
            $email = sanitize_input($_POST['email'] ?? null);
            $phone_number = sanitize_input($_POST['phone_number'] ?? null);
            $district = sanitize_input($_POST['district'] ?? null);
            $upazila = sanitize_input($_POST['upazila'] ?? null);
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

            $existing_phone = $userModel->findByPhoneNumber($formatted_phone);
            if ($existing_phone) {
                set_message('Phone number already registered.', 'danger');
                redirect('/views/auth/register.php');
            }

            // Generate a random 4-digit OTP
            $otp = random_int(1000, 9999);

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
            SmsController::sendSystemSms($formatted_phone, "Your OTP is: $otp");

            set_message('OTP sent to your phone.', 'success');
            redirect('/views/auth/verify_otp.php');
            break;

        case 'verify_otp':
            $user_otp = sanitize_input($_POST['otp'] ?? null);

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

            $new_otp = random_int(1000, 9999);
            $_SESSION['temp_registration']['otp'] = $new_otp;
            $_SESSION['temp_registration']['otp_timestamp'] = time(); // Reset timestamp

            $phone = $_SESSION['temp_registration']['phone_number'];

            SmsController::sendSystemSms($phone, "Your new OTP is: $new_otp");

            set_message('New OTP sent.', 'success');
            redirect('/views/auth/verify_otp.php');
            break;

        case 'login':
            $email = sanitize_input($_POST['email'] ?? null);
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

        case 'resend_verification':
            ensure_logged_in();
            $user_id = get_user_id();
            $db = getDb();
            $userModel = new User($db);
            $user = $userModel->find($user_id);

            if ($user['email_verified_at']) {
                set_message('Email already verified.', 'info');
            } else {
                $token = bin2hex(random_bytes(32));
                $userModel->setEmailVerificationToken($user_id, $token);
                
                if (EmailController::sendVerificationEmail($user['email'], $token)) {
                    set_message('Verification link sent to your email!', 'success');
                } else {
                    set_message('Failed to send email. Please try again.', 'danger');
                }
            }
            redirect('/views/dashboard/index.php');
            break;

        case 'find_user_for_reset':
            $identity = sanitize_input($_POST['identity'] ?? null);

            if (empty($identity)) {
                set_message('Email or Phone Number is required.', 'danger');
                redirect('/views/auth/forgot_password.php');
            }

            $db = getDb();
            $userModel = new User($db);
            $user = $userModel->findByIdentity($identity);

            if ($user) {
                $_SESSION['reset_user_id'] = $user['id'];
                redirect('/views/auth/select_reset_method.php');
            } else {
                set_message('User not found with the provided Email or Phone Number.', 'danger');
                redirect('/views/auth/forgot_password.php');
            }
            break;

        case 'send_reset_otp':
            if (!isset($_SESSION['reset_user_id'])) {
                set_message('Session expired. Please try again.', 'danger');
                redirect('/views/auth/forgot_password.php');
            }

            $reset_method = sanitize_input($_POST['reset_method'] ?? null);
            if (!$reset_method || !in_array($reset_method, ['email', 'sms'])) {
                set_message('Invalid reset method selected.', 'danger');
                redirect('/views/auth/select_reset_method.php');
            }

            $userId = $_SESSION['reset_user_id'];
            $db = getDb();
            $userModel = new User($db);
            $user = $userModel->find($userId);

            if (!$user) {
                set_message('User not found.', 'danger');
                unset($_SESSION['reset_user_id']);
                redirect('/views/auth/forgot_password.php');
            }

            $otp = random_int(100000, 999999); // 6-digit OTP
            $passwordResetModel = new PasswordReset($db);
            
            // Delete any existing OTPs for this user before creating a new one
            $passwordResetModel->deleteUserOtps($userId);
            $passwordResetModel->create($userId, $otp);

            $sent = false;
            if ($reset_method === 'email') {
                if ($user['email']) {
                    $subject = "Password Reset OTP";
                    $body = "<div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f4f4f4;'>
                                <div style='max-width: 600px; margin: 0 auto; background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>
                                    <h2 style='color: #333;'>Password Reset Request</h2>
                                    <p style='color: #555;'>Your One-Time Password (OTP) for password reset is:</p>
                                    <h1 style='color: #3b82f6; letter-spacing: 5px;'>$otp</h1>
                                    <p style='color: #999; font-size: 12px; margin-top: 20px;'>This OTP is valid for 15 minutes.</p>
                                </div>
                            </div>";
                    $sent = EmailController::sendEmail($user['email'], $subject, $body);
                } else {
                    set_message('User does not have an email address.', 'danger');
                    redirect('/views/auth/select_reset_method.php');
                }
            } elseif ($reset_method === 'sms') {
                if ($user['phone_number']) {
                    $formattedPhone = formatPhoneNumber($user['phone_number']);
                    $message = "Your Password Reset OTP is: $otp. Valid for 15 minutes.";
                    $sent = SmsController::sendSystemSms($formattedPhone, $message);
                } else {
                    set_message('User does not have a phone number.', 'danger');
                    redirect('/views/auth/select_reset_method.php');
                }
            }

            if ($sent) {
                set_message('OTP sent successfully. Please check your ' . ($reset_method === 'email' ? 'email' : 'phone') . '.', 'success');
                redirect('/views/auth/verify_reset_otp.php');
            } else {
                set_message('Failed to send OTP. Please try again later.', 'danger');
                redirect('/views/auth/select_reset_method.php');
            }
            break;

        case 'verify_otp_reset':
            if (!isset($_SESSION['reset_user_id'])) {
                set_message('Session expired. Please start over.', 'danger');
                redirect('/views/auth/forgot_password.php');
            }

            $otp = sanitize_input($_POST['otp'] ?? null);
            if (!$otp) {
                set_message('Please enter the OTP.', 'danger');
                redirect('/views/auth/verify_reset_otp.php');
            }

            $userId = $_SESSION['reset_user_id'];
            $db = getDb();
            $passwordResetModel = new PasswordReset($db);
            $record = $passwordResetModel->verify($userId, $otp);

            if ($record) {
                $_SESSION['otp_verified'] = true;
                redirect('/views/auth/new_password.php');
            } else {
                set_message('Invalid or expired OTP.', 'danger');
                redirect('/views/auth/verify_reset_otp.php');
            }
            break;

        case 'update_password':
            if (!isset($_SESSION['otp_verified']) || !$_SESSION['otp_verified'] || !isset($_SESSION['reset_user_id'])) {
                set_message('Unauthorized access.', 'danger');
                redirect('/views/auth/login.php');
            }

            $new_password = $_POST['new_password'] ?? null;
            $confirm_password = $_POST['confirm_password'] ?? null;

            if (!$new_password || !$confirm_password) {
                set_message('Both password fields are required.', 'danger');
                redirect('/views/auth/new_password.php');
            }

            if ($new_password !== $confirm_password) {
                set_message('Passwords do not match.', 'danger');
                redirect('/views/auth/new_password.php');
            }

            if (strlen($new_password) < 6) {
                set_message('Password must be at least 6 characters long.', 'danger');
                redirect('/views/auth/new_password.php');
            }

            $userId = $_SESSION['reset_user_id'];
            $hashedPassword = password_hash($new_password, PASSWORD_BCRYPT);

            $db = getDb();
            $userModel = new User($db);
            $passwordResetModel = new PasswordReset($db);

            if ($userModel->updatePassword($userId, $hashedPassword)) {
                $passwordResetModel->deleteUserOtps($userId);
                unset($_SESSION['reset_user_id']);
                unset($_SESSION['otp_verified']);
                set_message('Password reset successfully. Please login with your new password.', 'success');
                redirect('/views/auth/login.php');
            } else {
                set_message('Failed to reset password. Please try again.', 'danger');
                redirect('/views/auth/new_password.php');
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
