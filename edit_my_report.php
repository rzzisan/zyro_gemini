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
$customerName = isset($_POST['customer_name']) ? trim($_POST['customer_name']) : '';
$complaint = isset($_POST['complaint']) ? trim($_POST['complaint']) : '';

if (empty($phoneNumber) || empty($reportId) || empty($customerName) || empty($complaint)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit();
}

if (strlen($complaint) > 250) {
    echo json_encode(['success' => false, 'message' => 'Complaint cannot exceed 250 characters.']);
    exit();
}

$courierStatsModel = new CourierStats($GLOBALS['pdo']);
$result = $courierStatsModel->updateUserReport($_SESSION['user_id'], $phoneNumber, $reportId, $customerName, $complaint);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Report updated successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update report.']);
}
?>
