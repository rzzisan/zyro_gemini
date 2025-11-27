<?php
require_once __DIR__ . '/../core/config.php';
require_once ROOT_PATH . '/core/functions.php';

if (isAdmin()) {
    header('Location: ' . APP_URL . '/admin/index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md">
        <div class="bg-white shadow-md rounded-lg px-8 py-6">
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Admin Login</h2>
            <form action="<?php echo APP_URL; ?>/controllers/authController.php" method="POST">
                <?php csrf_field(); ?>
                <div class="mb-4">
                    <label for="email" class="sr-only">Email</label>
                    <input type="email" name="email" id="email" placeholder="Email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="mb-6">
                    <label for="password" class="sr-only">Password</label>
                    <input type="password" name="password" id="password" placeholder="Password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="flex items-center justify-between mb-6">
                    <label for="remember_me" class="flex items-center">
                        <input type="checkbox" name="remember_me" id="remember_me" class="mr-2">
                        <span class="text-sm text-gray-700">Remember me</span>
                    </label>
                </div>
                <input type="hidden" name="action" value="admin_login">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full">
                    Login
                </button>
            </form>
        </div>
    </div>
</body>
</html>