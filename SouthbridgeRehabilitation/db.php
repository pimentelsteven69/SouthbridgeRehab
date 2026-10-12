<?php
// Southbridge Rehabilitation - Database Connection
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'southbridge_loto';

$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) {
    die("MySQL connection failed: " . htmlspecialchars($conn->connect_error));
}
$conn->set_charset('utf8mb4');
if (!$conn->query("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
    die("Could not create database: " . htmlspecialchars($conn->error));
}
if (!$conn->select_db($dbname)) {
    die("Could not select database: " . htmlspecialchars($conn->error));
}

// Automatically create the password reset table if it does not exist.
// This prevents the Forgot Password page from failing on a fresh XAMPP install.
$passwordResetTable = "CREATE TABLE IF NOT EXISTS password_resets (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    token_hash CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY unique_token_hash (token_hash),
    KEY idx_user_id (user_id),
    CONSTRAINT fk_password_reset_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// Only create it when the users table exists. The normal site setup creates users first.
$usersExists = $conn->query("SHOW TABLES LIKE 'users'");
if ($usersExists && $usersExists->num_rows > 0) {
    if (!$conn->query($passwordResetTable)) {
        // Do not break the whole site if an older database has a schema mismatch.
        // The Forgot Password page will show the database error when it needs the table.
    }
}
