<?php
// Generate a hashed password for "admin123"
$password = "admin123";
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

echo "Hashed Password: " . $hashedPassword;
?>