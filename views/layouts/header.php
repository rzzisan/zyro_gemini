<?php
require_once __DIR__ . '/../../core/config.php';
require_once __DIR__ . '/../../core/functions.php';
ensure_logged_in();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/public/css/app.css">
</head>
<body class="bg-gray-100" x-data="{ 'isMobileMenuOpen': false }">
    <nav class="bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="#" class="text-2xl font-bold text-gray-800">My SaaS</a>
                    </div>
                    <div class="hidden sm:-my-px sm:ml-6 sm:flex sm:space-x-8">
                        <a href="<?php echo APP_URL; ?>/views/dashboard/index.php" class="<?php echo is_active('/views/dashboard/index.php') ? 'text-gray-900 border-indigo-500' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">Dashboard</a>
                        <a href="<?php echo APP_URL; ?>/views/dashboard/websites.php" class="<?php echo is_active('/views/dashboard/websites.php') ? 'text-gray-900 border-indigo-500' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">My Websites</a>
                        <a href="<?php echo APP_URL; ?>/views/dashboard/send_sms.php" class="<?php echo is_active('/views/dashboard/send_sms.php') ? 'text-gray-900 border-indigo-500' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">Send SMS</a>
                    </div>
                </div>
                <div class="hidden sm:ml-6 sm:flex sm:items-center">
                    <span class="text-gray-500">
                        Hello, <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                    </span>
                    <a href="<?php echo APP_URL; ?>/logout.php" class="ml-4 text-gray-500 hover:text-gray-700">Logout</a>
                </div>
                <div class="-mr-2 flex items-center sm:hidden">
                    <button @click="isMobileMenuOpen = !isMobileMenuOpen" type="button" class="bg-white inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" aria-controls="mobile-menu" aria-expanded="false">
                        <span class="sr-only">Open main menu</span>
                        <svg :class="{'hidden': isMobileMenuOpen, 'block': !isMobileMenuOpen }" class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg :class="{'hidden': !isMobileMenuOpen, 'block': isMobileMenuOpen }" class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        <div class="sm:hidden" id="mobile-menu" x-show="isMobileMenuOpen">
            <div class="pt-2 pb-3 space-y-1">
                <a href="<?php echo APP_URL; ?>/views/dashboard/index.php" class="<?php echo is_active('/views/dashboard/index.php') ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700'; ?> block pl-3 pr-4 py-2 border-l-4 text-base font-medium">Dashboard</a>
                <a href="<?php echo APP_URL; ?>/views/dashboard/websites.php" class="<?php echo is_active('/views/dashboard/websites.php') ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700'; ?> block pl-3 pr-4 py-2 border-l-4 text-base font-medium">My Websites</a>
                <a href="<?php echo APP_URL; ?>/views/dashboard/send_sms.php" class="<?php echo is_active('/views/dashboard/send_sms.php') ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700'; ?> block pl-3 pr-4 py-2 border-l-4 text-base font-medium">Send SMS</a>
            </div>
            <div class="pt-4 pb-3 border-t border-gray-200">
                <div class="flex items-center px-4">
                    <div class="ml-3">
                        <div class="text-base font-medium text-gray-800"><?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
                    </div>
                </div>
                <div class="mt-3 space-y-1">
                    <a href="<?php echo APP_URL; ?>/logout.php" class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100">Logout</a>
                </div>
            </div>
        </div>
    </nav>
    <div class="container mx-auto mt-4 px-4 sm:px-6 lg:px-8">