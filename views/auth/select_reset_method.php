<?php
require_once __DIR__ . '/../../core/config.php';
require_once ROOT_PATH . '/core/functions.php';
require_once ROOT_PATH . '/models/User.php';

// Check if reset_user_id is set in session
if (!isset($_SESSION['reset_user_id'])) {
    set_message('Please enter your email or phone number first.', 'danger');
    redirect('/views/auth/forgot_password.php');
}

$db = getDb();
$userModel = new User($db);
$user = $userModel->find($_SESSION['reset_user_id']);

if (!$user) {
    set_message('User not found. Please try again.', 'danger');
    unset($_SESSION['reset_user_id']); // Clear invalid session data
    redirect('/views/auth/forgot_password.php');
}

// Masking functions
function maskEmail($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return $email; // Return original if not a valid email
    }
    $parts = explode('@', $email);
    $name = $parts[0];
    $domain = $parts[1];

    // Ensure at least first and last char of name part are shown
    if (strlen($name) > 4) {
        $maskedName = substr($name, 0, 2) . str_repeat('*', strlen($name) - 4) . substr($name, -2);
    } else if (strlen($name) > 2) {
        $maskedName = substr($name, 0, 1) . str_repeat('*', strlen($name) - 2) . substr($name, -1);
    } else {
        $maskedName = str_repeat('*', strlen($name)); // Mask all if too short
    }
    return $maskedName . '@' . $domain;
}

function maskPhoneNumber($phoneNumber) {
    // Clean phone number (remove non-digits)
    $cleanedPhone = preg_replace('/[^0-9]/', '', $phoneNumber);
    if (strlen($cleanedPhone) < 11) { // Basic check for typical length
        return $phoneNumber; // Return original if not long enough
    }
    // For 01XXXXXXXXX, mask as 01X******XX
    $maskedPhone = substr($cleanedPhone, 0, 3) . str_repeat('*', 6) . substr($cleanedPhone, -2);
    return $maskedPhone;
}

$maskedEmail = $user['email'] ? maskEmail($user['email']) : null;
$maskedPhone = $user['phone_number'] ? maskPhoneNumber($user['phone_number']) : null;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Reset Method</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md">
        <div class="bg-white shadow-md rounded-lg px-8 py-6">
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Select Reset Method</h2>
            <?php display_message(); ?>
            <form action="../../controllers/authController.php" method="POST">
                <input type="hidden" name="action" value="send_reset_otp">
                <div class="mb-4">
                    <p class="block text-gray-700 text-sm font-bold mb-2">How would you like to receive the reset code?</p>
                    <?php if ($user['email']): ?>
                        <div class="mb-2">
                            <input type="radio" id="method_email" name="reset_method" value="email" class="mr-2" checked>
                            <label for="method_email" class="text-gray-700">Email: <?php echo htmlspecialchars($maskedEmail); ?></label>
                        </div>
                    <?php endif; ?>
                    <?php if ($user['phone_number']): ?>
                        <div class="mb-2">
                            <input type="radio" id="method_sms" name="reset_method" value="sms" class="mr-2" <?php echo !$user['email'] ? 'checked' : ''; ?>>
                            <label for="method_sms" class="text-gray-700">SMS: <?php echo htmlspecialchars($maskedPhone); ?></label>
                        </div>
                    <?php endif; ?>
                    <?php if (!$user['email'] && !$user['phone_number']): ?>
                        <p class="text-red-500">No contact methods available for this user.</p>
                        <a href="forgot_password.php" class="text-blue-500 hover:text-blue-800">Try again</a>
                    <?php endif; ?>
                </div>
                <?php if ($user['email'] || $user['phone_number']): ?>
                    <div class="flex items-center justify-between">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Send Code
                        </button>
                        <a href="forgot_password.php" class="text-sm text-blue-500 hover:text-blue-800">Cancel</a>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
</body>
</html>