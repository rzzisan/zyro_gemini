<?php
require_once __DIR__ . '/../../core/config.php';
require_once ROOT_PATH . '/core/functions.php';

if (!isset($_SESSION['reset_user_id'])) {
    set_message('Session expired. Please start over.', 'danger');
    redirect('/views/auth/forgot_password.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md">
        <div class="bg-white shadow-md rounded-lg px-8 py-6">
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Verify OTP</h2>
            <?php display_message(); ?>
            <form action="../../controllers/authController.php" method="POST">
                <input type="hidden" name="action" value="verify_otp_reset">
                <div class="mb-6">
                    <label for="otp" class="block text-gray-700 text-sm font-bold mb-2">Enter 6-digit OTP</label>
                    <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline text-center text-2xl tracking-widest" id="otp" name="otp" maxlength="6" required placeholder="123456">
                </div>
                <div class="flex items-center justify-between">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full">
                        Verify OTP
                    </button>
                </div>
                <div class="mt-4 text-center">
                    <a href="select_reset_method.php" class="text-sm text-blue-500 hover:text-blue-800">Resend OTP</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>