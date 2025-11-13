<?php
header('Content-Type: application/json');
require_once __DIR__ . '/core/config.php';
require_once __DIR__ . '/core/db.php';
require_once __DIR__ . '/models/CourierStats.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

$phoneNumber = isset($_POST['phone_number']) ? trim($_POST['phone_number']) : '';
$reportId = isset($_POST['report_id']) ? trim($_POST['report_id']) : '';

if (empty($phoneNumber) || empty($reportId)) {
    echo json_encode(['success' => false, 'message' => 'Phone number and report ID are required.']);
    exit();
}

$courierStatsModel = new CourierStats($GLOBALS['pdo']);

$isAdmin = (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true);

$result = $courierStatsModel->deleteUserReport($_SESSION['user_id'], $phoneNumber, $reportId, $isAdmin);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Report deleted successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete report.']);
}
?>
