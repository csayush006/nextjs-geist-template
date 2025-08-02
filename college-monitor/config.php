<?php
// config.php
// Configuration file for College Student Activity Monitoring System

// MySQL database configuration (use default XAMPP credentials)
define('DB_HOST', 'localhost');
define('DB_NAME', 'Ayush17514'); // Use 'test' or create a database for testing
define('DB_USER', 'root'); // Default XAMPP username
define('DB_PASS', ''); // Default XAMPP password (empty)

// API Credentials (placeholders for testing)
define('GITHUB_TOKEN', 'test_github_token');
define('LINKEDIN_CREDENTIALS', 'test_linkedin_credentials');

// Application settings
define('APP_NAME', 'College Monitor');
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds

// Error reporting for development (enabled for testing)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone setting
date_default_timezone_set('America/New_York');

// Security settings (basic for testing)
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS

// Rate limiting settings (for API calls)
define('API_RATE_LIMIT', 60); // requests per hour
define('API_CACHE_TIME', 300); // 5 minutes cache

?>