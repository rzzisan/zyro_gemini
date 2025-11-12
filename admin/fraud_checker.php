<?php
require_once __DIR__ . '/../views/layouts/admin_header.php';
require_once ROOT_PATH . '/models/CourierStats.php';
require_once ROOT_PATH . '/core/db.php';

$db = getDB();
$courierStats = new CourierStats($db);

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$rowsPerPage = isset($_GET['rows_per_page']) ? (int)$_GET['rows_per_page'] : 50;
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

$totalRows = $courierStats->getTotalCourierStatsCount($searchTerm);
$totalPages = ceil($totalRows / $rowsPerPage);
$stats = $courierStats->getCourierStatsPaginated($page, $rowsPerPage, $searchTerm);
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Fraud Checker</h1>

    <div class="bg-white p-6 rounded-lg shadow-md">
        <form id="fraud-checker-form" action="fraud_checker.php" method="GET">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="search" class="block text-gray-700 text-sm font-bold mb-2">Search Phone:</label>
                    <input type="text" id="search" name="search" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Enter phone number" value="<?php echo htmlspecialchars($searchTerm); ?>">
                </div>
                <div>
                    <label for="rows_per_page" class="block text-gray-700 text-sm font-bold mb-2">Rows per page:</label>
                    <select id="rows_per_page" name="rows_per_page" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="50" <?php if ($rowsPerPage == 50) echo 'selected'; ?>>50</option>
                        <option value="100" <?php if ($rowsPerPage == 100) echo 'selected'; ?>>100</option>
                        <option value="500" <?php if ($rowsPerPage == 500) echo 'selected'; ?>>500</option>
                        <option value="1000" <?php if ($rowsPerPage == 1000) echo 'selected'; ?>>1000</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="mt-8 bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold mb-4">Cached Courier History</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="w-1/6 py-3 px-4 uppercase font-semibold text-sm">Phone Number</th>
                        <th class="w-1/6 py-3 px-4 uppercase font-semibold text-sm">Total Parcels</th>
                        <th class="w-1/6 py-3 px-4 uppercase font-semibold text-sm">Total Delivered</th>
                        <th class="w-1/6 py-3 px-4 uppercase font-semibold text-sm">Total Cancelled</th>
                        <th class="w-1/6 py-3 px-4 uppercase font-semibold text-sm">Total Fraud Reports</th>
                        <th class="w-1/6 py-3 px-4 uppercase font-semibold text-sm">Last Updated</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    <?php foreach ($stats as $stat) : ?>
                        <tr class="border-b">
                            <td class="py-3 px-4"><?php echo htmlspecialchars($stat['phone_number']); ?></td>
                            <td class="py-3 px-4"><?php echo htmlspecialchars($stat['total_parcels']); ?></td>
                            <td class="py-3 px-4"><?php echo htmlspecialchars($stat['total_delivered']); ?></td>
                            <td class="py-3 px-4"><?php echo htmlspecialchars($stat['total_cancelled']); ?></td>
                            <td class="py-3 px-4"><?php echo htmlspecialchars($stat['total_fraud_reports']); ?></td>
                            <td class="py-3 px-4"><?php echo htmlspecialchars($stat['last_updated_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            <?php if ($totalPages > 1) : ?>
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Page <?php echo $page; ?> of <?php echo $totalPages; ?>
                    </div>
                    <div class="flex">
                        <?php if ($page > 1) : ?>
                            <a href="?page=<?php echo $page - 1; ?>&rows_per_page=<?php echo $rowsPerPage; ?>&search=<?php echo urlencode($searchTerm); ?>" class="px-3 py-1 border rounded-md bg-gray-200 hover:bg-gray-300">Previous</a>
                        <?php endif; ?>
                        <?php if ($page < $totalPages) : ?>
                            <a href="?page=<?php echo $page + 1; ?>&rows_per_page=<?php echo $rowsPerPage; ?>&search=<?php echo urlencode($searchTerm); ?>" class="px-3 py-1 border rounded-md bg-gray-200 hover:bg-gray-300 ml-2">Next</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../views/layouts/admin_footer.php'; ?>
