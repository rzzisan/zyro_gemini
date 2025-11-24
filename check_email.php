<?php
header('Content-Type: application/json');

require_once __DIR__ . '/core/config.php';
require_once __DIR__ . '/core/db.php';
require_once __DIR__ . '/models/User.php';

$email = $_GET['email'] ?? null;

if (!$email) {
    echo json_encode(['exists' => false, 'error' => 'Email not provided.']);
    exit;
}

$db = getDb();
$userModel = new User($db);

$user = $userModel->findByEmail($email);

if ($user) {
    echo json_encode(['exists' => true]);
} else {
    echo json_encode(['exists' => false]);
}
