<?php
// 1. Set execution limits
ignore_user_abort(true);
set_time_limit(3600); // 1 hour

// 2. Includes
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/functions.php';
require_once __DIR__ . '/../models/CourierCache.php';

echo "Starting courier cache update cron job...\n";

// 3. DB Connection
$db = getDb();

// 4. Initialize model
$cacheModel = new CourierCache($db);

// 5. Get 500 oldest records
$records = $cacheModel->getOldest(500);
$updated_count = 0;

if (empty($records)) {
    echo "No records needed updating. Cron job finished.\n";
    exit;
}

// 6. Loop through records
foreach ($records as $record) {
    $phone_clean = $record['phone_number'];
    
    // Fetch from Packzy API
    $api_url = PACKZY_API_URL . $phone_clean;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . PACKZY_API_KEY]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $packzy_data = json_decode($response, true);

    // Process & Save Data
    if ($response !== false && $http_code === 200 && !empty($packzy_data) && isset($packzy_data['total_parcels'])) {
        $success_rate = 0;
        if (isset($packzy_data['total_delivered'], $packzy_data['total_parcels']) && $packzy_data['total_parcels'] > 0) {
            $success_rate = ($packzy_data['total_delivered'] / $packzy_data['total_parcels']) * 100;
        }
        
        if ($cacheModel->save($phone_clean, $packzy_data, $success_rate)) {
            $updated_count++;
            echo "Updated cache for {$phone_clean}.\n";
        } else {
            echo "Failed to update cache for {$phone_clean}.\n";
        }
    } else {
        echo "Failed to fetch data for {$phone_clean}. HTTP Code: {$http_code}\n";
    }

    // 7. Sleep to avoid overwhelming the API
    sleep(1);
}

// 8. Completion message
echo "Cron job completed. " . $updated_count . " of " . count($records) . " records updated.\n";