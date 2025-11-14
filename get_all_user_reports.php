<?php
header('Content-Type: application/json');
require_once __DIR__ . '/core/config.php';
require_once __DIR__ . '/core/db.php';
require_once __DIR__ . '/models/CourierStats.php';
require_once __DIR__ . '/models/User.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Administrator access required.']);
    exit();
}

// Get parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$rowsPerPage = isset($_GET['rows_per_page']) ? (int)$_GET['rows_per_page'] : 25;
$searchTerm = isset($_GET['search']) ? strtolower(trim($_GET['search'])) : '';

$userModel = new User($GLOBALS['pdo']);
$userMap = $userModel->getAllUsersAsMap();

$courierStatsModel = new CourierStats($GLOBALS['pdo']);
$allReports = $courierStatsModel->getAllUserReports($userMap);

// Filter results if a search term is provided
$filteredReports = $allReports;
if (!empty($searchTerm)) {
    $filteredReports = array_filter($allReports, function ($report) use ($searchTerm) {
        return (
            strpos(strtolower($report['phone_number']), $searchTerm) !== false ||
            strpos(strtolower($report['customer_name']), $searchTerm) !== false ||
            strpos(strtolower($report['complaint']), $searchTerm) !== false ||
            strpos(strtolower($report['user_name']), $searchTerm) !== false
        );
    });
}

// Paginate the results
$totalRows = count($filteredReports);
$totalPages = ceil($totalRows / $rowsPerPage);
$offset = ($page - 1) * $rowsPerPage;
$paginatedData = array_slice($filteredReports, $offset, $rowsPerPage);

echo json_encode([
    'success' => true,
    'data' => $paginatedData,
    'pagination' => [
        'total_rows' => $totalRows,
        'total_pages' => $totalPages,
        'current_page' => $page,
        'rows_per_page' => $rowsPerPage
    ]
]);
?>
