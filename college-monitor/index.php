<?php
// index.php
// Entry point for College Student Activity Monitoring System

require_once 'config.php';

session_start();

// Check if admin is already logged in
if (isset($_SESSION['admin_id'])) {
    // Redirect to dashboard if already logged in
    header("Location: dashboard.php");
    exit;
} else {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit;
}
?>
