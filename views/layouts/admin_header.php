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
<body class="bg-gray-100" x-data="{ 'isMobileMenuOpen': false }">
    <nav class="bg-gray-800 p-4 text-white shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <div class="text-lg font-bold">Admin Panel</div>
            <div class="hidden md:flex space-x-4">
                <a href="<?php echo APP_URL; ?>/admin/index.php" class="hover:text-gray-300">Dashboard</a>
                <a href="<?php echo APP_URL; ?>/admin/users.php" class="hover:text-gray-300">Users</a>
                <a href="<?php echo APP_URL; ?>/admin/plans.php" class="hover:text-gray-300">Plans</a>
                <a href="<?php echo APP_URL; ?>/admin/fraud_checker.php" class="hover:text-gray-300">Fraud Checker</a>
                <div class="relative" x-data="{ open: false }">
                    <a @click="open = !open" class="cursor-pointer hover:text-gray-300">SMS</a>
                    <div x-show="open" @click.away="open = false" class="absolute bg-gray-800 text-white py-2 mt-2 rounded-md shadow-lg">
                        <a href="<?php echo APP_URL; ?>/admin/sms_credit.php" class="block px-4 py-2 hover:bg-gray-700">SMS Credit</a>
                        <a href="<?php echo APP_URL; ?>/admin/sms_history.php" class="block px-4 py-2 hover:bg-gray-700">SMS History</a>
                    </div>
                </div>
                <a href="<?php echo APP_URL; ?>/admin/logout.php" class="hover:text-gray-300">Logout</a>
            </div>
            <div class="hidden md:block text-sm">
                <?php echo "Welcome, " . htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?>
            </div>
            <div class="md:hidden">
                <button @click="isMobileMenuOpen = !isMobileMenuOpen" class="text-white focus:outline-none">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                    </svg>
                </button>
            </div>
        </div>
        <div x-show="isMobileMenuOpen" class="md:hidden mt-4">
            <a href="<?php echo APP_URL; ?>/admin/index.php" class="block py-2 px-4 text-sm hover:bg-gray-700">Dashboard</a>
            <a href="<?php echo APP_URL; ?>/admin/users.php" class="block py-2 px-4 text-sm hover:bg-gray-700">Users</a>
            <a href="<?php echo APP_URL; ?>/admin/plans.php" class="block py-2 px-4 text-sm hover:bg-gray-700">Plans</a>
            <a href="<?php echo APP_URL; ?>/admin/fraud_checker.php" class="block py-2 px-4 text-sm hover:bg-gray-700">Fraud Checker</a>
            <a href="<?php echo APP_URL; ?>/admin/sms_credit.php" class="block py-2 px-4 text-sm hover:bg-gray-700">SMS Credit</a>
            <a href="<?php echo APP_URL; ?>/admin/sms_history.php" class="block py-2 px-4 text-sm hover:bg-gray-700">SMS History</a>
            <a href="<?php echo APP_URL; ?>/admin/logout.php" class="block py-2 px-4 text-sm hover:bg-gray-700">Logout</a>
        </div>
    </nav>
    <div class="container mx-auto mt-4 p-4">