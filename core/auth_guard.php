<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Website.php';
require_once __DIR__ . '/../models/ApiToken.php';

/**
 * Sets the HTTP response code, encodes a JSON error message, and terminates the script.
 *
 * @param string $message The error message.
 * @param int    $code    The HTTP status code.
 */
function return_json_error($message, $code = 403)
{
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode(['error' => $message]);
    exit;
}

/**
 * Authenticates an API request from the WordPress plugin.
 *
 * @return int The authenticated user's ID.
 */
function authenticate_plugin_request()
{
    $db = getDb();

    // Step 1: Get Token from Header
    if (!isset($_SERVER['HTTP_AUTHORIZATION']) || substr($_SERVER['HTTP_AUTHORIZATION'], 0, 7) !== 'Bearer ') {
        return_json_error('Authorization header missing.');
    }
    $token = str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']);

    // Step 2: Find Token in Database
    $apiTokenModel = new ApiToken($db);
    $token_data = $apiTokenModel->findByToken($token);

    if (!$token_data) {
        return_json_error('Invalid API Key.');
    }

    $user_id = $token_data['user_id'];
    $website_id = $token_data['website_id'];

    // Step 3: Get Request Origin from User-Agent
    if (!isset($_SERVER['HTTP_USER_AGENT'])) {
        return_json_error('Invalid User-Agent.');
    }

    if (!preg_match('/WordPress\/\S+; (https?:\/\/\S+)/', $_SERVER['HTTP_USER_AGENT'], $matches)) {
        return_json_error('Invalid User-Agent.');
    }

    $request_host = parse_url($matches[1], PHP_URL_HOST);

    // Step 4: Match Token Domain with Request Domain
    $websiteModel = new Website($db);
    $website = $websiteModel->find($website_id);

    if (!$website) {
        return_json_error('Associated website not found.');
    }
    
    $registered_host = parse_url($website['domain'], PHP_URL_HOST);

    // Step 5: Validate
    if ($request_host !== $registered_host) {
        return_json_error('API Key not authorized for this domain.');
    }

    // Step 6: Success
    $apiTokenModel->updateLastUsed($token_data['id']);

    return $user_id;
}
