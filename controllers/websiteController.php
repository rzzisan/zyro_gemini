<?php
session_start();
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/functions.php';
require_once '../models/Website.php';
require_once '../models/ApiToken.php';

ensure_logged_in();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $db = getDb();
    $user_id = get_user_id();

    switch ($_POST['action']) {
        case 'add_website':
            $domain = $_POST['domain'] ?? null;

            if (!$domain || !filter_var($domain, FILTER_VALIDATE_URL)) {
                set_message('Please enter a valid domain URL.', 'danger');
                redirect('/views/dashboard/websites.php');
            }

            $websiteModel = new Website($db);
            $apiTokenModel = new ApiToken($db);

            $website_id = $websiteModel->create($user_id, $domain);

            if ($website_id) {
                $apiTokenModel->generateKey($user_id, $website_id);
                set_message('Website added successfully!', 'success');
            } else {
                set_message('Failed to add website.', 'danger');
            }

            redirect('/views/dashboard/websites.php');
            break;

        case 'delete_website':
            $website_id = $_POST['website_id'] ?? null;

            if (!$website_id) {
                set_message('Invalid request.', 'danger');
                redirect('/views/dashboard/websites.php');
            }

            (new ApiToken($db))->deleteByWebsite($website_id);
            (new Website($db))->delete($website_id, $user_id);

            set_message('Website deleted successfully.', 'success');
            redirect('/views/dashboard/websites.php');
            break;

        default:
            redirect('/views/dashboard/websites.php');
            break;
    }
} else {
    redirect('/views/dashboard/websites.php');
}