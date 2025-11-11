<?php
require_once __DIR__ . '/../../core/config.php';
require_once ROOT_PATH . '/core/functions.php';
ensureAdmin(); // Protects all admin pages that include this header
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-gray-800 p-4 text-white shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <div class="text-lg font-bold">Admin Panel</div>
            <ul class="flex space-x-4">
                <li><a href="<?php echo APP_URL; ?>/admin/index.php" class="hover:text-gray-300">Dashboard</a></li>
                <li><a href="<?php echo APP_URL; ?>/admin/users.php" class="hover:text-gray-300">Users</a></li>
                <li><a href="<?php echo APP_URL; ?>/admin/plans.php" class="hover:text-gray-300">Plans</a></li>
                <li class="relative" x-data="{ open: false }">
                    <a @click="open = !open" class="cursor-pointer hover:text-gray-300">SMS</a>
                    <ul x-show="open" @click.away="open = false" class="absolute bg-gray-800 text-white py-2 mt-2 rounded-md shadow-lg">
                        <li><a href="<?php echo APP_URL; ?>/admin/sms_credit.php" class="block px-4 py-2 hover:bg-gray-700">SMS Credit</a></li>
                    </ul>
                </li>
                <li><a href="<?php echo APP_URL; ?>/admin/logout.php" class="hover:text-gray-300">Logout</a></li>
            </ul>
            <div class="text-sm">
                <?php echo "Welcome, " . htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?>
            </div>
        </div>
    </nav>
    <div class="container mx-auto mt-4 p-4">