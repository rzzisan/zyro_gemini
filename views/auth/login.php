<?php
require_once __DIR__ . '/../../core/config.php';
require_once __DIR__ . '/../../core/functions.php';
if (is_logged_in()) {
    redirect('/views/dashboard/index.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md">
        <div class="bg-white shadow-md rounded-lg px-8 py-6">
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Login</h2>
            <?php display_message(); ?>
            <form action="../../controllers/authController.php" method="POST">
                <input type="hidden" name="action" value="login">
                <?php csrf_field(); ?>
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                    <input type="email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="email" name="email" required>
                </div>
                <div class="mb-6">
                    <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                    <input type="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" id="password" name="password" required>
                </div>
                <div class="flex items-center justify-between mb-6">
                    <label for="remember_me" class="flex items-center">
                        <input type="checkbox" name="remember_me" id="remember_me" class="mr-2">
                        <span class="text-sm text-gray-700">Remember me</span>
                    </label>
                    <a href="forgot_password.php" class="text-sm text-blue-500 hover:text-blue-800">Forgot password?</a>
                </div>
                <div class="flex items-center justify-between">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Login
                    </button>
                    <p class="text-sm text-gray-600">
                        Don't have an account? <a href="register.php" class="text-blue-500 hover:text-blue-800">Register</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>