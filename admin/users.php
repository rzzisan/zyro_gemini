<?php
require_once __DIR__ . '/../core/config.php';
require_once ROOT_PATH . '/core/functions.php';
require_once ROOT_PATH . '/models/User.php';

// Ensure admin access
ensureAdmin();

$db = getDb();
$userModel = new User($db);

// --- Helper Function to Render Table Rows ---
function renderUserRows($users) {
    if (empty($users)) {
        return '<tr><td colspan="10" class="text-center py-4 text-gray-500">No users found.</td></tr>';
    }
    $html = '';
    foreach ($users as $user) {
        $html .= '<tr class="hover:bg-gray-50 border-b border-gray-200 transition-colors duration-150">';
        $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">' . htmlspecialchars($user['id']) . '</td>';
        $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">' . htmlspecialchars($user['name']) . '</td>';
        $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">' . htmlspecialchars($user['email']) . '</td>';
        $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">' . htmlspecialchars($user['phone_number'] ?? 'N/A') . '</td>';
        $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">' . htmlspecialchars($user['plan_name'] ?? 'N/A') . '</td>';
        $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">' . htmlspecialchars($user['sms_balance'] ?? '0') . '</td>';
        $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">' . htmlspecialchars($user['daily_courier_limit'] ?? '0') . '</td>';
        $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><a href="#" class="text-indigo-600 hover:underline" onclick="showWebsites(' . $user['id'] . ')">' . $user['website_count'] . '</a></td>';
        $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">' . htmlspecialchars($user['created_at']) . '</td>';
        $html .= '<td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">';
        $html .= '<a href="edit_user.php?id=' . $user['id'] . '" class="text-indigo-600 hover:text-indigo-900">Edit</a>';
        $html .= '<a href="add_website.php?user_id=' . $user['id'] . '" class="text-green-600 hover:text-green-900">Add Website</a>';
        $html .= '<form method="POST" action="' . APP_URL . '/controllers/adminController.php" class="inline-block" onsubmit="return confirm(\'Are you sure?\');">';
        $html .= '<input type="hidden" name="action" value="delete_user">';
        $html .= '<input type="hidden" name="user_id" value="' . $user['id'] . '">';
        $html .= csrf_field_html(); // Using helper for CSRF input
        $html .= '<button type="submit" class="text-red-600 hover:text-red-900 ml-2">Delete</button>';
        $html .= '</form>';
        $html .= '</td>';
        $html .= '</tr>';
    }
    return $html;
}

// Helper to generate CSRF input (since verify_csrf_token is in functions but not an input generator)
function csrf_field_html() {
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

// --- AJAX Request Handler ---
if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $rows_per_page = isset($_GET['rows_per_page']) ? $_GET['rows_per_page'] : 25;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';

    if ($rows_per_page === 'all') {
        $limit = 1000000;
        $offset = 0;
        $page = 1;
    } else {
        $limit = (int)$rows_per_page;
        $offset = ($page - 1) * $limit;
    }

    // Fetch data
    $users = $userModel->findAll($limit, $offset, $search);
    $total_users = $userModel->getUserCount($search);
    $total_pages = ($rows_per_page === 'all') ? 1 : ceil($total_users / $limit);

    // Generate HTML for Table Rows
    $tableHtml = renderUserRows($users);

    // Generate HTML for Pagination
    $paginationHtml = '';
    if ($rows_per_page !== 'all' && $total_pages > 1) {
        // Previous Button
        $prevDisabled = ($page <= 1) ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50';
        $prevPage = max(1, $page - 1);
        $paginationHtml .= '<button onclick="fetchData(' . $prevPage . ')" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 ' . $prevDisabled . '" ' . ($page <= 1 ? 'disabled' : '') . '>';
        $paginationHtml .= '<span class="sr-only">Previous</span><i class="fas fa-chevron-left h-5 w-5"></i></button>';

        // Page Numbers
        $start = max(1, $page - 2);
        $end = min($total_pages, $page + 2);

        if ($start > 1) {
            $paginationHtml .= '<button onclick="fetchData(1)" class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium">1</button>';
            if ($start > 2) $paginationHtml .= '<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>';
        }

        for ($i = $start; $i <= $end; $i++) {
            $activeClass = ($i == $page) ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50';
            $paginationHtml .= '<button onclick="fetchData(' . $i . ')" class="relative inline-flex items-center px-4 py-2 border text-sm font-medium ' . $activeClass . '">' . $i . '</button>';
        }

        if ($end < $total_pages) {
            if ($end < $total_pages - 1) $paginationHtml .= '<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>';
            $paginationHtml .= '<button onclick="fetchData(' . $total_pages . ')" class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium">' . $total_pages . '</button>';
        }

        // Next Button
        $nextDisabled = ($page >= $total_pages) ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50';
        $nextPage = min($total_pages, $page + 1);
        $paginationHtml .= '<button onclick="fetchData(' . $nextPage . ')" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 ' . $nextDisabled . '" ' . ($page >= $total_pages ? 'disabled' : '') . '>';
        $paginationHtml .= '<span class="sr-only">Next</span><i class="fas fa-chevron-right h-5 w-5"></i></button>';
    }

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode([
        'html' => $tableHtml,
        'pagination' => $paginationHtml,
        'showing_start' => ($total_users > 0) ? $offset + 1 : 0,
        'showing_end' => min($offset + $limit, $total_users),
        'total_records' => $total_users
    ]);
    exit;
}

