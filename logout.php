<?php
session_start();
require_once __DIR__ . '/core/config.php';
require_once __DIR__ . '/core/functions.php';

session_destroy();
redirect('/views/auth/login.php');
?>
