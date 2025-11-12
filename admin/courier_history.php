<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../views/layouts/admin_header.php';
require_once __DIR__ . '/../models/CourierHistory.php';

$db = getDb();
$courierHistoryModel = new CourierHistory($db);

$search_term = $_GET['search'] ?? '';
$rows_per_page = $_GET['rows_per_page'] ?? 50;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $rows_per_page;

$total_rows = $courierHistoryModel->getTotalCourierHistoryCount($search_term);
$total_pages = ceil($total_rows / $rows_per_page);
$courier_history = $courierHistoryModel->getCourierHistoryPaginated($rows_per_page, $offset, $search_term);
?>

<div>
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Courier History</h1>

    <!-- Filter Form -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <form method="GET" action="courier_history.php" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($search_term); ?>" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Phone number...">
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Filter</button>
                <a href="courier_history.php" class="ml-2 bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Clear</a>
            </div>
        </form>
    </div>

    <!-- Courier History Table -->
    <div class="bg-white shadow-md rounded-lg p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-gray-700">All Courier Records</h2>
            <form method="GET" action="courier_history.php">
                <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_term); ?>">
                <select name="rows_per_page" onchange="this.form.submit()" class="text-sm border-gray-300 rounded-md">
                    <option value="50" <?php if ($rows_per_page == 50) echo 'selected'; ?>>50</option>
                    <option value="100" <?php if ($rows_per_page == 100) echo 'selected'; ?>>100</option>
                    <option value="500" <?php if ($rows_per_page == 500) echo 'selected'; ?>>500</option>
                    <option value="1000" <?php if ($rows_per_page == 1000) echo 'selected'; ?>>1000</option>
                </select>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 hidden md:table">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Orders</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Delivered</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Cancelled</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Success Rate</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Updated</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($courier_history as $history): ?>
                        <tr class="hover:bg-gray-100">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($history->phone_number); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($history->total_orders); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($history->total_delivered); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($history->total_cancelled); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php
                                $success_rate = 0;
                                if ($history->total_orders > 0) {
                                    $success_rate = ($history->total_delivered / $history->total_orders) * 100;
                                }
                                echo round($success_rate, 2) . '%';
                                ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('d M Y, h:i A', strtotime($history->last_updated)); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="grid grid-cols-1 gap-4 md:hidden">
                <?php foreach ($courier_history as $history): ?>
                    <div class="bg-white p-4 rounded-lg shadow hover:bg-gray-100 border border-transparent hover:border-gray-200">
                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($history->phone_number); ?></div>
                        <div class="mt-2 text-sm text-gray-500">Total Orders: <?php echo htmlspecialchars($history->total_orders); ?></div>
                        <div class="mt-2 text-sm text-gray-500">Total Delivered: <?php echo htmlspecialchars($history->total_delivered); ?></div>
                        <div class="mt-2 text-sm text-gray-500">Total Cancelled: <?php echo htmlspecialchars($history->total_cancelled); ?></div>
                        <div class="mt-2 text-sm text-gray-500">
                            Success Rate: 
                            <?php
                            $success_rate = 0;
                            if ($history->total_orders > 0) {
                                $success_rate = ($history->total_delivered / $history->total_orders) * 100;
                            }
                            echo round($success_rate, 2) . '%';
                            ?>
                        </div>
                        <div class="mt-2 text-sm text-gray-500">Last Updated: <?php echo date('d M Y, h:i A', strtotime($history->last_updated)); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            <?php
            $queryParams = http_build_query([
                'search' => $search_term,
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
</div>

<?php
require_once __DIR__ . '/../views/layouts/admin_footer.php';
?>
