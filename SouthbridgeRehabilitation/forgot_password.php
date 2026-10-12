<?php
require_once 'db.php';
require_once 'smtp_mail.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

$success=''; $error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $email=strtolower(trim($_POST['email'] ?? ''));
    if (!filter_var($email,FILTER_VALIDATE_EMAIL)) {
        $error='Please enter a valid email address.';
    } else {
        $stmt=$conn->prepare("SELECT id,full_name,email FROM users WHERE email=? LIMIT 1");
        $stmt->bind_param('s',$email); $stmt->execute();
        $user=$stmt->get_result()->fetch_assoc(); $stmt->close();

        // Always show the same success message to avoid revealing which emails are registered.
        $success='If an account exists for that email, a password reset link has been created.';

        if ($user) {
            $conn->query("DELETE FROM password_resets WHERE expires_at < NOW() OR user_id=".(int)$user['id']);
            $token=bin2hex(random_bytes(32));
            $hash=hash('sha256',$token);
            $expires=date('Y-m-d H:i:s',time()+3600);
            $stmt=$conn->prepare("INSERT INTO password_resets (user_id,token_hash,expires_at) VALUES (?,?,?)");
            $uid=(int)$user['id']; $stmt->bind_param('iss',$uid,$hash,$expires); $stmt->execute(); $stmt->close();

            $base=(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off' ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']),'/\\');
            $link=$base.'/reset_password.php?token='.urlencode($token);

            $subject='Southbridge Rehabilitation Password Reset';
            $body="Hello ".$user['full_name'].",\n\nUse the link below to reset your Southbridge password. This link expires in 1 hour:\n\n".$link."\n\nIf you did not request this password reset, you can safely ignore this email.";
            [$sent, $mailError] = smtpSend($user['email'], $user['full_name'], $subject, $body);
            if (!$sent) {
                $error = 'We could not send the reset email. Please contact an administrator. (' . $mailError . ')';
                $success = '';
            } else {
                $success='If an account exists for that email, a password reset link has been sent to that email address.';
            }
        }
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Forgot Password - Southbridge</title><style>
body{font-family:Arial;background:#eef3f7;min-height:100vh;display:grid;place-items:center;margin:0}
.card{background:#fff;width:min(480px,92%);padding:32px;border-radius:10px;box-shadow:0 5px 25px #0002}
h1{text-align:center;color:#165a8a;font-size:23px}p{line-height:1.5}
label{display:block;font-weight:bold;margin:14px 0 5px}
input{width:100%;padding:11px;box-sizing:border-box;border:1px solid #aaa;border-radius:4px}
button{width:100%;margin-top:20px;padding:12px;background:#165a8a;color:#fff;border:0;border-radius:4px;font-weight:bold;cursor:pointer}
.alert{padding:10px;margin:12px 0;border-radius:4px}.error{background:#fee2e2;color:#991b1b}.success{background:#dcfce7;color:#166534}
.link{text-align:center;margin-top:18px}.link a{color:#165a8a}
</style></head>
<body><div class="card"><h1>Southbridge Rehabilitation</h1><h2>Forgot Password</h2>
<p>Enter your account email address. If the account exists, a password reset link will be emailed to you. The link expires after 1 hour.</p>
<?php if($error): ?><div class="alert error"><?=htmlspecialchars($error)?></div><?php endif; ?>
<?php if($success): ?><div class="alert success"><?=htmlspecialchars($success)?></div><?php endif; ?>
<form method="post"><label>Email Address</label><input type="email" name="email" required>
<button type="submit">Email Reset Link</button></form>
<div class="link"><a href="signin.php">Back to Sign In</a></div>
</div></body></html>