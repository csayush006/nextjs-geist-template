<?php
// config.php
// Configuration file for College Student Activity Monitoring System

// MySQL database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'college_monitor');
define('DB_USER', 'root');
define('DB_PASS', '');

// API Credentials (placeholders; update the logic later)
define('GITHUB_TOKEN', 'your_github_token_here');
define('LINKEDIN_CREDENTIALS', 'your_linkedin_credentials_here');
// LeetCode will be handled via scraping so no API token required

// Application settings
define('APP_NAME', 'College Monitor');
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds

// Error reporting for development (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone setting
date_default_timezone_set('America/New_York');

// Security settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS

// Rate limiting settings (for API calls)
define('API_RATE_LIMIT', 60); // requests per hour
define('API_CACHE_TIME', 300); // 5 minutes cache

?>
