<?php
require_once __DIR__ . '/../../core/config.php';
require_once ROOT_PATH . '/core/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md">
        <div class="bg-white shadow-md rounded-lg px-8 py-6">
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Forgot Password</h2>
            <?php display_message(); ?>
            <form action="../../controllers/authController.php" method="POST">
                <input type="hidden" name="action" value="find_user_for_reset">
                <div class="mb-4">
                    <label for="identity" class="block text-gray-700 text-sm font-bold mb-2">Email or Phone Number</label>
                    <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="identity" name="identity" required>
                </div>
                <div class="flex items-center justify-between">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Find Account
                    </button>
                    <a href="login.php" class="text-sm text-blue-500 hover:text-blue-800">Back to Login</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>