<?php
require_once __DIR__ . '/../views/layouts/admin_header.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Plan.php';
require_once __DIR__ . '/../models/Subscription.php';
require_once __DIR__ . '/../models/SmsCredit.php';

$db = getDb();

if (!isset($_GET['id'])) {
    redirect('/admin/users.php');
}

$user_id = $_GET['id'];
$userModel = new User($db);
$user_details = $userModel->getUserDetails($user_id);

if (!$user_details) {
    $_SESSION['flash_message'] = 'User not found.';
    redirect('/admin/users.php');
}

$planModel = new Plan($db);
$plans = $planModel->findAll();
?>

<h1 class="text-3xl font-bold text-gray-800 mb-6">Edit User: <?php echo htmlspecialchars($user_details['name']); ?></h1>

<?php
if (isset($_SESSION['flash_message'])) {
    echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">' . htmlspecialchars($_SESSION['flash_message']) . '</div>';
    unset($_SESSION['flash_message']);
}
?>

<div class="bg-white shadow-md rounded-lg p-6 mb-6">
    <h2 class="text-xl font-semibold text-gray-700 mb-4">Edit User Details</h2>
    <form action="<?php echo APP_URL; ?>/controllers/adminController.php" method="POST">
        <input type="hidden" name="action" value="update_user">
        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_details['id']); ?>">
        
        <div class="mb-4">
            <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Name:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user_details['name']); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>
        
        <div class="mb-4">
            <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_details['email']); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>
        
        <div class="mb-4">
            <label for="role" class="block text-gray-700 text-sm font-bold mb-2">Role:</label>
            <select id="role" name="role" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <option value="user" <?php echo ($user_details['role'] == 'user') ? 'selected' : ''; ?>>User</option>
                <option value="admin" <?php echo ($user_details['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
            </select>
        </div>

        <div class="mb-6">
            <label for="password" class="block text-gray-700 text-sm font-bold mb-2">New Password (leave blank to keep current):</label>
            <input type="password" id="password" name="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Enter new password">
        </div>
        
        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Update User Details</button>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Form 1: Update Subscription -->
    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Manage Subscription</h2>
        <p class="text-gray-600 mb-4">Current Plan: <span class="font-bold"><?php echo htmlspecialchars($user_details['plan_name'] ?? 'Not Subscribed'); ?></span></p>
        
        <form action="<?php echo APP_URL; ?>/controllers/adminController.php" method="POST">
            <input type="hidden" name="action" value="update_subscription">
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_details['id']); ?>">
            
            <div class="mb-4">
                <label for="new_plan_id" class="block text-gray-700 text-sm font-bold mb-2">New Plan:</label>
                <select id="new_plan_id" name="new_plan_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <?php foreach ($plans as $plan): ?>
                        <option value="<?php echo $plan['id']; ?>" <?php echo ($plan['name'] == $user_details['plan_name']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($plan['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Update Plan</button>
        </form>
    </div>

    <!-- Form 2: Update SMS Credits -->
    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Manage SMS Credits</h2>
        <p class="text-gray-600 mb-4">Current Credits: <span class="font-bold"><?php echo htmlspecialchars($user_details['balance'] ?? '0'); ?></span></p>
        
        <form action="<?php echo APP_URL; ?>/controllers/adminController.php" method="POST">
            <input type="hidden" name="action" value="set_credits">
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_details['id']); ?>">
            
            <div class="mb-4">
                <label for="new_balance" class="block text-gray-700 text-sm font-bold mb-2">Set New Balance:</label>
                <input type="number" id="new_balance" name="new_balance" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Enter new credit balance" required>
            </div>
            
            <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Set Credits</button>
        </form>
    </div>
</div>

<div class="mt-6">
    <a href="<?php echo APP_URL; ?>/admin/users.php" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">Back to User List</a>
</div>


<?php
require_once __DIR__ . '/../views/layouts/admin_footer.php';
?>