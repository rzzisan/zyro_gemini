<?php
require_once __DIR__ . '/../views/layouts/admin_header.php';
require_once __DIR__ . '/../models/User.php';

$db = getDb();
$userModel = new User($db);
$users = $userModel->findAll();
?>

<h1 class="text-3xl font-bold text-gray-800 mb-6">Add Website</h1>

<div class="bg-white shadow-md rounded-lg p-6">
    <form action="<?php echo APP_URL; ?>/controllers/adminController.php" method="POST">
        <input type="hidden" name="action" value="add_website">

        <div class="mb-4">
            <label for="user_id" class="block text-gray-700 text-sm font-bold mb-2">User:</label>
            <select name="user_id" id="user_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                <option value="">Select a user</option>
                <?php $selectedUserId = $_GET['user_id'] ?? null;

foreach ($users as $user): 
    $selected = ($user['id'] == $selectedUserId) ? 'selected' : '';
    echo "<option value='{$user['id']}' $selected>" . htmlspecialchars($user['name']) . "</option>";
endforeach; ?>
            </select>
        </div>

        <div class="mb-4">
            <label for="domain" class="block text-gray-700 text-sm font-bold mb-2">Domain:</label>
            <input type="text" name="domain" id="domain" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Add Website
            </button>
            <a href="users.php" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                Cancel
            </a>
        </div>
    </form>
</div>

<?php
require_once __DIR__ . '/../views/layouts/admin_footer.php';
?>
