<?php
require_once 'auth.php';
require_permission('work_order_log');
header('Content-Type: text/html; charset=utf-8');

$conn = new mysqli('localhost', 'root', '', 'southbridge_loto');
if ($conn->connect_error) {
    die('<h2 style="font-family:Arial">Database connection failed: ' . htmlspecialchars($conn->connect_error) . '</h2><p>Make sure MySQL is running in XAMPP.</p>');
}
$conn->set_charset('utf8mb4');

// Create the work_orders table automatically if it does not exist.
$create = "CREATE TABLE IF NOT EXISTS work_orders (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  work_order_number VARCHAR(100) DEFAULT '',
  work_order_date DATE NULL,
  floor_location VARCHAR(50) DEFAULT '',
  room_number VARCHAR(10) DEFAULT '',
  problem TEXT,
  work_completed TEXT,
  parts_materials TEXT,
  notes TEXT,
  completed_by VARCHAR(255) DEFAULT '',
  completion_date DATE NULL,
  supervisor_signature VARCHAR(255) DEFAULT '',
  date_signed DATE NULL,
  status VARCHAR(50) DEFAULT 'Completed',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (!$conn->query($create)) {
    die('<h2 style="font-family:Arial">Could not create work order table</h2><p>' . htmlspecialchars($conn->error) . '</p>');
}

$result = $conn->query("SELECT id, work_order_number, work_order_date, floor_location, room_number,
    problem, work_completed, parts_materials, notes, completed_by,
    completion_date, supervisor_signature, date_signed, status, created_at
    FROM work_orders ORDER BY id DESC");

function h($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Southbridge Rehabilitation - Work Order Log</title>
<style>
*{box-sizing:border-box} body{font-family:Arial,Helvetica,sans-serif;margin:0;color:#111;background:#eef2f5}.page{max-width:1500px;margin:20px auto;background:#fff;padding:24px;box-shadow:0 2px 10px rgba(0,0,0,.12)}h1{text-align:center;margin:0 0 5px;font-size:27px}h2{text-align:center;margin:0 0 18px;font-size:19px;font-weight:normal}.controls{display:flex;gap:9px;margin-bottom:15px;flex-wrap:wrap}button{border:0;padding:10px 15px;cursor:pointer;font-weight:bold;border-radius:4px}.back{background:#1d4ed8;color:#fff}.print{background:#166534;color:#fff}.new{background:#0f766e;color:#fff}.count{margin-bottom:10px;font-weight:bold}.table-wrap{overflow:auto}table{width:100%;border-collapse:collapse;min-width:1300px;font-size:12px}th,td{border:1px solid #777;padding:7px;vertical-align:top}th{background:#dfe7ee;text-align:center;position:sticky;top:0}td.long{min-width:180px;white-space:pre-wrap}.empty{text-align:center;padding:30px;border:1px solid #777}@media print{body{background:#fff}.page{margin:0;padding:0;max-width:none;box-shadow:none}.controls{display:none}table{font-size:9px}}
</style>
</head>
<body>
<div class="page">
<h1>SOUTHBRIDGE REHABILITATION</h1>
<h2>FACILITY MAINTENANCE WORK ORDER LOG</h2>
<div class="controls">
<button class="back" onclick="window.location.href='SouthbridgeRehab.php'">⌂ Home</button>
<button class="new" onclick="window.location.href='Southbridge_Rehabilitation_Work_Order.php'">+ New Work Order</button>
<button class="print" onclick="window.print()">Print Log</button>
</div>

<?php if (!$result): ?>
<div class="empty">Unable to load work orders: <?= h($conn->error) ?></div>
<?php else: ?>
<div class="count">Total Work Orders: <?= $result->num_rows ?></div>
<?php if ($result->num_rows === 0): ?>
<div class="empty">No work orders have been saved yet. Create a work order and click <b>Save Work Order</b>.</div>
<?php else: ?>
<div class="table-wrap"><table><thead><tr>
<th>ID</th><th>Work Order #</th><th>Date</th><th>Floor</th><th>Room</th><th>Problem / Work Requested</th><th>Work Completed</th><th>Parts / Materials</th><th>Notes</th><th>Completed By</th><th>Completion Date</th><th>Supervisor Signature</th><th>Date Signed</th><th>Status</th><th>Saved</th>
</tr></thead><tbody>
<?php while ($row = $result->fetch_assoc()): ?><tr>
<td><?= h($row['id']) ?></td><td><?= h($row['work_order_number']) ?></td><td><?= h($row['work_order_date']) ?></td><td><?= h($row['floor_location']) ?></td><td><?= h($row['room_number']) ?></td><td class="long"><?= h($row['problem']) ?></td><td class="long"><?= h($row['work_completed']) ?></td><td class="long"><?= h($row['parts_materials']) ?></td><td class="long"><?= h($row['notes']) ?></td><td><?= h($row['completed_by']) ?></td><td><?= h($row['completion_date']) ?></td><td><?= h($row['supervisor_signature']) ?></td><td><?= h($row['date_signed']) ?></td><td><?= h($row['status']) ?></td><td><?= h($row['created_at']) ?></td>
</tr><?php endwhile; ?></tbody></table></div>
<?php endif; endif; ?>
</div>
</body></html>
<?php $conn->close(); ?>
