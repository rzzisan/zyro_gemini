<?php
require_once __DIR__ . '/core/config.php';
require_once ROOT_PATH . '/controllers/smsController.php';

error_log("POST data: " . print_r($_POST, true) . "\n", 3, ROOT_PATH . '/debug.log');

$action = $_POST['action'] ?? '';

if ($action === 'send_sms') {
    SmsController::handleSendSmsRequest();
} else {
    error_log("Invalid action: " . $action . "\n", 3, ROOT_PATH . '/debug.log');
    header('Location: ' . APP_URL . '/views/dashboard/send_sms.php?error=Invalid action');
    exit;
}
