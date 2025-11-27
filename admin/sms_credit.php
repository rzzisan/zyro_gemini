<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../core/config.php'; // Defines ROOT_PATH
require_once ROOT_PATH . '/views/layouts/admin_header.php';
require_once ROOT_PATH . '/core/functions.php';
require_once ROOT_PATH . '/models/User.php';
require_once ROOT_PATH . '/models/Settings.php';
require_once ROOT_PATH . '/models/SmsCreditHistory.php';

$db = getDb();
$userModel = new User($db);
$settingsModel = new Settings($db);
$smsCreditHistoryModel = new SmsCreditHistory($db);

$master_balance = $settingsModel->get('master_sms_balance') ?? 0;
$total_assigned_credits = $userModel->getTotalAssignedCredits();
$unassigned_credits = $master_balance - $total_assigned_credits;

$total_credits_given = $smsCreditHistoryModel->getTotalCredits();
$total_credits_spent = $smsCreditHistoryModel->getTotalDebits();
$total_credits_remaining = $total_credits_given - $total_credits_spent;
?>

<h1 class="text-3xl font-bold text-gray-800 mb-6">SMS Credit Management</h1>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Master SMS Balance</h2>
        <p class="text-3xl font-bold text-gray-800"><?php echo $master_balance; ?></p>
    </div>
    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Total Assigned Credits</h2>
        <p class="text-3xl font-bold text-gray-800"><?php echo $total_assigned_credits; ?></p>
    </div>
    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Available Unassigned Credits</h2>
        <p class="text-3xl font-bold text-gray-800"><?php echo $unassigned_credits; ?></p>
    </div>
    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Total Credits Given</h2>
        <p class="text-3xl font-bold text-gray-800"><?php echo $total_credits_given; ?></p>
    </div>
    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Total Credits Spent</h2>
        <p class="text-3xl font-bold text-gray-800"><?php echo $total_credits_spent; ?></p>
    </div>
    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Total Credits Remaining</h2>
        <p class="text-3xl font-bold text-gray-800"><?php echo $total_credits_remaining; ?></p>
    </div>
</div>

<div class="mt-6 bg-white shadow-md rounded-lg p-6">
    <h2 class="text-xl font-semibold text-gray-700 mb-4">Set New Master Balance</h2>
    <form action="<?php echo APP_URL; ?>/controllers/adminController.php" method="POST">
        <input type="hidden" name="action" value="set_master_balance">
        <?php csrf_field(); ?>
        <div class="mb-4">
            <label for="master_balance" class="block text-gray-700 text-sm font-bold mb-2">Master Balance:</label>
            <input type="number" name="master_balance" id="master_balance" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>
        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Save
            </button>
        </div>
    </form>
</div>

<?php
require_once ROOT_PATH . '/views/layouts/admin_footer.php';
?>
