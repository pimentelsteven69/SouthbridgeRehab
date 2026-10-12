<?php
require_once 'auth.php';
require_permission('user_management');

$msg=''; $error='';
$roles=['Admin','Maintenance Supervisor','Maintenance','Pest Control','Head Nurse','Nurse','Administration','Receptionist','Work Order Inserty'];

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $action=$_POST['action']??'';
    if ($action==='role') {
        $id=(int)($_POST['id']??0);
        $role=$_POST['role']??'';
        if (!in_array($role,$roles,true)) $error='Invalid role.';
        elseif ($id===(int)$_SESSION['user_id'] && $role!=='Admin') $error='You cannot remove your own Admin role while logged in.';
        else {
            $stmt=$conn->prepare("UPDATE users SET role=? WHERE id=?");
            $stmt->bind_param('si',$role,$id);
            $stmt->execute(); $stmt->close();
            $msg='User role updated.';
        }
    } elseif ($action==='delete') {
        $id=(int)($_POST['id']??0);
        if ($id===(int)$_SESSION['user_id']) $error='You cannot delete your own account.';
        else {
            $stmt=$conn->prepare("DELETE FROM users WHERE id=?");
            $stmt->bind_param('i',$id); $stmt->execute(); $stmt->close();
            $msg='User deleted.';
        }
    }
}
$users=$conn->query("SELECT id,full_name,email,role,created_at FROM users ORDER BY full_name");
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Southbridge - User Management</title><style>body{font-family:Arial;background:#eef3f7;margin:0}.page{max-width:1100px;margin:25px auto;background:#fff;padding:25px;border-radius:10px;box-shadow:0 3px 15px #0002}h1{text-align:center}.msg{padding:10px;background:#dcfce7;color:#166534}.err{padding:10px;background:#fee2e2;color:#991b1b}table{width:100%;border-collapse:collapse;margin-top:20px}th,td{border:1px solid #aaa;padding:9px;text-align:left}th{background:#e5e7eb}select,button{padding:8px}.save{background:#165a8a;color:#fff;border:0}.delete{background:#b91c1c;color:#fff;border:0}.top a{display:inline-block;padding:9px 13px;background:#165a8a;color:#fff;text-decoration:none;margin-right:6px;border-radius:4px}</style></head>
<body><div class="page"><h1>Southbridge Rehabilitation</h1><h2>User &amp; Role Management</h2>
<div class="top"><a href="SouthbridgeRehab.php">Home</a><a href="admin_email_settings.php">Email Settings</a><a href="logout.php">Log Out</a></div>
<?php if($msg):?><p class="msg"><?=htmlspecialchars($msg)?></p><?php endif;?>
<?php if($error):?><p class="err"><?=htmlspecialchars($error)?></p><?php endif;?>
<table><tr><th>Name</th><th>Email</th><th>Role</th><th>Created</th><th>Actions</th></tr>
<?php while($u=$users->fetch_assoc()): ?><tr>
<td><?=htmlspecialchars($u['full_name'])?></td><td><?=htmlspecialchars($u['email'])?></td><td><?=htmlspecialchars($u['role'])?></td><td><?=htmlspecialchars($u['created_at'])?></td>
<td><form method="post" style="display:inline-flex;gap:5px"><input type="hidden" name="action" value="role"><input type="hidden" name="id" value="<?=$u['id']?>">
<select name="role"><?php foreach($roles as $r):?><option <?= $u['role']===$r?'selected':''?>><?=htmlspecialchars($r)?></option><?php endforeach;?></select><button class="save">Save Role</button></form>
<form method="post" style="display:inline" onsubmit="return confirm('Delete this user?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$u['id']?>"><button class="delete">Delete</button></form></td>
</tr><?php endwhile;?></table></div></body></html>