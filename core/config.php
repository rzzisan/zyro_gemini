<?php
define('ROOT_PATH', dirname(__DIR__));

session_start();

// Load environment variables from .env file
require_once ROOT_PATH . '/vendor/autoload.php';

if (file_exists(ROOT_PATH . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(ROOT_PATH);
    $dotenv->safeLoad();
}

// Database connection details
define('DB_HOST', $_ENV['DB_HOST'] ?? getenv('DB_HOST'));
define('DB_NAME', $_ENV['DB_NAME'] ?? getenv('DB_NAME'));
define('DB_USER', $_ENV['DB_USER'] ?? getenv('DB_USER'));
define('DB_PASS', $_ENV['DB_PASS'] ?? getenv('DB_PASS'));

require_once ROOT_PATH . '/core/remember_me_guard.php';
check_remember_me();

// Application settings
define('APP_URL', $_ENV['APP_URL'] ?? getenv('APP_URL'));
define('APP_NAME', $_ENV['APP_NAME'] ?? getenv('APP_NAME'));

// Third-party API keys
define('PACKZY_API_URL', $_ENV['PACKZY_API_URL'] ?? getenv('PACKZY_API_URL'));
define('FRAUD_CHECKER_CACHE_EXPIRATION', $_ENV['FRAUD_CHECKER_CACHE_EXPIRATION'] ?? getenv('FRAUD_CHECKER_CACHE_EXPIRATION')); // in days
define('SMS_GATEWAY_URL', $_ENV['SMS_GATEWAY_URL'] ?? getenv('SMS_GATEWAY_URL'));
define('SMS_GATEWAY_API_KEY', $_ENV['SMS_GATEWAY_API_KEY'] ?? getenv('SMS_GATEWAY_API_KEY'));
define('SMS_GATEWAY_SECRET_KEY', $_ENV['SMS_GATEWAY_SECRET_KEY'] ?? getenv('SMS_GATEWAY_SECRET_KEY'));
define('SMS_GATEWAY_SENDER_ID', $_ENV['SMS_GATEWAY_SENDER_ID'] ?? getenv('SMS_GATEWAY_SENDER_ID'));

// SMTP Configuration
define('SMTP_HOST', $_ENV['SMTP_HOST'] ?? getenv('SMTP_HOST'));
define('SMTP_USER', $_ENV['SMTP_USER'] ?? getenv('SMTP_USER'));
define('SMTP_PASS', $_ENV['SMTP_PASS'] ?? getenv('SMTP_PASS'));
define('SMTP_PORT', $_ENV['SMTP_PORT'] ?? getenv('SMTP_PORT'));
define('SMTP_SECURE', $_ENV['SMTP_SECURE'] ?? getenv('SMTP_SECURE'));
define('FROM_EMAIL', $_ENV['FROM_EMAIL'] ?? getenv('FROM_EMAIL'));
define('FROM_NAME', $_ENV['FROM_NAME'] ?? getenv('FROM_NAME'));