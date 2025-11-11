<?php
require_once __DIR__ . '/core/config.php';
require_once __DIR__ . '/core/functions.php';

if (is_logged_in()) {
    redirect('/views/dashboard/index.php');
} else {
    redirect('/views/auth/login.php');
}
