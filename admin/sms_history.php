<?php
require_once __DIR__ . '/../views/layouts/admin_header.php';
require_once __DIR__ . '/../models/SmsHistory.php';
require_once __DIR__ . '/../models/User.php';

$db = getDb();
$smsHistoryModel = new SmsHistory($db);
$userModel = new User($db);

// Get all users for the filter dropdown
$users = $userModel->findAll();

// Filtering and Pagination
$user_id = $_GET['user_id'] ?? '';
$search_term = $_GET['search'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$rows_per_page = $_GET['rows_per_page'] ?? 25;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $rows_per_page;

$total_rows = $smsHistoryModel->getTotalSmsHistoryCount($user_id, $search_term, $start_date, $end_date);
$total_pages = ceil($total_rows / $rows_per_page);
$sms_history = $smsHistoryModel->getSmsHistoryPaginated($rows_per_page, $offset, $user_id, $search_term, $start_date, $end_date);
?>

<div x-data="{ fullMessage: '', showModal: false }">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">SMS History</h1>

    <!-- Filter Form -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <form method="GET" action="sms_history.php" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label for="user_id" class="block text-sm font-medium text-gray-700">User</label>
                <select name="user_id" id="user_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                    <option value="">All Users</option>
                    <option value="system" <?php echo ($user_id === 'system') ? 'selected' : ''; ?>>System</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo htmlspecialchars($user['id']); ?>" <?php echo ($user_id == $user['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($user['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($search_term); ?>" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Phone or message...">
            </div>
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                <input type="date" name="start_date" id="start_date" value="<?php echo htmlspecialchars($start_date); ?>" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
            </div>
            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                <input type="date" name="end_date" id="end_date" value="<?php echo htmlspecialchars($end_date); ?>" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Filter</button>
                <a href="sms_history.php" class="ml-2 bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Clear</a>
            </div>
        </form>
    </div>

    <!-- SMS History Table -->
    <div class="bg-white shadow-md rounded-lg p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-gray-700">All SMS Records</h2>
            <form method="GET" action="sms_history.php">
                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">
                <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_term); ?>">
                <input type="hidden" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                <input type="hidden" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
                <select name="rows_per_page" onchange="this.form.submit()" class="text-sm border-gray-300 rounded-md">
                    <option value="25" <?php if ($rows_per_page == 25) echo 'selected'; ?>>25</option>
                    <option value="50" <?php if ($rows_per_page == 50) echo 'selected'; ?>>50</option>
                    <option value="100" <?php if ($rows_per_page == 100) echo 'selected'; ?>>100</option>
                </select>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 hidden md:table">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">To</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Message</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SMS Count</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Credit Deducted</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($sms_history as $sms): ?>
                        <tr class="hover:bg-gray-100">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($sms['user_name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('d M Y, h:i A', strtotime($sms['created_at'])); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($sms['to_number']); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <?php echo htmlspecialchars(substr($sms['message'], 0, 50)); ?>...
                                <button @click="fullMessage = `<?php echo htmlspecialchars(addslashes($sms['message'])); ?>`; showModal = true" class="text-blue-500 hover:underline">View Full</button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($sms['sms_count']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($sms['credit_deducted']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="grid grid-cols-1 gap-4 md:hidden">
                <?php foreach ($sms_history as $sms): ?>
                    <div class="bg-white p-4 rounded-lg shadow hover:bg-gray-100 border border-transparent hover:border-gray-200">
                        <div class="flex justify-between items-center">
                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($sms['user_name']); ?></div>
                            <div class="text-sm text-gray-500"><?php echo date('d M Y, h:i A', strtotime($sms['created_at'])); ?></div>
                        </div>
                        <div class="mt-2 text-sm text-gray-500">To: <?php echo htmlspecialchars($sms['to_number']); ?></div>
                        <div class="mt-2 text-sm text-gray-500">
                            <?php echo htmlspecialchars(substr($sms['message'], 0, 100)); ?>...
                            <button @click="fullMessage = `<?php echo htmlspecialchars(addslashes($sms['message'])); ?>`; showModal = true" class="text-blue-500 hover:underline">View Full</button>
                        </div>
                        <div class="mt-2 flex justify-between items-center">
                            <div class="text-sm text-gray-500">SMS Count: <?php echo htmlspecialchars($sms['sms_count']); ?></div>
                            <div class="text-sm text-gray-500">Credit: <?php echo htmlspecialchars($sms['credit_deducted']); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            <?php
            $queryParams = http_build_query([
                'user_id' => $user_id,
                'search' => $search_term,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'rows_per_page' => $rows_per_page
            ]);
            ?>
            <nav class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $rows_per_page, $total_rows); ?> of <?php echo $total_rows; ?> results
                </div>
                <div class="flex">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&<?php echo $queryParams; ?>" class="px-3 py-1 bg-white border border-gray-300 text-gray-500 rounded-md hover:bg-gray-50">Previous</a>
                    <?php endif; ?>
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&<?php echo $queryParams; ?>" class="ml-2 px-3 py-1 bg-white border border-gray-300 text-gray-500 rounded-md hover:bg-gray-50">Next</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </div>

    <!-- Full Message Modal -->
    <div x-show="showModal" class="fixed z-10 inset-0 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Full SMS Message</h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500" x-text="fullMessage"></p>
                    </div>
                </div>
                <div class="mt-5 sm:mt-6">
                    <button @click="showModal = false" type="button" class="inline-flex justify-center w-full rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-500 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:text-sm">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../views/layouts/admin_footer.php';
?>