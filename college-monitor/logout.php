<?php
// logout.php
// Admin logout handler for College Student Activity Monitoring System

session_start();

// Log the logout action
if (isset($_SESSION['admin_username'])) {
    error_log("Admin logout: " . $_SESSION['admin_username'] . " from IP: " . $_SERVER['REMOTE_ADDR']);
}

// Destroy all session data
session_unset();
session_destroy();

// Start a new session for the success message
session_start();
$_SESSION['success_message'] = "You have been successfully logged out.";

// Redirect to login page
header("Location: login.php");
exit;
?>
