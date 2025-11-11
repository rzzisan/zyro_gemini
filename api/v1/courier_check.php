<?php
header("Content-Type: application/json");

// 1. Includes
require_once __DIR__ . '/../../core/config.php';
require_once __DIR__ . '/../../core/functions.php';
require_once __DIR__ . '/../../core/auth_guard.php';
require_once __DIR__ . '/../../models/UsageLog.php';
require_once __DIR__ . '/../../models/CourierCache.php';
require_once __DIR__ . '/../../models/Plan.php';
require_once __DIR__ . '/../../models/User.php';

// 2. Authenticate
$user_id = authenticate_plugin_request();

// 3. DB Connection
$db = getDb();

// 4. Get request body
$data = json_decode(file_get_contents('php://input'), true);

// 5. Get phones
$phones = $data['phones'] ?? [];
if (empty($phones) || !is_array($phones)) {
    return_json_error('No phones provided.', 400);
}

// 6. Initialize models
$planModel = new Plan($db);
$usageLogModel = new UsageLog($db);
$cacheModel = new CourierCache($db);

// 7. Get user's plan
$plan = $planModel->getForUser($user_id);
if (!$plan || !isset($plan['daily_courier_limit'])) {
    return_json_error('Could not determine user plan or plan limits.', 500);
}

// 8. Get daily usage
$daily_usage = $usageLogModel->getDailyUniqueUsageCount($user_id, 'courier_check');

// 9. Initialize results
$results = [];
$three_days_ago = new DateTime('-3 days');

// 10. Loop through phones
foreach (array_unique($phones) as $phone) {
    $phone_clean = preg_replace('/[^0-9]/', '', $phone);
    if (empty($phone_clean)) {
        $results[$phone] = ['error' => 'Invalid phone number format.'];
        continue;
    }

    // Check Cache
    $cached_data = $cacheModel->find($phone_clean);
    if ($cached_data) {
        $last_updated = new DateTime($cached_data['last_updated']);
        if ($last_updated > $three_days_ago) {
            $results[$phone] = json_decode($cached_data['courier_data'], true);
            continue; // Use cache and move to next phone
        }
    }

    // Check Daily Limit (on cache miss or stale cache)
    $already_checked_today = $usageLogModel->hasCheckedToday($user_id, 'courier_check', $phone_clean);
    if (!$already_checked_today && $daily_usage >= $plan['daily_courier_limit']) {
        $results[$phone] = ['error' => 'Daily limit exceeded'];
        continue;
    }

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
    if ($response === false || $http_code !== 200 || empty($packzy_data) || !isset($packzy_data['total_parcels'])) {
        $results[$phone] = ['error' => 'Failed to fetch courier data'];
    } else {
        $success_rate = 0;
        if (isset($packzy_data['total_delivered'], $packzy_data['total_parcels']) && $packzy_data['total_parcels'] > 0) {
            $success_rate = ($packzy_data['total_delivered'] / $packzy_data['total_parcels']) * 100;
        }
        
        $cacheModel->save($phone_clean, $packzy_data, $success_rate);
        $results[$phone] = $packzy_data;

        // Record Usage
        if (!$already_checked_today) {
            $usageLogModel->recordUsage($user_id, 'courier_check', $phone_clean);
            $daily_usage++;
        }
    }
}

// 11. Send final response
echo json_encode($results);
exit;
