<?php
session_start();
require_once 'db.php';

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
$col = $conn->query("SHOW COLUMNS FROM users LIKE 'role'");
if ($col && $col->num_rows === 0) {
    $conn->query("ALTER TABLE users ADD COLUMN role VARCHAR(50) NOT NULL DEFAULT 'Maintenance' AFTER password_hash");
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT id, full_name, email, password_hash, role FROM users WHERE email=? LIMIT 1");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'] ?: 'Maintenance';
        header('Location: SouthbridgeRehab.php');
        exit;
    }
    $error = 'Incorrect email or password.';
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Southbridge Rehabilitation - Sign In</title>
<style>body{font-family:Arial;background:#eef3f7;min-height:100vh;display:grid;place-items:center;margin:0}.card{background:#fff;width:min(430px,90%);padding:32px;border-radius:10px;box-shadow:0 5px 25px #0002}h1{text-align:center;font-size:24px}label{display:block;font-weight:bold;margin:14px 0 5px}input{width:100%;padding:11px;box-sizing:border-box;border:1px solid #aaa;border-radius:4px}button{width:100%;margin-top:20px;padding:12px;background:#165a8a;color:#fff;border:0;border-radius:4px;font-weight:bold}.error{background:#fee2e2;color:#991b1b;padding:10px;margin:12px 0}.link{text-align:center;margin-top:18px}</style></head>
<body><div class="card"><h1>Southbridge Rehabilitation<br>&amp; Health Care Center</h1><p style="text-align:center">Facility Management System</p>
<?php if($error): ?><div class="error"><?=htmlspecialchars($error)?></div><?php endif; ?>
<form method="post"><label>Email</label><input type="email" name="email" required>
<label>Password</label><input type="password" name="password" required>
<button type="submit">Sign In</button></form>
<div class="link"><a href="forgot_password.php">Forgot Password?</a><br><br><a href="signup.html">Create an account</a></div></div></body></html>