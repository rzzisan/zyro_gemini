<?php
require_once __DIR__ . '/../views/layouts/admin_header.php';
require_once __DIR__ . '/../models/User.php';

$db = getDb();
$userModel = new User($db);
$users = $userModel->findAll();
?>

<h1 class="text-3xl font-bold text-gray-800 mb-6">User Management</h1>

<div class="mb-6">
    <a href="add_user.php" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Add New User</a>
</div>

<div class="bg-white shadow-md rounded-lg p-6">
    <h2 class="text-xl font-semibold text-gray-700 mb-4">All Users</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plan</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SMS Credits</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Courier Limit</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Websites</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registered At</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['id']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($user['name']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($user['email']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($user['plan_name'] ?? 'N/A'); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($user['sms_balance'] ?? '0'); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($user['daily_courier_limit'] ?? '0'); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <a href="#" class="text-blue-500 hover:text-blue-700" onclick="showWebsites(<?php echo $user['id']; ?>)"><?php echo $user['website_count']; ?></a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($user['created_at']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="text-indigo-600 hover:text-indigo-900 mr-4">Edit</a>
                            <a href="add_website.php?user_id=<?php echo $user['id']; ?>" class="text-green-600 hover:text-green-900 mr-4">Add Website</a>
                            <form method="POST" action="<?php echo APP_URL; ?>/controllers/adminController.php" class="inline-block">
                                <input type="hidden" name="action" value="delete_user">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Website list modal -->
<div id="website-modal" class="fixed z-10 inset-0 overflow-y-auto hidden">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <div>
                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                    Websites
                </h3>
                <div class="mt-2">
                    <ul id="website-list" class="divide-y divide-gray-200">
                        <!-- Website list will be populated here -->
                    </ul>
                </div>
            </div>
            <div class="mt-5 sm:mt-6">
                <button type="button" class="inline-flex justify-center w-full rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-500 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:text-sm" onclick="closeModal()">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function showWebsites(userId) {
        fetch(`<?php echo APP_URL; ?>/api/v1/get_user_websites.php?user_id=${userId}`)
            .then(response => response.json())
            .then(data => {
                const websiteList = document.getElementById('website-list');
                websiteList.innerHTML = '';
                if (data.length > 0) {
                    data.forEach(website => {
                        const listItem = document.createElement('li');
                        listItem.className = 'py-2';
                        listItem.textContent = website.domain;
                        websiteList.appendChild(listItem);
                    });
                } else {
                    const listItem = document.createElement('li');
                    listItem.className = 'py-2';
                    listItem.textContent = 'No websites found for this user.';
                    websiteList.appendChild(listItem);
                }
                document.getElementById('website-modal').classList.remove('hidden');
            });
    }

    function closeModal() {
        document.getElementById('website-modal').classList.add('hidden');
    }
</script>

<?php
require_once __DIR__ . '/../views/layouts/admin_footer.php';
?>