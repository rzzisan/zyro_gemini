<?php
header("Content-Type: application/json");

// 1. Include dependencies
require_once __DIR__ . '/../../core/auth_guard.php';
require_once __DIR__ . '/../../core/CreditService.php';
require_once __DIR__ . '/../../models/SmsCredit.php';
require_once __DIR__ . '/../../core/config.php'; // For SMS_GATEWAY_* constants

// 2. Authenticate the request
// This function will handle errors and exit if authentication fails.
$user_id = authenticate_plugin_request();

// 3. Get DB connection
$db = getDb();

// 4. Get JSON body from the request
$data = json_decode(file_get_contents('php://input'), true);

// 5. Check for required fields
if (empty($data['phone']) || empty($data['message'])) {
    return_json_error('Phone or message missing.', 400);
}
$phone = $data['phone'];
$message = $data['message'];

// 6. Calculate needed credits
$credits_needed = CreditService::calculateSmsCredits($message);

// 7. Check credits
$creditModel = new SmsCredit($db);
if (!$creditModel->hasCredits($user_id, $credits_needed)) {
    return_json_error('Insufficient SMS credits.', 402); // 402 Payment Required
}

// 8. Send SMS via Gateway
$queryParams = http_build_query([
    'apikey' => SMS_GATEWAY_API_KEY,
    'secretkey' => SMS_GATEWAY_SECRET_KEY,
    'callerID' => SMS_GATEWAY_SENDER_ID,
    'toUser' => $phone,
    'messageContent' => $message
]);

$url = SMS_GATEWAY_URL . '?' . $queryParams;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 20);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

// 9. Handle Gateway Response
if ($response === false || $http_code != 200) {
    // Log the error for debugging purposes
    error_log("SMS Gateway Error: cURL error - {$curl_error}, HTTP Code - {$http_code}, Response - {$response}");
    return_json_error('SMS Gateway failed.', 502); // 502 Bad Gateway
}

// If gateway call was successful, deduct credits and return success response
if ($creditModel->deductCredits($user_id, $credits_needed)) {
    echo json_encode([
        'success' => true,
        'message' => 'SMS sent.',
        'credits_remaining' => $creditModel->getBalance($user_id)
    ]);
} else {
    // This is a critical error state: SMS was sent, but credits were not deducted.
    // This could happen in a race condition if the balance was spent between the `hasCredits` check and this `deductCredits` call.
    error_log("CRITICAL: Failed to deduct {$credits_needed} credits from user {$user_id} after sending SMS.");
    return_json_error('Failed to deduct credits after sending.', 500);
}

exit;
