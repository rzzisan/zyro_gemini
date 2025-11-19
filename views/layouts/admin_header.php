<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../core/config.php';
require_once ROOT_PATH . '/core/functions.php';
ensureAdmin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link href="<?php echo APP_URL; ?>/public/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body class="bg-gray-100">

<div x-data="{ sidebarOpen: window.innerWidth > 1024 }" class="flex h-screen bg-gray-100" @resize.window="sidebarOpen = window.innerWidth > 1024">

    <!-- Hamburger Menu Button -->
    <button @click.stop="sidebarOpen = !sidebarOpen" class="fixed top-4 left-4 z-40 text-gray-500 focus:outline-none lg:hidden">
        <i class="fas fa-bars"></i>
    </button>

    <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-20 bg-black opacity-50 transition-opacity lg:hidden" x-cloak></div>

    <aside
        class="fixed inset-y-0 left-0 z-30 bg-gray-800 text-gray-100 transition-all duration-300 transform 
               lg:static lg:inset-0 lg:translate-x-0"
        :class="{
            'w-64': sidebarOpen, 
            'w-20': !sidebarOpen, 
            'translate-x-0': sidebarOpen, 
            '-translate-x-full': !sidebarOpen
        }"
        x-cloak
    >
        <div class="flex items-center justify-between h-16 px-4 py-2">
            <a href="<?php echo APP_URL; ?>/admin/index.php" class="text-2xl font-bold text-white" x-show="sidebarOpen">Admin Panel</a>
            <button @click.stop="sidebarOpen = !sidebarOpen" class="text-gray-300 focus:outline-none hidden lg:block" :class="{'lg:hidden': sidebarOpen}">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        <nav class="mt-8">
            <a href="<?php echo APP_URL; ?>/admin/index.php"
               title="Dashboard"
               class="flex items-center px-4 py-3 mt-2 text-sm font-medium rounded-md transition-colors duration-200
               <?php echo is_active('/admin/index.php') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?>"
               :class="{ 'justify-start': sidebarOpen, 'justify-center': !sidebarOpen }">
                <i class="fas fa-tachometer-alt w-6 text-center" :class="{ 'mr-3': sidebarOpen }"></i>
                <span x-show="sidebarOpen">Dashboard</span>
            </a>
            <a href="<?php echo APP_URL; ?>/admin/users.php"
               title="Users"
               class="flex items-center px-4 py-3 mt-2 text-sm font-medium rounded-md transition-colors duration-200
               <?php echo is_active('/admin/users.php') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?>"
               :class="{ 'justify-start': sidebarOpen, 'justify-center': !sidebarOpen }">
                <i class="fas fa-users w-6 text-center" :class="{ 'mr-3': sidebarOpen }"></i>
                <span x-show="sidebarOpen">Users</span>
            </a>
            <a href="<?php echo APP_URL; ?>/admin/plans.php"
               title="Plans"
               class="flex items-center px-4 py-3 mt-2 text-sm font-medium rounded-md transition-colors duration-200
               <?php echo is_active('/admin/plans.php') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?>"
               :class="{ 'justify-start': sidebarOpen, 'justify-center': !sidebarOpen }">
                <i class="fas fa-clipboard-list w-6 text-center" :class="{ 'mr-3': sidebarOpen }"></i>
                <span x-show="sidebarOpen">Plans</span>
            </a>
            <a href="<?php echo APP_URL; ?>/admin/fraud_checker.php"
               title="Fraud Checker"
               class="flex items-center px-4 py-3 mt-2 text-sm font-medium rounded-md transition-colors duration-200
               <?php echo is_active('/admin/fraud_checker.php') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?>"
               :class="{ 'justify-start': sidebarOpen, 'justify-center': !sidebarOpen }">
                <i class="fas fa-shield-alt w-6 text-center" :class="{ 'mr-3': sidebarOpen }"></i>
                <span x-show="sidebarOpen">Fraud Checker</span>
            </a>
            
            <div x-data="{ open: false }" class="mt-2">
                <button @click="open = !open" 
                        title="SMS"
                        class="w-full flex items-center px-4 py-3 text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white rounded-md focus:outline-none"
                        :class="{ 'justify-between': sidebarOpen, 'justify-center': !sidebarOpen }">
                    <span class="flex items-center">
                        <i class="fas fa-sms w-6 text-center" :class="{ 'mr-3': sidebarOpen }"></i>
                        <span x-show="sidebarOpen">SMS</span>
                    </span>
                    <i class="fas" :class="{ 'fa-chevron-down': !open, 'fa-chevron-up': open, 'hidden': !sidebarOpen }"></i>
                </button>
                <div x-show="open && sidebarOpen" class="pl-8 py-2">
                    <a href="<?php echo APP_URL; ?>/admin/sms_credit.php"
                       class="<?php echo is_active('/admin/sms_credit.php') ? 'text-white' : 'text-gray-400 hover:text-white'; ?> 
                       block py-1 text-sm rounded-md transition-colors duration-200">SMS Credit</a>
                    <a href="<?php echo APP_URL; ?>/admin/sms_history.php"
                       class="<?php echo is_active('/admin/sms_history.php') ? 'text-white' : 'text-gray-400 hover:text-white'; ?> 
                       block py-1 mt-1 text-sm rounded-md transition-colors duration-200">SMS History</a>
                </div>
            </div>

        </nav>
    </aside>

    <div class="flex-1 flex flex-col overflow-hidden transition-all duration-300"
         :class="{ 'lg:ml-64': sidebarOpen, 'lg:ml-20': !sidebarOpen }">
        
        <header class="flex items-center justify-between p-4 bg-white border-b">
            <button @click.stop="sidebarOpen = !sidebarOpen" class="text-gray-500 focus:outline-none hidden lg:block">
                <i class="fas fa-bars"></i>
            </button>
            
            <div class="flex items-center">
                <span class="text-gray-500 text-sm hidden sm:block">
                    <?php echo "Welcome, " . htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?>
                </span>
                <a href="<?php echo APP_URL; ?>/admin/logout.php" class="ml-4 text-gray-500 hover:text-gray-700 text-sm">
                    <i class="fas fa-sign-out-alt mr-1"></i>Logout
                </a>
            </div>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-4 sm:p-6 lg:p-8">
            