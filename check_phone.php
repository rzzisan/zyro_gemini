<?php
header('Content-Type: application/json');

require_once __DIR__ . '/core/config.php';
require_once __DIR__ . '/core/db.php';
require_once __DIR__ . '/models/User.php';

$phone = $_GET['phone'] ?? null;

if (!$phone) {
    echo json_encode(['exists' => false, 'error' => 'Phone number not provided.']);
    exit;
}

$db = getDb();
$userModel = new User($db);

$user = $userModel->findByPhoneNumber($phone);

if ($user) {
    echo json_encode(['exists' => true]);
} else {
    echo json_encode(['exists' => false]);
}
