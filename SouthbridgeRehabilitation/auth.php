<?php
// Southbridge Rehabilitation - Authentication / Role Protection
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/db.php';

$create = "CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'Maintenance',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY(id),
    UNIQUE KEY unique_email(email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$conn->query($create);

$checkRole = $conn->query("SHOW COLUMNS FROM users LIKE 'role'");
if ($checkRole && $checkRole->num_rows === 0) {
    $conn->query("ALTER TABLE users ADD COLUMN role VARCHAR(50) NOT NULL DEFAULT 'Work Order Inserty' AFTER password_hash");
}

function current_user() {
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'name' => $_SESSION['full_name'] ?? '',
        'email' => $_SESSION['email'] ?? '',
        'role' => $_SESSION['role'] ?? ''
    ];
}

function is_logged_in() {
    return !empty($_SESSION['user_id']);
}

function permission_map($role) {
    $all = ['dashboard','work_order_create','work_order_log','loto','pest_control','user_management','email_settings'];
    $map = [
        'Admin' => $all,
        'Maintenance Supervisor' => ['dashboard','work_order_create','work_order_log','loto','pest_control'],
        'Maintenance' => ['dashboard','work_order_create','work_order_log'],
        'Pest Control' => ['dashboard','pest_control'],
		'Head Nurse' => ['dashboard','work_order_create'],
		'Nurse' => ['dashboard','work_order_create'],
        'Administration' => $all,
		'Receptionist' => ['dashboard','work_order_create','work_order_log'],
		'Work Order Inserty' => ['dashboard','work_order_create']
    ];
    return $map[$role] ?? [];
}

function can_access($permission) {
    return is_logged_in() && in_array($permission, permission_map($_SESSION['role'] ?? ''), true);
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: signin.php');
        exit;
    }
}

function require_permission($permission) {
    require_login();
    if (!can_access($permission)) {
        http_response_code(403);
        echo '<!doctype html><html><head><meta charset="utf-8"><title>Access Denied</title>
        <style>body{font-family:Arial;background:#eef3f7;text-align:center;padding:70px}.box{background:#fff;max-width:600px;margin:auto;padding:35px;border-radius:10px;box-shadow:0 4px 18px #0002}a{display:inline-block;margin-top:20px;padding:11px 18px;background:#165a8a;color:#fff;text-decoration:none;border-radius:5px}</style></head>
        <body><div class="box"><h1>Access Denied</h1><p>Your account does not have permission to open this section.</p><a href="SouthbridgeRehab.php">Return to Home</a></div></body></html>';
        exit;
    }
}
