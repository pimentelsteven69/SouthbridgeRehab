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
if ($col && $col->num_rows === 0) $conn->query("ALTER TABLE users ADD COLUMN role VARCHAR(50) NOT NULL DEFAULT 'Maintenance' AFTER password_hash");

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['full_name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $pw = $_POST['password'] ?? '';
    $cpw = $_POST['confirm_password'] ?? '';

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $error = 'Enter your name and a valid email.';
    elseif (strlen($pw) < 8) $error = 'Password must be at least 8 characters.';
    elseif ($pw !== $cpw) $error = 'Passwords do not match.';
    else {
        $count = $conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'];
        $check = $conn->prepare("SELECT id FROM users WHERE email=?");
        $check->bind_param('s',$email); $check->execute(); $check->store_result();
        if ($check->num_rows) $error = 'An account with that email already exists.';
        else {
            // The very first account becomes Admin. Later accounts default to Maintenance.
            $role = ((int)$count === 0) ? 'Admin' : 'Maintenance';
            $hash = password_hash($pw, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users(full_name,email,password_hash,role) VALUES(?,?,?,?)");
            $stmt->bind_param('ssss',$name,$email,$hash,$role);
            if ($stmt->execute()) {
                header('Location: signin.php');
                exit;
            }
            $error = 'Unable to create account.';
            $stmt->close();
        }
        $check->close();
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Southbridge Rehabilitation - Sign Up</title>
<style>body{font-family:Arial;background:#eef3f7;min-height:100vh;display:grid;place-items:center;margin:0}.card{background:#fff;width:min(430px,90%);padding:32px;border-radius:10px;box-shadow:0 5px 25px #0002}h1{text-align:center;font-size:24px}label{display:block;font-weight:bold;margin:14px 0 5px}input{width:100%;padding:11px;box-sizing:border-box;border:1px solid #aaa;border-radius:4px}button{width:100%;margin-top:20px;padding:12px;background:#165a8a;color:#fff;border:0;border-radius:4px;font-weight:bold}.error{background:#fee2e2;color:#991b1b;padding:10px;margin:12px 0}.link{text-align:center;margin-top:18px}</style></head>
<body><div class="card"><h1>Southbridge Rehabilitation<br>&amp; Health Care Center</h1><p style="text-align:center">Create Facility Management Account</p>
<?php if($error): ?><div class="error"><?=htmlspecialchars($error)?></div><?php endif; ?>
<form method="post"><label>Full Name</label><input name="full_name" required>
<label>Email</label><input name="email" type="email" required>
<label>Password</label><input name="password" type="password" minlength="8" required>
<label>Confirm Password</label><input name="confirm_password" type="password" minlength="8" required>
<button type="submit">Create Account</button></form>
<div class="link">Already have an account? <a href="signin.php">Sign In</a></div></div></body></html>