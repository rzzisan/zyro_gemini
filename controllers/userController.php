<?php

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/functions.php';
require_once __DIR__ . '/../models/User.php';

class UserController
{
    public static function handleProfileUpdate($userId, $name, $email)
    {
        $db = getDb();
        $userModel = new User($db);

        if (!$userModel->updateProfile($userId, $name, $email)) {
            set_message('Email already in use by another account.', 'danger');
        } else {
            set_message('Profile updated successfully!', 'success');
            $_SESSION['user_name'] = $name;
        }
        redirect('/views/dashboard/profile.php');
    }

    public static function handleChangePassword($userId, $currentPassword, $newPassword, $confirmPassword)
    {
        if ($newPassword !== $confirmPassword) {
            set_message('New passwords do not match.', 'danger');
            redirect('/views/dashboard/profile.php');
            return;
        }

        $db = getDb();
        $userModel = new User($db);

        $user = $userModel->find($userId);

        if ($user && password_verify($currentPassword, $user['password'])) {
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            if ($userModel->updatePassword($userId, $hashedPassword)) {
                set_message('Password changed successfully!', 'success');
            } else {
                set_message('Failed to change password. Please try again.', 'danger');
            }
        } else {
            set_message('Incorrect current password.', 'danger');
        }
        redirect('/views/dashboard/profile.php');
    }
}