<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'onechef_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site configuration
define('SITE_URL', 'http://localhost:8000');
define('SITE_NAME', 'One Chef');
define('SITE_PHONE', '+380737001987');
define('SITE_ADDRESS', 'проспект Науки, 57а, Дніпро');

// Business settings
define('PICKUP_DISCOUNT', 10); // 10% discount for pickup

// Paths
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/src');
define('PUBLIC_PATH', ROOT_PATH . '/public');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Europe/Kiev');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>