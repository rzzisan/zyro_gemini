<?php
require_once __DIR__ . '/../../core/config.php';
require_once ROOT_PATH . '/core/functions.php';

if (!isset($_SESSION['otp_verified']) || !$_SESSION['otp_verified'] || !isset($_SESSION['reset_user_id'])) {
    set_message('Please verify OTP first.', 'danger');
    redirect('/views/auth/forgot_password.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md">
        <div class="bg-white shadow-md rounded-lg px-8 py-6">
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Set New Password</h2>
            <?php display_message(); ?>
            <form action="../../controllers/authController.php" method="POST">
                <input type="hidden" name="action" value="update_password">
                <?php csrf_field(); ?>
                <div class="mb-4">
                    <label for="new_password" class="block text-gray-700 text-sm font-bold mb-2">New Password</label>
                    <input type="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="new_password" name="new_password" required>
                </div>
                <div class="mb-6">
                    <label for="confirm_password" class="block text-gray-700 text-sm font-bold mb-2">Confirm Password</label>
                    <input type="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" id="confirm_password" name="confirm_password" required>
                </div>
                <div class="flex items-center justify-between">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full">
                        Reset Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>