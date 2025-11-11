<?php
include '../layouts/header.php';

require_once ROOT_PATH . '/models/Website.php';
require_once ROOT_PATH . '/models/ApiToken.php';

$db = getDb();
$websiteModel = new Website($db);
$apiTokenModel = new ApiToken($db);
$user_id = get_user_id();
$websites = $websiteModel->findByUser($user_id);
?>

<h1 class="text-3xl font-bold text-gray-800 mb-6">My Websites & API Keys</h1>

<?php display_message(); ?>

<div class="bg-white shadow-md rounded-lg p-6 mb-6">
    <h2 class="text-xl font-semibold text-gray-700 mb-4">Add New Website</h2>
    <form action="../../controllers/websiteController.php" method="POST" class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
        <input type="hidden" name="action" value="add_website">
        <div class="flex-grow w-full sm:w-auto">
            <label for="domain" class="sr-only">Website URL</label>
            <input type="url" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="domain" name="domain" placeholder="https://example.com" required>
        </div>
        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full sm:w-auto">Add Website</button>
    </form>
</div>

<div class="bg-white shadow-md rounded-lg p-6">
    <h2 class="text-xl font-semibold text-gray-700 mb-4">Existing Websites</h2>
    <?php if ($websites): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Domain</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">API Key</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($websites as $website): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($website['domain']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php
                                $key = $apiTokenModel->findByWebsite($website['id']);
                                echo htmlspecialchars($key ?: 'N/A');
                                ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <form method="POST" action="../../controllers/websiteController.php" onsubmit="return confirm('Are you sure you want to delete this website and its API key?');" class="inline-block">
                                    <input type="hidden" name="action" value="delete_website">
                                    <input type="hidden" name="website_id" value="<?php echo $website['id']; ?>">
                                    <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-gray-600">No websites added yet.</p>
    <?php endif; ?>
</div>

<?php include '../layouts/footer.php'; ?>