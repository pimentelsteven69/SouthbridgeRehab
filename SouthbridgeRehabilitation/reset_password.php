<?php
require_once 'db.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

$error=''; $success='';
$token=$_GET['token'] ?? $_POST['token'] ?? '';
$validUser=null;

if (strlen($token)===64 && ctype_xdigit($token)) {
    $hash=hash('sha256',$token);
    $stmt=$conn->prepare("SELECT pr.id, pr.user_id, u.email FROM password_resets pr JOIN users u ON u.id=pr.user_id WHERE pr.token_hash=? AND pr.expires_at>NOW() LIMIT 1");
    $stmt->bind_param('s',$hash); $stmt->execute();
    $validUser=$stmt->get_result()->fetch_assoc(); $stmt->close();
}
if ($_SERVER['REQUEST_METHOD']==='POST' && $validUser) {
    $new=$_POST['new_password'] ?? '';
    $confirm=$_POST['confirm_password'] ?? '';
    if (strlen($new)<8) $error='Password must be at least 8 characters.';
    elseif ($new!==$confirm) $error='The passwords do not match.';
    else {
        $newHash=password_hash($new,PASSWORD_DEFAULT);
        $uid=(int)$validUser['user_id'];
        $stmt=$conn->prepare("UPDATE users SET password_hash=? WHERE id=?");
        $stmt->bind_param('si',$newHash,$uid); $stmt->execute(); $stmt->close();
        $stmt=$conn->prepare("DELETE FROM password_resets WHERE user_id=?");
        $stmt->bind_param('i',$uid); $stmt->execute(); $stmt->close();
        $success='Your password has been reset successfully. You can now sign in.';
        $validUser=null;
    }
} elseif ($_SERVER['REQUEST_METHOD']==='POST' && !$validUser) {
    $error='This reset link is invalid or has expired. Please request a new one.';
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Reset Password - Southbridge</title><style>
body{font-family:Arial;background:#eef3f7;min-height:100vh;display:grid;place-items:center;margin:0}
.card{background:#fff;width:min(480px,92%);padding:32px;border-radius:10px;box-shadow:0 5px 25px #0002}
h1{text-align:center;color:#165a8a;font-size:23px}p{line-height:1.5}
label{display:block;font-weight:bold;margin:14px 0 5px}
input{width:100%;padding:11px;box-sizing:border-box;border:1px solid #aaa;border-radius:4px}
button{width:100%;margin-top:20px;padding:12px;background:#165a8a;color:#fff;border:0;border-radius:4px;font-weight:bold;cursor:pointer}
.alert{padding:10px;margin:12px 0;border-radius:4px}.error{background:#fee2e2;color:#991b1b}.success{background:#dcfce7;color:#166534}
.link{text-align:center;margin-top:18px}.link a{color:#165a8a}
</style></head>
<body><div class="card"><h1>Southbridge Rehabilitation</h1><h2>Reset Password</h2>
<?php if($error): ?><div class="alert error"><?=htmlspecialchars($error)?></div><?php endif; ?>
<?php if($success): ?><div class="alert success"><?=htmlspecialchars($success)?></div><div class="link"><a href="signin.php">Go to Sign In</a></div>
<?php elseif($validUser): ?>
<form method="post"><input type="hidden" name="token" value="<?=htmlspecialchars($token)?>">
<label>New Password</label><input type="password" name="new_password" minlength="8" required>
<label>Confirm New Password</label><input type="password" name="confirm_password" minlength="8" required>
<button type="submit">Reset Password</button></form>
<?php else: ?><div class="link"><a href="forgot_password.php">Request a New Reset Link</a></div><?php endif; ?>
</div></body></html>