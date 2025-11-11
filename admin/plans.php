<?php
require_once __DIR__ . '/../core/config.php';
require_once ROOT_PATH . '/core/functions.php';
require_once ROOT_PATH . '/models/Plan.php';
require_once ROOT_PATH . '/views/layouts/admin_header.php';

$db = getDb();
$planModel = new Plan($db);
$plans = $planModel->findAll();

// Display flash message if set
if (isset($_SESSION['flash_message'])) {
    echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">' . htmlspecialchars($_SESSION['flash_message']) . '</div>';
    unset($_SESSION['flash_message']);
}
?>

<h1 class="text-3xl font-bold text-gray-800 mb-6">Manage Plans</h1>

<div class="bg-white shadow-md rounded-lg p-6 mb-6">
    <h2 class="text-xl font-semibold text-gray-700 mb-4">Create New Plan</h2>
    <form action="<?php echo APP_URL; ?>/controllers/adminController.php" method="POST">
        <input type="hidden" name="action" value="create_plan">
        <div class="mb-4">
            <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Plan Name:</label>
            <input type="text" id="name" name="name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>
        
        <div class="mb-4">
            <label for="price" class="block text-gray-700 text-sm font-bold mb-2">Price:</label>
            <input type="number" id="price" name="price" step="0.01" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>
        
        <div class="mb-4">
            <label for="daily_courier_limit" class="block text-gray-700 text-sm font-bold mb-2">Daily Courier Limit:</label>
            <input type="number" id="daily_courier_limit" name="daily_courier_limit" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>
        
        <div class="mb-6">
            <label for="sms_credit_bonus" class="block text-gray-700 text-sm font-bold mb-2">SMS Credit Bonus:</label>
            <input type="number" id="sms_credit_bonus" name="sms_credit_bonus" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>
        
        <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Create Plan</button>
    </form>
</div>

<div class="bg-white shadow-md rounded-lg p-6">
    <h2 class="text-xl font-semibold text-gray-700 mb-4">Existing Plans</h2>
    <?php if (empty($plans)): ?>
        <p class="text-gray-600">No plans found. Create one above!</p>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Daily Courier Limit</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SMS Credit Bonus</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($plans as $plan): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($plan['id']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($plan['name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($plan['price']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($plan['daily_courier_limit']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($plan['sms_credit_bonus']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="<?php echo APP_URL; ?>/admin/edit_plan.php?id=<?php echo htmlspecialchars($plan['id']); ?>" class="text-indigo-600 hover:text-indigo-900 mr-4">Edit</a>
                                <form method="POST" action="<?php echo APP_URL; ?>/controllers/adminController.php" onsubmit="return confirm('Are you sure you want to delete this plan?');" class="inline-block">
                                    <input type="hidden" name="action" value="delete_plan">
                                    <input type="hidden" name="plan_id" value="<?php echo htmlspecialchars($plan['id']); ?>">
                                    <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php
require_once ROOT_PATH . '/views/layouts/admin_footer.php';
?>