// --- Initial Page Load (Non-AJAX) ---
require_once __DIR__ . '/../views/layouts/admin_header.php';

// Initial Data Fetch
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$rows_per_page = isset($_GET['rows_per_page']) ? $_GET['rows_per_page'] : 25;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if ($rows_per_page === 'all') {
    $limit = 1000000;
    $offset = 0;
} else {
    $limit = (int)$rows_per_page;
    $offset = ($page - 1) * $limit;
}

$users = $userModel->findAll($limit, $offset, $search);
$total_users = $userModel->getUserCount($search);
$total_pages = ($rows_per_page === 'all') ? 1 : ceil($total_users / $limit);
?>

<h1 class="text-3xl font-bold text-gray-800 mb-6">User Management</h1>

<div class="mb-6">
    <a href="add_user.php" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline shadow">
        <i class="fas fa-plus mr-2"></i>Add New User
    </a>
</div>

<form method="GET" action="" class="bg-white shadow-md rounded-lg w-full flex flex-col sm:flex-row justify-between items-center p-4 mb-4 gap-4">
    <div class="flex items-center text-gray-700 text-sm">
        <span class="mr-2">Show</span>
        <select name="rows_per_page" id="rows_per_page" onchange="fetchData(1)" class="form-select border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:border-indigo-500 shadow-sm">
            <option value="25" <?php echo $rows_per_page == 25 ? 'selected' : ''; ?>>25</option>
            <option value="50" <?php echo $rows_per_page == 50 ? 'selected' : ''; ?>>50</option>
            <option value="100" <?php echo $rows_per_page == 100 ? 'selected' : ''; ?>>100</option>
            <option value="all" <?php echo $rows_per_page === 'all' ? 'selected' : ''; ?>>All</option>
        </select>
        <span class="ml-2">entries</span>
    </div>

    <div class="flex items-center text-gray-700 text-sm w-full sm:w-auto">
        <label for="search" class="mr-2 font-medium">Search:</label>
        <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($search); ?>" 
               class="form-input border border-gray-300 rounded px-3 py-1 text-sm focus:outline-none focus:border-indigo-500 shadow-sm w-full sm:w-64"
               placeholder="Name, Email, or Phone..." autocomplete="off">
    </div>
</form>

