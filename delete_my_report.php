<?php
header('Content-Type: application/json');
require_once __DIR__ . '/core/config.php';
require_once __DIR__ . '/core/db.php';
require_once __DIR__ . '/core/functions.php';
require_once __DIR__ . '/models/CourierStats.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$user_id = null;
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;

if ($is_admin) {
    $user_id = $_SESSION['admin_id'] ?? null;
} else {
    $user_id = $_SESSION['user_id'] ?? null;
}

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

$csrf_token = $_POST['csrf_token'] ?? '';
if (!isset($_SESSION['csrf_token']) || $csrf_token !== $_SESSION['csrf_token']) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
    exit();
}

$phoneNumber = sanitize_input($_POST['phone_number'] ?? '');
$reportId = sanitize_input($_POST['report_id'] ?? '');

if (empty($phoneNumber) || empty($reportId)) {
    echo json_encode(['success' => false, 'message' => 'Phone number and report ID are required.']);
    exit();
}

$courierStatsModel = new CourierStats($GLOBALS['pdo']);

$result = $courierStatsModel->deleteUserReport($user_id, $phoneNumber, $reportId, $is_admin);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Report deleted successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete report.']);
}
?>
