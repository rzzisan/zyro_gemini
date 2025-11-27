<?php
define('ROOT_PATH', dirname(__DIR__));

session_start();

// Load environment variables from .env file
if (file_exists(ROOT_PATH . '/.env')) {
    $lines = file(ROOT_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}

// Database connection details
define('DB_HOST', getenv('DB_HOST'));
define('DB_NAME', getenv('DB_NAME'));
define('DB_USER', getenv('DB_USER'));
define('DB_PASS', getenv('DB_PASS'));

require_once ROOT_PATH . '/core/remember_me_guard.php';
check_remember_me();

// Application settings
define('APP_URL', getenv('APP_URL'));
define('APP_NAME', getenv('APP_NAME'));

// Third-party API keys
define('PACKZY_API_URL', getenv('PACKZY_API_URL'));
define('FRAUD_CHECKER_CACHE_EXPIRATION', getenv('FRAUD_CHECKER_CACHE_EXPIRATION')); // in days
define('SMS_GATEWAY_URL', getenv('SMS_GATEWAY_URL'));
define('SMS_GATEWAY_API_KEY', getenv('SMS_GATEWAY_API_KEY'));
define('SMS_GATEWAY_SECRET_KEY', getenv('SMS_GATEWAY_SECRET_KEY'));
define('SMS_GATEWAY_SENDER_ID', getenv('SMS_GATEWAY_SENDER_ID'));

// SMTP Configuration
define('SMTP_HOST', getenv('SMTP_HOST'));
define('SMTP_USER', getenv('SMTP_USER'));
define('SMTP_PASS', getenv('SMTP_PASS'));
define('SMTP_PORT', getenv('SMTP_PORT'));
define('SMTP_SECURE', getenv('SMTP_SECURE'));
define('FROM_EMAIL', getenv('FROM_EMAIL'));
define('FROM_NAME', getenv('FROM_NAME'));