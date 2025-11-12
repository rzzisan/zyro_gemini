<?php
define('ROOT_PATH', dirname(__DIR__));

session_start();

// Database connection details
define('DB_HOST', 'localhost');
define('DB_NAME', 'zyrotechbd_db');
define('DB_USER', 'zyrotechbd_db_user');
define('DB_PASS', 'Zareen@54221');

require_once ROOT_PATH . '/core/remember_me_guard.php';
check_remember_me();

// Application settings
define('APP_URL', 'https://saas.zyrotechbd.com/');
define('APP_NAME', 'ZyroSaaS');

// Third-party API keys
define('PACKZY_API_KEY', '');
define('PACKZY_API_URL', 'https://portal.packzy.com/api/v1/fraud_check/');
define('SMS_GATEWAY_URL', 'http://118.67.213.114:3775/sendtext');
define('SMS_GATEWAY_API_KEY', 'f59ff32f5d568c53');
define('SMS_GATEWAY_SECRET_KEY', '1226beec');
define('SMS_GATEWAY_SENDER_ID', 'Non masking_centurylinknetworkি');
