<?php
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/functions.php';

class UserController {
    public static function getUserProfile($userId) {
        // In a real application, this would fetch user data from the database
        return [
            'id' => $userId,
            'username' => 'Test User',
            'email' => 'user@example.com',
            'plan' => 'Premium',
            'websites' => 5
        ];
    }

    public static function updateUserProfile($userId, $data) {
        // In a real application, this would update user data in the database
        error_log("User $userId profile updated with data: " . print_r($data, true));
        return true;
    }
}
