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

$courierStatsModel = new CourierStats($GLOBALS['pdo']);
$reports = $courierStatsModel->getReportsByUserId($_SESSION['user_id']);

echo json_encode(['success' => true, 'data' => $reports]);
?>
