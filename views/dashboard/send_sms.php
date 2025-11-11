<?php
require_once __DIR__ . '/../layouts/header.php';
require_once ROOT_PATH . '/models/User.php';
require_once ROOT_PATH . '/core/db.php';

$db = getDb();
$user_id = get_user_id();
$userModel = new User($db);
$user = $userModel->find($user_id);

?>

<h1 class="text-3xl font-bold text-gray-800 mb-6">Send SMS</h1>

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

<div class="mt-6 bg-white shadow-md rounded-lg p-6">
    <div class="flex justify-between items-center mb-4">
        <p class="text-gray-600">Your SMS Credits: <?php echo htmlspecialchars($user['sms_balance'] ?? '0'); ?></p>
        <a href="<?php echo APP_URL; ?>/views/dashboard/sms_history.php" class="text-blue-500 hover:text-blue-700">SMS History</a>
    </div>
    <form action="<?php echo APP_URL; ?>/send_sms.php" method="POST">
        <input type="hidden" name="action" value="send_sms">
        <div class="mb-4">
            <label for="to" class="block text-gray-700 text-sm font-bold mb-2">To:</label>
            <input type="text" name="to" id="to" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>
        <div class="mb-4">
            <label for="message" class="block text-gray-700 text-sm font-bold mb-2">Message:</label>
            <textarea name="message" id="message" rows="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required></textarea>
            <div id="char-count" class="text-sm text-gray-600 mt-1"></div>
        </div>
        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Send SMS
            </button>
        </div>
    </form>
</div>

<script>
    const messageTextarea = document.getElementById('message');
    const charCountDiv = document.getElementById('char-count');

    messageTextarea.addEventListener('input', () => {
        const message = messageTextarea.value;
        const charCount = message.length;
        const isUnicode = /[^\x00-\x7F]/.test(message);

        let sms_limit = 160;
        let sms_count = 1;

        if (isUnicode) {
            sms_limit = 70;
        }

        sms_count = Math.ceil(charCount / sms_limit);

        charCountDiv.textContent = `${charCount} characters / ${sms_count} SMS (${sms_limit} chars/SMS)`;
    });
</script>

<?php include '../layouts/footer.php'; ?>