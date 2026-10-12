<?php
require_once 'auth.php';
require_permission('email_settings');

$msg=''; $error='';
$conn->query("CREATE TABLE IF NOT EXISTS email_settings (
 id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
 provider VARCHAR(30) NOT NULL DEFAULT 'gmail',
 host VARCHAR(255) NOT NULL DEFAULT '',
 port INT NOT NULL DEFAULT 587,
 encryption VARCHAR(10) NOT NULL DEFAULT 'tls',
 username VARCHAR(255) NOT NULL DEFAULT '',
 password TEXT NOT NULL,
 from_email VARCHAR(255) NOT NULL DEFAULT '',
 from_name VARCHAR(255) NOT NULL DEFAULT 'Southbridge Rehabilitation',
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$providers=[
 'gmail'=>['Gmail / Google Workspace','smtp.gmail.com',587,'tls','Use a Google App Password.'],
 'outlook'=>['Outlook.com / Hotmail / Live','smtp-mail.outlook.com',587,'tls','Outlook.com SMTP requires OAuth2/Modern Authentication; this basic SMTP sender cannot authenticate it.'],
 'microsoft365'=>['Microsoft 365','smtp.office365.com',587,'tls','SMTP AUTH must be enabled for the mailbox/tenant.'],
 'icloud'=>['iCloud Mail','smtp.mail.me.com',587,'tls','Use an Apple app-specific password.']
];

$row=$conn->query("SELECT * FROM email_settings WHERE id=1 LIMIT 1")->fetch_assoc();
if(!$row) $row=['provider'=>'gmail','host'=>'smtp.gmail.com','port'=>587,'encryption'=>'tls','username'=>'','password'=>'','from_email'=>'','from_name'=>'Southbridge Rehabilitation'];

if($_SERVER['REQUEST_METHOD']==='POST'){
 $action=$_POST['action']??'';
 if($action==='save'){
   $provider=$_POST['provider']??'gmail';
   if(!isset($providers[$provider])) $error='Invalid email provider.';
   else {
     $host=trim($_POST['host']??$providers[$provider][1]);
     $port=(int)($_POST['port']??$providers[$provider][2]);
     $encryption=$_POST['encryption']??$providers[$provider][3];
     $username=trim($_POST['username']??'');
     $from_email=trim($_POST['from_email']??$username);
     $from_name=trim($_POST['from_name']??'Southbridge Rehabilitation');
     $password=$_POST['password']??'';
     if($password==='') $password=$row['password']??'';
     if(!filter_var($username,FILTER_VALIDATE_EMAIL)) $error='Enter a valid SMTP username/email address.';
     elseif(!filter_var($from_email,FILTER_VALIDATE_EMAIL)) $error='Enter a valid From email address.';
     elseif(!$host || $port<1 || $port>65535) $error='Enter valid SMTP server settings.';
     else {
       $stmt=$conn->prepare("INSERT INTO email_settings (id,provider,host,port,encryption,username,password,from_email,from_name) VALUES (1,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE provider=VALUES(provider),host=VALUES(host),port=VALUES(port),encryption=VALUES(encryption),username=VALUES(username),password=VALUES(password),from_email=VALUES(from_email),from_name=VALUES(from_name)");
       $stmt->bind_param('ssissssss',$provider,$host,$port,$encryption,$username,$password,$from_email,$from_name);
       if($stmt->execute()){ $msg='Email settings saved.'; $row=array_merge($row,['provider'=>$provider,'host'=>$host,'port'=>$port,'encryption'=>$encryption,'username'=>$username,'password'=>$password,'from_email'=>$from_email,'from_name'=>$from_name]); } else $error='Could not save email settings: '.$stmt->error;
       $stmt->close();
     }
   }
 }
 if($action==='test'){
   require_once 'smtp_mail.php';
   $to=trim($_POST['test_email']??'');
   if(!filter_var($to,FILTER_VALIDATE_EMAIL)) $error='Enter a valid test email address.';
   else { [$ok,$mailError]=smtpSend($to,'Southbridge Test Recipient','Southbridge Rehabilitation - Test Email',"This is a test email from your Southbridge Rehabilitation website.\n\nIf you received this message, your email settings are working."); if($ok)$msg='Test email sent successfully to '.$to.'.'; else $error='Test email failed: '.$mailError; }
 }
}

$currentProvider=$row['provider']??'gmail';
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Email Settings - Southbridge</title>
<style>body{font-family:Arial;background:#eef3f7;margin:0}.page{max-width:850px;margin:30px auto;background:#fff;padding:28px;border-radius:10px;box-shadow:0 3px 15px #0002}h1{text-align:center;color:#165a8a}.top{margin-bottom:20px}.top a{display:inline-block;padding:9px 13px;background:#165a8a;color:#fff;text-decoration:none;margin-right:6px;border-radius:4px}.grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}label{font-weight:bold;display:block;margin:8px 0 5px}input,select{width:100%;box-sizing:border-box;padding:10px;border:1px solid #aaa;border-radius:4px}button{padding:11px 16px;border:0;border-radius:4px;background:#165a8a;color:#fff;font-weight:bold;cursor:pointer}.secondary{background:#166534}.msg,.err{padding:11px;border-radius:4px;margin:12px 0}.msg{background:#dcfce7;color:#166534}.err{background:#fee2e2;color:#991b1b}.help{background:#f3f4f6;padding:12px;margin:15px 0;border-radius:5px;line-height:1.5}.actions{margin-top:18px;display:flex;gap:10px;flex-wrap:wrap}@media(max-width:650px){.grid{grid-template-columns:1fr}}</style></head><body><div class="page">
<h1>Southbridge Rehabilitation</h1><h2>Email Settings</h2><div class="top"><a href="SouthbridgeRehab.php">Home</a><a href="admin_users.php">User Management</a><a href="logout.php">Log Out</a></div>
<?php if($msg):?><div class="msg"><?=htmlspecialchars($msg)?></div><?php endif;?><?php if($error):?><div class="err"><?=htmlspecialchars($error)?></div><?php endif;?>
<div class="help"><strong>Choose your email provider.</strong> Gmail uses a Google App Password. iCloud uses an Apple app-specific password. Microsoft 365 requires SMTP AUTH to be enabled. Regular Outlook.com/Hotmail accounts require OAuth2 and cannot use the basic SMTP sender in this package.</div>
<form method="post"><input type="hidden" name="action" value="save"><div class="grid">
<div><label>Provider</label><select name="provider" id="provider" onchange="providerChanged()"><option value="gmail" <?=$currentProvider==='gmail'?'selected':''?>>Gmail / Google Workspace</option><option value="outlook" <?=$currentProvider==='outlook'?'selected':''?>>Outlook.com / Hotmail / Live</option><option value="microsoft365" <?=$currentProvider==='microsoft365'?'selected':''?>>Microsoft 365</option><option value="icloud" <?=$currentProvider==='icloud'?'selected':''?>>iCloud</option></select></div>
<div><label>SMTP Server</label><input name="host" id="host" value="<?=htmlspecialchars($row['host'])?>"></div>
<div><label>SMTP Port</label><input name="port" id="port" type="number" value="<?=htmlspecialchars($row['port'])?>"></div>
<div><label>Encryption</label><select name="encryption" id="encryption"><option value="tls" <?=$row['encryption']==='tls'?'selected':''?>>STARTTLS / TLS (587)</option><option value="ssl" <?=$row['encryption']==='ssl'?'selected':''?>>SSL/TLS (465)</option></select></div>
<div><label>Email / SMTP Username</label><input name="username" type="email" value="<?=htmlspecialchars($row['username'])?>" required></div>
<div><label>Password / App Password</label><input name="password" type="password" placeholder="Leave blank to keep existing password"></div>
<div><label>From Email</label><input name="from_email" type="email" value="<?=htmlspecialchars($row['from_email'] ?: $row['username'])?>" required></div>
<div><label>From Name</label><input name="from_name" value="<?=htmlspecialchars($row['from_name'])?>"></div>
</div><div class="actions"><button type="submit">Save Email Settings</button></div></form>
<hr style="margin:25px 0"><h3>Test Email</h3><p>Save your settings first, then send a test message.</p><form method="post"><input type="hidden" name="action" value="test"><label>Send Test Email To</label><input name="test_email" type="email" placeholder="your@email.com" required><div class="actions"><button class="secondary">Send Test Email</button></div></form>
</div><script>
const presets={gmail:['smtp.gmail.com',587,'tls'],outlook:['smtp-mail.outlook.com',587,'tls'],microsoft365:['smtp.office365.com',587,'tls'],icloud:['smtp.mail.me.com',587,'tls']};
function providerChanged(){const p=document.getElementById('provider').value;if(presets[p]){document.getElementById('host').value=presets[p][0];document.getElementById('port').value=presets[p][1];document.getElementById('encryption').value=presets[p][2];}}
</script></body></html>
