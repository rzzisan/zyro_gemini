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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body class="bg-gray-100">

<div x-data="{ sidebarOpen: window.innerWidth > 1024 }" class="flex h-screen bg-gray-100">
    
    <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-20 bg-black opacity-50 transition-opacity lg:hidden" x-cloak></div>

    <aside
        class="fixed inset-y-0 left-0 z-30 bg-white border-r transition-all duration-300 transform 
               lg:static lg:inset-0 lg:translate-x-0"
        :class="{
            'w-64': sidebarOpen, 
            'w-20': !sidebarOpen, 
            'translate-x-0': sidebarOpen, 
            '-translate-x-full': !sidebarOpen
        }"
        x-cloak
    >
        <div class="flex items-center justify-center h-16 px-4 py-2">
            <a href="<?php echo APP_URL; ?>/views/dashboard/index.php" class="text-2xl font-bold text-gray-800" x-show="sidebarOpen">My SaaS</a>
        </div>

        <nav class="mt-8">
            <a href="<?php echo APP_URL; ?>/views/dashboard/index.php"
               title="Dashboard"
               class="flex items-center px-4 py-3 mt-2 text-sm font-medium rounded-md transition-colors duration-200
               <?php echo is_active('/views/dashboard/index.php') ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'; ?>"
               :class="{ 'justify-start': sidebarOpen, 'justify-center': !sidebarOpen }">
                <i class="fas fa-tachometer-alt w-6 text-center" :class="{ 'mr-3': sidebarOpen }"></i>
                <span x-show="sidebarOpen">Dashboard</span>
            </a>
            <a href="<?php echo APP_URL; ?>/views/dashboard/websites.php"
               title="My Websites"
               class="flex items-center px-4 py-3 mt-2 text-sm font-medium rounded-md transition-colors duration-200
               <?php echo is_active('/views/dashboard/websites.php') ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'; ?>"
               :class="{ 'justify-start': sidebarOpen, 'justify-center': !sidebarOpen }">
                <i class="fas fa-globe w-6 text-center" :class="{ 'mr-3': sidebarOpen }"></i>
                <span x-show="sidebarOpen">My Websites</span>
            </a>
            <a href="<?php echo APP_URL; ?>/views/dashboard/send_sms.php"
               title="Send SMS"
               class="flex items-center px-4 py-3 mt-2 text-sm font-medium rounded-md transition-colors duration-200
               <?php echo is_active('/views/dashboard/send_sms.php') ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'; ?>"
               :class="{ 'justify-start': sidebarOpen, 'justify-center': !sidebarOpen }">
                <i class="fas fa-paper-plane w-6 text-center" :class="{ 'mr-3': sidebarOpen }"></i>
                <span x-show="sidebarOpen">Send SMS</span>
            </a>
            <a href="<?php echo APP_URL; ?>/views/dashboard/fraud_checker.php"
               title="Fraud Checker"
               class="flex items-center px-4 py-3 mt-2 text-sm font-medium rounded-md transition-colors duration-200
               <?php echo is_active('/views/dashboard/fraud_checker.php') ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'; ?>"
               :class="{ 'justify-start': sidebarOpen, 'justify-center': !sidebarOpen }">
                <i class="fas fa-shield-alt w-6 text-center" :class="{ 'mr-3': sidebarOpen }"></i>
                <span x-show="sidebarOpen">Fraud Checker</span>
            </a>
            <a href="<?php echo APP_URL; ?>/views/dashboard/profile.php"
               title="Profile"
               class="flex items-center px-4 py-3 mt-2 text-sm font-medium rounded-md transition-colors duration-200
               <?php echo is_active('/views/dashboard/profile.php') ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'; ?>"
               :class="{ 'justify-start': sidebarOpen, 'justify-center': !sidebarOpen }">
                <i class="fas fa-user-circle w-6 text-center" :class="{ 'mr-3': sidebarOpen }"></i>
                <span x-show="sidebarOpen">Profile</span>
            </a>
        </nav>
    </aside>

    <div class="flex-1 flex flex-col overflow-hidden transition-all duration-300"
         :class="{ 'lg:ml-64': sidebarOpen, 'lg:ml-20': !sidebarOpen }">
        
        <header class="flex items-center justify-between p-4 bg-white border-b">
            <button @click.stop="sidebarOpen = !sidebarOpen" class="text-gray-500 focus:outline-none">
                <i class="fas fa-bars"></i>
            </button>
            
            <div class="flex items-center">
                <span class="text-gray-500 text-sm hidden sm:block">
                    Hello, <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                </span>
                <a href="<?php echo APP_URL; ?>/logout.php" class="ml-4 text-gray-500 hover:text-gray-700 text-sm">
                    <i class="fas fa-sign-out-alt mr-1"></i>Logout
                </a>
            </div>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-4 sm:p-6 lg:p-8">
            