<?php

require_once __DIR__ . '/../core/config.php'; // Defines ROOT_PATH
require_once ROOT_PATH . '/views/layouts/admin_header.php';
require_once ROOT_PATH . '/core/functions.php';
require_once ROOT_PATH . '/models/User.php';

$db = getDb();
$userModel = new User($db);

$totalUsers = $userModel->getUserCount();
?>

<h1 class="text-3xl font-bold text-gray-800 mb-6">Admin Dashboard</h1>

<p class="text-gray-700 mb-6">Welcome to the admin dashboard. Here you can manage users, plans, and other settings.</p>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Total Users</h2>
        <p class="text-3xl font-bold text-gray-800"><?php echo $totalUsers; ?></p>
    </div>
</div>

<div class="mt-6">
    <a href="<?php echo APP_URL; ?>/admin/users.php" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">Manage Users</a>
</div>


<?php
require_once ROOT_PATH . '/views/layouts/admin_footer.php';
?>

