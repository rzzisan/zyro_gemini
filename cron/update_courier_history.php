<?php
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../models/CourierHistory.php';

$db = getDb();

$three_days_ago = new DateTime('-3 days');
$stmt = $db->prepare('SELECT * FROM courier_history WHERE last_updated < ?');
$stmt->execute([$three_days_ago->format('Y-m-d H:i:s')]);
$histories = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($histories as $history_data) {
    $history = CourierHistory::fromArray($history_data);

    // Fetch from Packzy API
    $api_url = PACKZY_API_URL . $history->phone_number;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . PACKZY_API_KEY]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $packzy_data = json_decode($response, true);

    if ($response !== false && $http_code === 200 && !empty($packzy_data) && isset($packzy_data['total_parcels'])) {
        $history->total_orders = $packzy_data['total_parcels'] ?? 0;
        $history->total_delivered = $packzy_data['total_delivered'] ?? 0;
        $history->total_cancelled = $packzy_data['total_cancelled'] ?? 0;
        $history->save();
        echo "Updated phone number: " . $history->phone_number . "\n";
    } else {
        echo "Failed to update phone number: " . $history->phone_number . "\n";
    }
    // Sleep for a second to avoid rate limiting
    sleep(1);
}
