<?php
header('Content-Type: application/json');
require_once __DIR__ . '/core/config.php';
require_once __DIR__ . '/core/db.php';
require_once __DIR__ . '/core/functions.php';
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

$csrf_token = $_POST['csrf_token'] ?? '';
if (!isset($_SESSION['csrf_token']) || $csrf_token !== $_SESSION['csrf_token']) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
    exit();
}

$phoneNumber = sanitize_input($_POST['phone_number'] ?? '');
$customerName = sanitize_input($_POST['customer_name'] ?? '');
$complaint = sanitize_input($_POST['complaint'] ?? '');

if (empty($phoneNumber) || empty($customerName) || empty($complaint)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit();
}

if (strlen($complaint) > 250) {
    echo json_encode(['success' => false, 'message' => 'Complaint cannot exceed 250 characters.']);
    exit();
}

$report = [
    'user_id' => $_SESSION['user_id'],
    'customer_name' => $customerName,
    'complaint' => $complaint,
    'reported_at' => date('Y-m-d H:i:s')
];

$courierStatsModel = new CourierStats($GLOBALS['pdo']);
$result = $courierStatsModel->addFraudReport($phoneNumber, $report);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Fraud report submitted successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to submit fraud report. Phone number not found in stats.']);
}
?>
