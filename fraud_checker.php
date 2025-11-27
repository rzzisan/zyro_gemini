<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/core/config.php';
require_once ROOT_PATH . '/core/db.php';
require_once ROOT_PATH . '/core/functions.php';
require_once ROOT_PATH . '/models/CourierStats.php';

header('Content-Type: application/json');


$response = ['success' => false, 'message' => 'An error occurred.'];

if (isset($_POST['phone_number'])) {
    $phoneNumber = $_POST['phone_number'];

    // Validate phone number format (01xxxxxxxxx)
    if (!preg_match('/^01[3-9]\d{8}$/', $phoneNumber)) {
        $response['message'] = 'Invalid phone number format. Please use the format 01xxxxxxxxx.';
        echo json_encode($response);
        exit;
    }

    try {
        $db = getDB();
        $courierStats = new CourierStats($db);

        $stats = $courierStats->findByPhoneNumber($phoneNumber);

        $isCacheExpired = !$stats || (time() - strtotime($stats['last_updated_at'])) > (FRAUD_CHECKER_CACHE_EXPIRATION * 24 * 60 * 60);

        if ($stats && !$isCacheExpired) {
            $userReports = [];
            if (!empty($stats['user_reports'])) {
                $decodedReports = json_decode($stats['user_reports'], true);
                if (is_array($decodedReports)) {
                    $userReports = $decodedReports;
                }
            }
            $stats['total_fraud_reports'] += count($userReports);
            $stats['user_reports_data'] = $userReports;

            $response = ['success' => true, 'data' => $stats];
        } else {
            $apiUrl = PACKZY_API_URL . $phoneNumber;
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Add timeout
            
            $apiResult = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            $apiSuccess = false;

            if (!$curlError && $httpCode == 200) {
                $apiData = json_decode($apiResult, true);

                if (json_last_error() === JSON_ERROR_NONE && isset($apiData['total_parcels']) && isset($apiData['total_delivered']) && isset($apiData['total_cancelled'])) {
                    $dataToSave = [
                        'courier_name' => 'SteadFast',
                        'phone_number' => $phoneNumber,
                        'total_parcels' => $apiData['total_parcels'] ?? 0,
                        'total_delivered' => $apiData['total_delivered'] ?? 0,
                        'total_cancelled' => $apiData['total_cancelled'] ?? 0,
                        'total_fraud_reports' => is_array($apiData['total_fraud_reports']) ? count($apiData['total_fraud_reports']) : ($apiData['total_fraud_reports'] ?? 0),
                    ];

                    $courierStats->upsert($dataToSave);
                    
                    // Re-fetch to get fresh data with correct structure if needed, or just use dataToSave
                    // Adding user reports logic for consistency
                    $stats = $courierStats->findByPhoneNumber($phoneNumber); // Fetch the upserted record
                    $apiSuccess = true;
                }
            }

            if ($apiSuccess) {
                // Process fresh stats (same logic as cached)
                $userReports = [];
                if (!empty($stats['user_reports'])) {
                    $decodedReports = json_decode($stats['user_reports'], true);
                    if (is_array($decodedReports)) {
                        $userReports = $decodedReports;
                    }
                }
                $stats['total_fraud_reports'] += count($userReports);
                $stats['user_reports_data'] = $userReports;

                $response = ['success' => true, 'data' => $stats];
            } else {
                // API Failed. Check if we have old stats to fallback to.
                if ($stats) {
                    $userReports = [];
                    if (!empty($stats['user_reports'])) {
                        $decodedReports = json_decode($stats['user_reports'], true);
                        if (is_array($decodedReports)) {
                            $userReports = $decodedReports;
                        }
                    }
                    $stats['total_fraud_reports'] += count($userReports);
                    $stats['user_reports_data'] = $userReports;
                    
                    $response = [
                        'success' => true, 
                        'data' => $stats, 
                        'message' => 'Showing cached data (Live check failed: ' . ($curlError ?: "HTTP $httpCode") . ')'
                    ];
                } else {
                    // No cache, and API failed.
                    if ($curlError) {
                        $response['message'] = 'Failed to fetch data from API: ' . $curlError;
                    } elseif ($httpCode != 200) {
                        $response['message'] = 'Failed to fetch data from API. HTTP code: ' . $httpCode;
                    } else {
                         $response['message'] = 'Invalid API response format.';
                    }
                }
            }
        }
    } catch (Exception $e) {
        $response['message'] = 'A server error occurred.';
    }
} else {
    $response['message'] = 'Phone number is required.';
}

echo json_encode($response);