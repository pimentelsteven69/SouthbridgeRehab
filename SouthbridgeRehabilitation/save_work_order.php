<?php
require_once 'auth.php';
require_permission('work_order_create');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success'=>false,'message'=>'POST required.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) {
    $data = $_POST;
}
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>'Invalid form data.']);
    exit;
}

mysqli_report(MYSQLI_REPORT_OFF);
$conn = new mysqli('localhost', 'root', '', 'southbridge_loto');
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Database connection failed: '.$conn->connect_error]);
    exit;
}
$conn->set_charset('utf8mb4');

$create = "CREATE TABLE IF NOT EXISTS work_orders (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  work_order_number VARCHAR(100) DEFAULT '',
  work_order_date DATE NULL,
  floor_location VARCHAR(50) DEFAULT '',
  room_number VARCHAR(20) DEFAULT '',
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
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Could not create work_orders table: '.$conn->error]);
    exit;
}

function v($a,$k){ return isset($a[$k]) ? trim((string)$a[$k]) : ''; }
function d($v){ $v=trim((string)$v); return ($v !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/',$v)) ? $v : null; }

$wo = v($data,'workOrder');
$wd = d(v($data,'date'));
$floor = v($data,'floor');
$room = v($data,'room');
$problem = v($data,'problem');
$completed = v($data,'workDone');
$parts = v($data,'parts');
$notes = v($data,'notes');
$by = v($data,'completedBy');
$cd = d(v($data,'completionDate'));
$sig = v($data,'signature');
$ds = d(v($data,'signDate'));
$status = v($data,'status') ?: 'Completed';

$stmt = $conn->prepare("INSERT INTO work_orders
(work_order_number,work_order_date,floor_location,room_number,problem,work_completed,
parts_materials,notes,completed_by,completion_date,supervisor_signature,date_signed,status)
VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Prepare failed: '.$conn->error]);
    exit;
}

$stmt->bind_param('sssssssssssss',$wo,$wd,$floor,$room,$problem,$completed,$parts,$notes,$by,$cd,$sig,$ds,$status);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Save failed: '.$stmt->error]);
    exit;
}

$id = $stmt->insert_id;
$stmt->close();
$conn->close();

echo json_encode(['success'=>true,'message'=>'Work order saved successfully.','id'=>$id]);
?>
