<?php
require_once __DIR__ . '/../../core/auth_guard.php';

// Authenticate the request
$user_id = authenticate_plugin_request();

// If authentication is successful, the user ID is returned.
// You can now proceed with API logic, using $user_id to identify the user.

echo json_encode(['success' => true, 'message' => 'Authenticated', 'user_id' => $user_id]);