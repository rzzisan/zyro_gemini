<?php
require_once __DIR__ . '/../core/config.php';
require_once ROOT_PATH . '/core/functions.php';
require_once ROOT_PATH . '/models/Plan.php';
require_once ROOT_PATH . '/models/User.php';
require_once ROOT_PATH . '/models/Subscription.php';
require_once ROOT_PATH . '/models/SmsCredit.php';
require_once ROOT_PATH . '/models/Website.php';
require_once ROOT_PATH . '/models/Settings.php';
require_once ROOT_PATH . '/models/SmsCreditHistory.php';

ensureAdmin(); // Protect the entire controller

$db = getDb();
$planModel = new Plan($db);
$userModel = new User($db);
$subscriptionModel = new Subscription($db);
$smsCreditModel = new SmsCredit($db);
$websiteModel = new Website($db);
$settingsModel = new Settings($db);
$smsCreditHistoryModel = new SmsCreditHistory($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    verify_csrf_token($_POST['csrf_token'] ?? '');

    switch ($_POST['action']) {
        case 'set_master_balance':
            if (isset($_POST['master_balance'])) {
                $settingsModel->set('master_sms_balance', $_POST['master_balance']);
                $_SESSION['flash_message'] = 'Master balance updated successfully!';
            } else {
                $_SESSION['flash_message'] = 'Failed to update master balance. Invalid data provided.';
            }
            redirect('/admin/index.php');
            break;
        case 'create_plan':
            // Basic validation
            if (!empty($_POST['name']) && isset($_POST['price'])) {
                $planModel->create($_POST);
                $_SESSION['flash_message'] = 'Plan created successfully!';
            } else {
                $_SESSION['flash_message'] = 'Failed to create plan. Name and price are required.';
            }
            redirect('/admin/plans.php');
            break;

        case 'update_plan':
            $plan_id = $_POST['plan_id'] ?? null;
            if ($plan_id && !empty($_POST['name']) && isset($_POST['price'])) {
                $planModel->update($plan_id, $_POST);
                $_SESSION['flash_message'] = 'Plan updated successfully!';
            } else {
                $_SESSION['flash_message'] = 'Failed to update plan. Invalid data provided.';
            }
            redirect('/admin/plans.php');
            break;

        case 'delete_plan':
            $plan_id = $_POST['plan_id'] ?? null;
            if ($plan_id) {
                $planModel->delete($plan_id);
                $_SESSION['flash_message'] = 'Plan deleted successfully!';
            } else {
                $_SESSION['flash_message'] = 'Failed to delete plan. Plan ID was missing.';
            }
            redirect('/admin/plans.php');
            break;

        case 'create_user':
            if (!empty($_POST['name']) && !empty($_POST['email']) && !empty($_POST['password']) && !empty($_POST['plan_id']) && !empty($_POST['phone_number'])) {
                $user_id = $userModel->createUser(
                    $_POST['name'], 
                    $_POST['email'], 
                    $_POST['password'], 
                    $_POST['phone_number'],
                    $_POST['district'] ?? null,
                    $_POST['upazila'] ?? null,
                    $_POST['role']
                );
                
                $plan_id = $_POST['plan_id'];
                $subscriptionModel->create($user_id, $plan_id);
                
                $plan = $planModel->find($plan_id);
                $smsCreditModel->create($user_id, $plan['sms_credit_bonus']);
                $smsCreditHistoryModel->addCreditHistory($user_id, 'credit', $plan['sms_credit_bonus']);

                $_SESSION['flash_message'] = 'User created successfully!';
            } else {
                $_SESSION['flash_message'] = 'Failed to create user. Name, email, password, phone number, and plan are required.';
            }
            redirect('/admin/users.php');
            break;

        case 'update_user':
            $user_id = $_POST['user_id'] ?? null;
            if ($user_id && !empty($_POST['name']) && !empty($_POST['email'])) {
                $data = [
                    'name' => $_POST['name'],
                    'email' => $_POST['email'],
                    'role' => $_POST['role'],
                    'password' => $_POST['password'] ?? '',
                    'phone_number' => $_POST['phone_number'] ?? null,
                    'district' => $_POST['district'] ?? null,
                    'upazila' => $_POST['upazila'] ?? null
                ];
                $userModel->update($user_id, $data);
                $_SESSION['flash_message'] = 'User updated successfully!';
            } else {
                $_SESSION['flash_message'] = 'Failed to update user. Invalid data provided.';
            }
            redirect('/admin/users.php');
            break;

        case 'delete_user':
            $user_id = $_POST['user_id'] ?? null;
            if ($user_id) {
                // Add a check to prevent admin from deleting themselves
                if (isset($_SESSION['admin_id']) && $_SESSION['admin_id'] == $user_id) {
                    $_SESSION['flash_message'] = 'You cannot delete your own account.';
                } else {
                    if ($userModel->delete($user_id)) {
                        $_SESSION['flash_message'] = 'User deleted successfully!';
                    } else {
                        $_SESSION['flash_message'] = 'Failed to delete user.';
                    }
                }
            } else {
                $_SESSION['flash_message'] = 'Failed to delete user. User ID was missing.';
            }
            header('Location: ' . APP_URL . '/admin/users.php');
            die();
            break;

        case 'add_website':
            $user_id = $_POST['user_id'] ?? null;
            $domain = $_POST['domain'] ?? null;
            if ($user_id && $domain) {
                $websiteModel->create($user_id, $domain);
                $_SESSION['flash_message'] = 'Website added successfully!';
            } else {
                $_SESSION['flash_message'] = 'Failed to add website. User and domain are required.';
            }
            redirect('/admin/users.php');
            break;

        case 'update_subscription':
            $user_id = $_POST['user_id'] ?? null;
            $new_plan_id = $_POST['new_plan_id'] ?? null;
            if ($user_id && $new_plan_id) {
                $subscriptionModel->updateUserPlan($user_id, $new_plan_id);
                $_SESSION['flash_message'] = 'User subscription updated successfully!';
            } else {
                $_SESSION['flash_message'] = 'Failed to update subscription. Invalid data provided.';
            }
            redirect('/admin/edit_user.php?id=' . $user_id);
            break;

        case 'set_credits':
            $user_id = $_POST['user_id'] ?? null;
            $new_balance = $_POST['new_balance'] ?? null;
            if ($user_id && isset($new_balance)) {
                $old_balance = $smsCreditModel->getBalance($user_id);
                $smsCreditModel->setBalance($user_id, $new_balance);
                $diff = $new_balance - $old_balance;
                if ($diff > 0) {
                    $smsCreditHistoryModel->addCreditHistory($user_id, 'credit', $diff);
                }
                $_SESSION['flash_message'] = 'User credits updated successfully!';
            } else {
                $_SESSION['flash_message'] = 'Failed to set credits. Invalid data provided.';
            }
            redirect('/admin/edit_user.php?id=' . $user_id);
            break;
        
        default:
            $_SESSION['flash_message'] = 'Unknown action.';
            redirect('/admin/index.php');
            break;
    }
} else {
    // Redirect if accessed directly without POST method
    redirect('/admin/index.php');
}
