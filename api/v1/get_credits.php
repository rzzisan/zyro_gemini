<?php
header("Content-Type: application/json");

// 1. Include dependencies
require_once __DIR__ . '/../../core/config.php';
require_once __DIR__ . '/../../core/auth_guard.php';
require_once __DIR__ . '/../../models/SmsCredit.php';
require_once __DIR__ . '/../../core/functions.php'; // getDb() is in functions.php

// 2. Authenticate the request
// This function will handle errors and exit if authentication fails.
$user_id = authenticate_plugin_request();

// 3. Get DB connection
$db = getDb();

// 4. Get the user's SMS credit balance
$balance = (new SmsCredit($db))->getBalance($user_id);

// 5. Return the balance as a JSON response
echo json_encode(['success' => true, 'credits' => $balance]);

exit;
