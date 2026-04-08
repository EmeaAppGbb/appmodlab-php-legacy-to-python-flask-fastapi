<?php
// Database Configuration - Hardcoded credentials (bad practice)
define('DB_HOST', 'localhost');
define('DB_USER', 'citypulse_user');
define('DB_PASS', 'citypulse123');
define('DB_NAME', 'citypulse_events');

// Application Settings
define('SITE_NAME', 'CityPulse Events');
define('SITE_URL', 'http://localhost:8080');
define('UPLOAD_DIR', 'uploads/');
define('MAX_UPLOAD_SIZE', 5242880); // 5MB

// PayPal Configuration (hardcoded, insecure)
define('PAYPAL_EMAIL', 'merchant@citypulse.com');
define('PAYPAL_SANDBOX', true);
define('PAYPAL_URL', PAYPAL_SANDBOX ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr');

// Session Configuration (insecure)
ini_set('session.cookie_httponly', 0);
ini_set('session.cookie_secure', 0);
session_start();

// Error Reporting (bad for production)
error_reporting(E_ALL);
ini_set('display_errors', '1');
?>
