<?php
// Database configuration
define('DB_HOST', 'reanim02.mysql.tools');
define('DB_NAME', 'reanim02_onechef');
define('DB_USER', 'reanim02_onechef');
define('DB_PASS', '9nj;MSe6~2');

// Site configuration
define('SITE_URL', 'https://onechef.reanimator.dp.ua');
define('SITE_NAME', 'ONE CHEF');
define('SITE_PHONE', '+380737001987');
define('SITE_ADDRESS', 'проспект Науки, 57а, Дніпро');
define('GMAPS_API_KEY', 'AIzaSyA4JfX17OUSfXCj58BuLMD2X8g1kQ55gW4');


// Working hours (ДОБАВЛЯЕМ ЭТО)
define('WORKING_DAYS', 'Щоденно');
define('WORKING_HOURS_START', '10:31');
define('WORKING_HOURS_END', '22:01');

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
date_default_timezone_set('Europe/Kyiv');

// Путь для загрузки файлов
define('UPLOAD_DIR', __DIR__ . '/../public/uploads/');

?>