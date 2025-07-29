<?php
// check_session.php
// Session validation endpoint for AJAX requests

require_once 'config.php';

session_start();

// Set JSON content type
header('Content-Type: application/json');

// Check if admin is logged in and session is valid
$isActive = false;

if (isset($_SESSION['admin_id']) && isset($_SESSION['login_time'])) {
    // Check session timeout
    if ((time() - $_SESSION['login_time']) <= SESSION_TIMEOUT) {
        $isActive = true;
    }
}

// Return JSON response
echo json_encode([
    'active' => $isActive,
    'timestamp' => time()
]);
?>
