<?php
require_once __DIR__ . '/../core/config.php';
require_once ROOT_PATH . '/core/functions.php';
require_once ROOT_PATH . '/models/Plan.php';
require_once ROOT_PATH . '/views/layouts/admin_header.php';

// 1. Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('/admin/plans.php');
}

$plan_id = $_GET['id'];
$db = getDb();
$planModel = new Plan($db);
$plan = $planModel->find($plan_id);

// 2. Check if plan exists
if (!$plan) {
    $_SESSION['flash_message'] = 'Plan not found.';
    redirect('/admin/plans.php');
}
?>

<h1 class="text-3xl font-bold text-gray-800 mb-6">Edit Plan: <?php echo htmlspecialchars($plan['name']); ?></h1>

<div class="bg-white shadow-md rounded-lg p-6 mb-6 max-w-lg mx-auto">
    <form action="<?php echo APP_URL; ?>/controllers/adminController.php" method="POST">
        <input type="hidden" name="action" value="update_plan">
        <input type="hidden" name="plan_id" value="<?php echo htmlspecialchars($plan['id']); ?>">
        
        <div class="mb-4">
            <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Plan Name:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($plan['name']); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>
        
        <div class="mb-4">
            <label for="price" class="block text-gray-700 text-sm font-bold mb-2">Price:</label>
            <input type="number" id="price" name="price" step="0.01" value="<?php echo htmlspecialchars($plan['price']); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>
        
        <div class="mb-4">
            <label for="daily_courier_limit" class="block text-gray-700 text-sm font-bold mb-2">Daily Courier Limit:</label>
            <input type="number" id="daily_courier_limit" name="daily_courier_limit" value="<?php echo htmlspecialchars($plan['daily_courier_limit']); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>
        
        <div class="mb-6">
            <label for="sms_credit_bonus" class="block text-gray-700 text-sm font-bold mb-2">SMS Credit Bonus:</label>
            <input type="number" id="sms_credit_bonus" name="sms_credit_bonus" value="<?php echo htmlspecialchars($plan['sms_credit_bonus']); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>
        
        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Update Plan</button>
            <a href="<?php echo APP_URL; ?>/admin/plans.php" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">Cancel</a>
        </div>
    </form>
</div>

<?php
require_once ROOT_PATH . '/views/layouts/admin_footer.php';
?>