<div class="bg-white shadow-md rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone Number</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plan</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SMS Credits</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Courier Limit</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Websites</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registered At</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody id="user-table-body" class="bg-white divide-y divide-gray-200">
                <?php echo renderUserRows($users); ?>
            </tbody>
        </table>
    </div>
    
    <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 flex flex-col sm:flex-row items-center justify-between">
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between w-full">
            <div>
                <p class="text-sm text-gray-700">
                    Showing
                    <span id="showing-start" class="font-medium"><?php echo ($total_users > 0) ? $offset + 1 : 0; ?></span>
                    to
                    <span id="showing-end" class="font-medium"><?php echo min($offset + $limit, $total_users); ?></span>
                    of
                    <span id="total-records" class="font-medium"><?php echo $total_users; ?></span>
                    entries
                </p>
            </div>
            <div>
                <nav id="pagination-container" class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                    <?php 
                        // Render initial pagination (reusing logic via AJAX call structure would be cleaner, but duplicating for first paint is faster here)
                        if ($rows_per_page !== 'all' && $total_pages > 1) {
                            // Render functionality handled by JS on load to keep PHP dry? No, render for SEO/No-JS.
                            // ... (Simplified: The JS will take over, but here's static HTML)
                            $prevDisabled = ($page <= 1) ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50';
                            echo '<button onclick="fetchData('.max(1, $page - 1).')" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 '.$prevDisabled.'" '.($page<=1?'disabled':'').'><i class="fas fa-chevron-left h-5 w-5"></i></button>';
                            
                            // Simplified static pagination for initial load
                            echo '<button class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">Page '.$page.' of '.$total_pages.'</button>';

                            $nextDisabled = ($page >= $total_pages) ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50';
                            echo '<button onclick="fetchData('.min($total_pages, $page + 1).')" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 '.$nextDisabled.'" '.($page>=$total_pages?'disabled':'').'><i class="fas fa-chevron-right h-5 w-5"></i></button>';
                        }
                    ?>
                </nav>
            </div>
        </div>
    </div>
</div>

<div id="website-modal" class="fixed z-10 inset-0 overflow-y-auto hidden" style="background-color: rgba(0,0,0,0.5);">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Websites</h3>
                <button type="button" class="text-gray-400 hover:text-gray-500 focus:outline-none" onclick="closeModal()">
                    <span class="sr-only">Close</span>
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="mt-2">
                <ul id="website-list" class="divide-y divide-gray-200 border rounded bg-gray-50 max-h-60 overflow-y-auto">
                    </ul>
            </div>
            <div class="mt-5 sm:mt-6">
                <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm" onclick="closeModal()">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // --- Live Search & Pagination Logic ---
    let debounceTimer;

    // Debounce function to delay API calls
    function debounce(func, delay) {
        return function(...args) {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => func.apply(this, args), delay);
        }
    }

    function fetchData(page) {
        const search = document.getElementById('search').value;
        const rowsPerPage = document.getElementById('rows_per_page').value;

        // Update URL without reloading (optional, but good for UX)
        const url = new URL(window.location.href);
        url.searchParams.set('page', page);
        url.searchParams.set('rows_per_page', rowsPerPage);
        url.searchParams.set('search', search);
        window.history.pushState({}, '', url);

        // Fetch Data via AJAX
        fetch(`<?php echo APP_URL; ?>/admin/users.php?ajax=1&page=${page}&rows_per_page=${rowsPerPage}&search=${encodeURIComponent(search)}`)
            .then(response => response.json())
            .then(data => {
                // Update Table Rows
                document.getElementById('user-table-body').innerHTML = data.html;
                
                // Update Pagination Controls
                document.getElementById('pagination-container').innerHTML = data.pagination;
                
                // Update Info Text
                document.getElementById('showing-start').textContent = data.showing_start;
                document.getElementById('showing-end').textContent = data.showing_end;
                document.getElementById('total-records').textContent = data.total_records;
            })
            .catch(error => console.error('Error fetching data:', error));
    }

    // Attach Listeners
    const searchInput = document.getElementById('search');
    searchInput.addEventListener('input', debounce(() => fetchData(1), 300)); // 300ms debounce

    // Existing Website Modal Logic
    function showWebsites(userId) {
        fetch(`<?php echo APP_URL; ?>/api/v1/get_user_websites.php?user_id=${userId}`)
            .then(response => response.json())
            .then(data => {
                const websiteList = document.getElementById('website-list');
                websiteList.innerHTML = '';
                if (data.length > 0) {
                    data.forEach(website => {
                        const listItem = document.createElement('li');
                        listItem.className = 'py-2 px-3 text-sm text-gray-700';
                        listItem.textContent = website.domain;
                        websiteList.appendChild(listItem);
                    });
                } else {
                    const listItem = document.createElement('li');
                    listItem.className = 'py-2 px-3 text-sm text-gray-500 italic';
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