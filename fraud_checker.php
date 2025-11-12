<?php
header('Content-Type: application/json');

require_once __DIR__ . '/core/config.php';
require_once __DIR__ . '/core/db.php';
require_once __DIR__ . '/models/CourierHistory.php';

// Clear the debug log for this request
file_put_contents(__DIR__ . '/debug.log', '');

$phone = $_POST['phone_number'] ?? '';
$phone_clean = preg_replace('/[^0-9]/', '', $phone);

if (empty($phone_clean)) {
    echo json_encode(['error' => 'Invalid phone number.']);
    exit;
}

$db = getDb();
$courierHistoryModel = new CourierHistory($db);
$history = $courierHistoryModel->findByPhoneNumber($phone_clean);

if ($history) {
    returnData($history);
    exit;
}

// Fetch from Packzy API
$api_url = PACKZY_API_URL . $phone_clean;
file_put_contents(__DIR__ . '/debug.log', "Calling API: " . $api_url . "\n", FILE_APPEND);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 20);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

file_put_contents(__DIR__ . '/debug.log', "HTTP Code: " . $http_code . "\n", FILE_APPEND);
file_put_contents(__DIR__ . '/debug.log', "Response: " . $response . "\n", FILE_APPEND);
if ($curl_error) {
    file_put_contents(__DIR__ . '/debug.log', "cURL Error: " . $curl_error . "\n", FILE_APPEND);
}

$packzy_data = json_decode($response, true);

file_put_contents(__DIR__ . '/debug.log', "json_decode successful: " . (is_array($packzy_data) ? 'yes' : 'no') . "\n", FILE_APPEND);
file_put_contents(__DIR__ . '/debug.log', "packzy_data empty: " . (empty($packzy_data) ? 'yes' : 'no') . "\n", FILE_APPEND);
file_put_contents(__DIR__ . '/debug.log', "total_parcels set: " . (isset($packzy_data['total_parcels']) ? 'yes' : 'no') . "\n", FILE_APPEND);


if ($response === false || $http_code !== 200 || empty($packzy_data) || !isset($packzy_data['total_parcels'])) {
    file_put_contents(__DIR__ . '/debug.log', "Error condition triggered.\n", FILE_APPEND);
    echo json_encode(['error' => 'Failed to fetch courier data.']);
    exit;
}

if (!$history) {
    $history = new CourierHistory($db);
    $history->phone_number = $phone_clean;
}

$history->total_orders = $packzy_data['total_parcels'] ?? 0;
$history->total_delivered = $packzy_data['total_delivered'] ?? 0;
$history->total_cancelled = $packzy_data['total_cancelled'] ?? 0;
$history->save();

returnData($history);

function returnData(CourierHistory $history) {
    $success_rate = 0;
    if ($history->total_orders > 0) {
        $success_rate = ($history->total_delivered / $history->total_orders) * 100;
    }

    echo json_encode([
        'courier_name' => $history->courier_name,
        'total_orders' => $history->total_orders,
        'total_delivered' => $history->total_delivered,
        'total_cancelled' => $history->total_cancelled,
        'success_rate' => round($success_rate, 2)
    ]);
}