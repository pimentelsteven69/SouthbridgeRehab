<?php
require_once 'auth.php';
require_login();

$error=''; $success='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $current=$_POST['current_password'] ?? '';
    $new=$_POST['new_password'] ?? '';
    $confirm=$_POST['confirm_password'] ?? '';

    if (strlen($new) < 8) $error='New password must be at least 8 characters.';
    elseif ($new !== $confirm) $error='The new passwords do not match.';
    else {
        $stmt=$conn->prepare("SELECT password_hash FROM users WHERE id=? LIMIT 1");
        $id=(int)$_SESSION['user_id'];
        $stmt->bind_param('i',$id); $stmt->execute();
        $user=$stmt->get_result()->fetch_assoc(); $stmt->close();

        if (!$user || !password_verify($current,$user['password_hash'])) {
            $error='Your current password is incorrect.';
        } else {
            $hash=password_hash($new,PASSWORD_DEFAULT);
            $stmt=$conn->prepare("UPDATE users SET password_hash=? WHERE id=?");
            $stmt->bind_param('si',$hash,$id); $stmt->execute(); $stmt->close();
            $success='Your password has been changed successfully.';
        }
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Change Password - Southbridge</title><style>
body{font-family:Arial;background:#eef3f7;min-height:100vh;display:grid;place-items:center;margin:0}
.card{background:#fff;width:min(480px,92%);padding:32px;border-radius:10px;box-shadow:0 5px 25px #0002}
h1{text-align:center;color:#165a8a;font-size:23px}p{line-height:1.5}
label{display:block;font-weight:bold;margin:14px 0 5px}
input{width:100%;padding:11px;box-sizing:border-box;border:1px solid #aaa;border-radius:4px}
button{width:100%;margin-top:20px;padding:12px;background:#165a8a;color:#fff;border:0;border-radius:4px;font-weight:bold;cursor:pointer}
.alert{padding:10px;margin:12px 0;border-radius:4px}.error{background:#fee2e2;color:#991b1b}.success{background:#dcfce7;color:#166534}
.link{text-align:center;margin-top:18px}.link a{color:#165a8a}
</style></head>
<body><div class="card"><h1>Southbridge Rehabilitation</h1><h2>Change Password</h2>
<?php if($error): ?><div class="alert error"><?=htmlspecialchars($error)?></div><?php endif; ?>
<?php if($success): ?><div class="alert success"><?=htmlspecialchars($success)?></div><?php endif; ?>
<form method="post" autocomplete="off">
<label>Current Password</label><input type="password" name="current_password" required>
<label>New Password</label><input type="password" name="new_password" minlength="8" required>
<label>Confirm New Password</label><input type="password" name="confirm_password" minlength="8" required>
<button type="submit">Change Password</button></form>
<div class="link"><a href="SouthbridgeRehab.php">Back to Home</a> &nbsp;|&nbsp; <a href="logout.php">Log Out</a></div>
</div></body></html>