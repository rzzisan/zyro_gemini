<?php
require_once ROOT_PATH . '/core/config.php';
require_once ROOT_PATH . '/core/db.php';
require_once ROOT_PATH . '/core/functions.php';
require_once ROOT_PATH . '/models/SmsCredit.php';
require_once ROOT_PATH . '/models/User.php';
require_once ROOT_PATH . '/models/Settings.php';
require_once ROOT_PATH . '/models/SmsCreditHistory.php';
require_once ROOT_PATH . '/models/SmsHistory.php';

class SmsController {
    public static function sendSms($to, $message) {
        $url = SMS_GATEWAY_URL;
        $apiKey = SMS_GATEWAY_API_KEY;
        $secretKey = SMS_GATEWAY_SECRET_KEY;
        $senderId = SMS_GATEWAY_SENDER_ID;
        $data = [
            'apikey' => $apiKey,
            'secretkey' => $secretKey,
            'callerID' => $senderId,
            'toUser' => $to,
            'messageContent' => $message,
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($error) {
            return false;
        }

        if ($http_code !== 200) {
            return false;
        }

        return true;
    }

    public static function handleSendSmsRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user_id = get_user_id();
            $to = $_POST['to'];
            $message = $_POST['message'];

            // Validate and format the phone number
            $to = formatPhoneNumber($_POST['to']);

            if (!$to) {
                header('Location: ' . APP_URL . '/views/dashboard/send_sms.php?error=Invalid phone number format');
                exit;
            }

            $db = getDb();
            $userModel = new User($db);
            $smsCreditModel = new SmsCredit($db);
            $settingsModel = new Settings($db);
            $smsCreditHistoryModel = new SmsCreditHistory($db);
            $smsHistoryModel = new SmsHistory($db);

            $user = $userModel->find($user_id);

            $isUnicode = preg_match('/[^\x00-\x7F]/', $message);
            $sms_limit = $isUnicode ? 70 : 160;
            $sms_count = ceil(strlen($message) / $sms_limit);

            if ($user && isset($user['sms_balance']) && $user['sms_balance'] >= $sms_count) {
                if (self::sendSms($to, $message)) {
                    $smsCreditModel->deductCredits($user_id, $sms_count);
                    $smsCreditHistoryModel->addCreditHistory($user_id, 'debit', $sms_count);
                    $smsHistoryModel->addSmsHistory($user_id, $to, $message, $sms_count, $sms_count);

                    // Deduct from master balance
                    $master_balance = $settingsModel->get('master_sms_balance');
                    if ($master_balance) {
                        $new_master_balance = $master_balance - $sms_count;
                        $settingsModel->set('master_sms_balance', $new_master_balance);
                    }

                    // Redirect with success message
                    header('Location: ' . APP_URL . '/views/dashboard/send_sms.php?message=SMS sent successfully');
                } else {
                    // Redirect with error message
                    header('Location: ' . APP_URL . '/views/dashboard/send_sms.php?error=Failed to send SMS');
                }
            } else {
                // Redirect with error message
                header('Location: ' . APP_URL . '/views/dashboard/send_sms.php?error=Insufficient SMS credits');
            }
        }
    }
}


