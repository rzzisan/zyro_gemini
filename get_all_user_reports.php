<?php
header('Content-Type: application/json');
require_once __DIR__ . '/core/config.php';
require_once __DIR__ . '/core/db.php';
require_once __DIR__ . '/models/CourierStats.php';
require_once __DIR__ . '/models/User.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Administrator access required.']);
    exit();
}

$userModel = new User($GLOBALS['pdo']);
$userMap = $userModel->getAllUsersAsMap();

$courierStatsModel = new CourierStats($GLOBALS['pdo']);
$reports = $courierStatsModel->getAllUserReports($userMap);

echo json_encode(['success' => true, 'data' => $reports]);
?>
