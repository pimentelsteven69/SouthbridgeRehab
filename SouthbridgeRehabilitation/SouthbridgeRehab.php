<?php
require_once 'auth.php';
require_login();
$u=current_user();
function btn($permission,$href,$icon,$label){
    if (!can_access($permission)) return '';
    return '<a class="page-button" href="'.htmlspecialchars($href).'"><div class="icon">'.$icon.'</div>'.htmlspecialchars($label).'</a>';
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Southbridge Rehabilitation - Facility Management</title>
<style>body{font-family:Arial;margin:0;background:#eef3f7}.container{max-width:1000px;margin:40px auto;background:#fff;padding:35px;border-radius:12px;box-shadow:0 4px 20px #0002;text-align:center}h1{color:#165a8a;margin-bottom:5px}.user{margin:10px 0 25px;color:#555}.buttons{display:grid;grid-template-columns:repeat(2,1fr);gap:15px}.page-button{display:block;padding:25px;background:#165a8a;color:#fff;text-decoration:none;border-radius:8px;font-weight:bold}.page-button:hover{background:#174b6e}.icon{font-size:38px;margin-bottom:10px}.admin{background:#7c3aed}.logout{background:#b91c1c}@media(max-width:700px){.buttons{grid-template-columns:1fr}}</style></head>
<body><div class="container"><h1>SOUTHBRIDGE REHABILITATION</h1><h2>Facility Management Home</h2>
<div class="user">Signed in as <strong><?=htmlspecialchars($u['name'])?></strong> — <?=htmlspecialchars($u['role'])?></div>
<div class="buttons">
<?=btn('work_order_create','Southbridge_Rehabilitation_Work_Order.php','🛠️','Work Orders')?>
<?=btn('work_order_log','work_order_log.php','📋','Work Order Log')?>
<?=btn('loto','loto.php','🔒','Lockout / Tagout Log')?>
<?=btn('pest_control','pest_control.php','🦟','Pest Control Log')?>
<?=btn('user_management','admin_users.php','👥','User Management')?>
<a class="page-button" href="change_password.php"><div class="icon">🔑</div>Change Password</a>
<a class="page-button logout" href="logout.php"><div class="icon">🚪</div>Log Out</a>
</div></div></body></html>