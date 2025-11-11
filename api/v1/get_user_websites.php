<?php
require_once __DIR__ . '/../../core/config.php';
require_once ROOT_PATH . '/core/functions.php';
require_once ROOT_PATH . '/models/Website.php';

header('Content-Type: application/json');

$db = getDb();
$websiteModel = new Website($db);

$user_id = $_GET['user_id'] ?? null;

if ($user_id) {
    $websites = $websiteModel->findByUser($user_id);
    echo json_encode($websites);
} else {
    echo json_encode([]);
}
