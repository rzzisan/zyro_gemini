<?php
require_once __DIR__ . '/../views/layouts/admin_header.php';
require_once ROOT_PATH . '/models/CourierStats.php';
require_once ROOT_PATH . '/core/db.php';

$db = getDB();
$courierStats = new CourierStats($db);

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$rowsPerPage = isset($_GET['rows_per_page']) ? (int)$_GET['rows_per_page'] : 25;
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

$totalRows = $courierStats->getTotalCourierStatsCount($searchTerm);
$totalPages = ceil($totalRows / $rowsPerPage);
$stats = $courierStats->getCourierStatsPaginated($page, $rowsPerPage, $searchTerm);
?>

<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Fraud Checker</h1>

    <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 mb-8">
        <form id="fraud-checker-form" action="fraud_checker.php" method="GET" class="flex flex-col sm:flex-row items-center gap-4">
            <div class="flex-grow w-full sm:w-auto">
                <label for="search" class="sr-only">Search Phone:</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <input type="text" id="search" name="search" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Enter phone number..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                </div>
            </div>
            <div class="flex items-center gap-4 w-full sm:w-auto">
                <div class="w-full sm:w-auto">
                    <label for="rows_per_page" class="sr-only">Rows per page:</label>
                    <select id="rows_per_page" name="rows_per_page" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                        <option value="25" <?php if ($rowsPerPage == 25) echo 'selected'; ?>>25 per page</option>
                        <option value="50" <?php if ($rowsPerPage == 50) echo 'selected'; ?>>50 per page</option>
                        <option value="100" <?php if ($rowsPerPage == 100) echo 'selected'; ?>>100 per page</option>
                        <option value="500" <?php if ($rowsPerPage == 500) echo 'selected'; ?>>500 per page</option>
                        <option value="1000" <?php if ($rowsPerPage == 1000) echo 'selected'; ?>>1000 per page</option>
                    </select>
                </div>
                <button type="submit" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 w-full sm:w-auto">
                    Filter
                </button>
                <a href="user_fraud_reports.php" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 w-full sm:w-auto">
                    User Fraud Reports
                </a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="px-4 sm:px-6 py-4">
            <h2 class="text-2xl font-bold text-gray-800">Cached Courier History</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SL</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone Number</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Parcels</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Delivered</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Cancelled</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Fraud Reports</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Updated</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($stats)) : ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 whitespace-nowrap text-sm text-gray-500 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m-9 4h12M3 7h18M5 12h14M4 17h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                    <p class="mt-2">No data available.</p>
                                    <p class="text-xs text-gray-400">Try adjusting your search or filter.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php $serialNumber = (($page - 1) * $rowsPerPage); ?>
                        <?php foreach ($stats as $index => $stat) : ?>
                            <tr class="hover:bg-gray-50 transition-colors duration-200">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo $serialNumber + $index + 1; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($stat['phone_number']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($stat['total_parcels']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($stat['total_delivered']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($stat['total_cancelled']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold <?php echo $stat['total_fraud_reports'] > 0 ? 'text-red-600' : 'text-gray-500'; ?>"><?php echo htmlspecialchars($stat['total_fraud_reports']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($stat['last_updated_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if ($totalPages > 1) : ?>
        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
            <div class="flex-1 flex justify-between sm:hidden">
                <?php if ($page > 1) : ?>
                    <a href="?page=<?php echo $page - 1; ?>&rows_per_page=<?php echo $rowsPerPage; ?>&search=<?php echo urlencode($searchTerm); ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Previous
                    </a>
                <?php endif; ?>
                <?php if ($page < $totalPages) : ?>
                    <a href="?page=<?php echo $page + 1; ?>&rows_per_page=<?php echo $rowsPerPage; ?>&search=<?php echo urlencode($searchTerm); ?>" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Next
                    </a>
                <?php endif; ?>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Showing
                        <span class="font-medium"><?php echo ($page - 1) * $rowsPerPage + 1; ?></span>
                        to
                        <span class="font-medium"><?php echo min($page * $rowsPerPage, $totalRows); ?></span>
                        of
                        <span class="font-medium"><?php echo $totalRows; ?></span>
                        results
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <?php if ($page > 1) : ?>
                            <a href="?page=<?php echo $page - 1; ?>&rows_per_page=<?php echo $rowsPerPage; ?>&search=<?php echo urlencode($searchTerm); ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <span class="sr-only">Previous</span>
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        <?php endif; ?>

                        <?php
                        $startPage = max(1, $page - 2);
                        $endPage = min($totalPages, $page + 2);

                        if ($startPage > 1) {
                            echo '<a href="?page=1&rows_per_page=' . $rowsPerPage . '&search=' . urlencode($searchTerm) . '" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">1</a>';
                            if ($startPage > 2) {
                                echo '<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>';
                            }
                        }

                        for ($i = $startPage; $i <= $endPage; $i++) {
                            $activeClass = $i == $page ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50';
                            echo '<a href="?page=' . $i . '&rows_per_page=' . $rowsPerPage . '&search=' . urlencode($searchTerm) . '" class="relative inline-flex items-center px-4 py-2 border text-sm font-medium ' . $activeClass . '">' . $i . '</a>';
                        }

                        if ($endPage < $totalPages) {
                            if ($endPage < $totalPages - 1) {
                                echo '<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>';
                            }
                            echo '<a href="?page=' . $totalPages . '&rows_per_page=' . $rowsPerPage . '&search=' . urlencode($searchTerm) . '" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">' . $totalPages . '</a>';
                        }
                        ?>

                        <?php if ($page < $totalPages) : ?>
                            <a href="?page=<?php echo $page + 1; ?>&rows_per_page=<?php echo $rowsPerPage; ?>&search=<?php echo urlencode($searchTerm); ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <span class="sr-only">Next</span>
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        <?php endif; ?>
                    </nav>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../views/layouts/admin_footer.php'; ?>
