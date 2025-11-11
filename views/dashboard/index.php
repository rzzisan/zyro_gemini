<?php
require_once __DIR__ . '/../layouts/header.php';
require_once ROOT_PATH . '/models/Subscription.php';
require_once ROOT_PATH . '/models/Plan.php';
require_once ROOT_PATH . '/models/UsageLog.php';
require_once ROOT_PATH . '/models/User.php';

$db = getDb();
$user_id = get_user_id();

$subscriptionModel = new Subscription($db);
$planModel = new Plan($db);
$usageLogModel = new UsageLog($db);
$userModel = new User($db);

$subscription = $subscriptionModel->findByUser($user_id);
$plan = null;
if ($subscription) {
    $plan = $planModel->find($subscription['plan_id']);
}

$user = $userModel->find($user_id);
$usage_count = $usageLogModel->getDailyUniqueUsageCount($user_id, 'courier_check');
?>

<h1 class="text-3xl font-bold text-gray-800 mb-6">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>

<?php if (isset($_GET['message'])): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline"><?php echo htmlspecialchars($_GET['message']); ?></span>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline"><?php echo htmlspecialchars($_GET['error']); ?></span>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Your Plan</h2>
        <?php if ($plan): ?>
            <h3 class="text-lg font-medium text-gray-900 mb-2"><?php echo htmlspecialchars($plan['name']); ?></h3>
            <p class="text-gray-600">
                Daily Courier Check Limit: <?php echo htmlspecialchars($plan['daily_courier_limit']); ?>
            </p>
        <?php else: ?>
            <p class="text-gray-600">No active plan found.</p>
        <?php endif; ?>
    </div>
    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Today's Usage</h2>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Courier Checks</h3>
        <p class="text-gray-600">
            <?php echo $usage_count; ?> / <?php echo $plan ? htmlspecialchars($plan['daily_courier_limit']) : 'N/A'; ?>
        </p>
    </div>
</div>

<?php include '../layouts/footer.php'; ?>