<?php
// Southbridge Rehabilitation - LOTO Log
// XAMPP/PHP/MySQL version

$dbHost = 'localhost';
$dbName = 'southbridge_loto';
$dbUser = 'root';
$dbPass = ''; // Default XAMPP MySQL password is usually blank.

$message = '';
$error = '';

try {
    $pdo = new PDO(
        "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4",
        $dbUser,
        $dbPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (PDOException $e) {
    $pdo = null;
    $error = 'Database connection failed. Import database.sql in phpMyAdmin and check the MySQL settings in this file.';
}

function h($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo) {
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $pdo->beginTransaction();
        try {
            $pdo->exec("DELETE FROM loto_entries");
            $stmt = $pdo->prepare("INSERT INTO loto_entries
                (entry_date, time_out, equipment, location, hazard, lock_tag, employee, verified, time_in, work_performed, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $dates = $_POST['date'] ?? [];
            $outs = $_POST['out'] ?? [];
            $equipment = $_POST['equipment'] ?? [];
            $locations = $_POST['location'] ?? [];
            $hazards = $_POST['hazard'] ?? [];
            $locks = $_POST['lock'] ?? [];
            $employees = $_POST['employee'] ?? [];
            $verified = $_POST['verified'] ?? [];
            $ins = $_POST['in'] ?? [];
            $work = $_POST['work'] ?? [];
            $notes = $_POST['notes'] ?? [];

            $count = max(
                count($dates), count($outs), count($equipment), count($locations),
                count($hazards), count($locks), count($employees), count($verified),
                count($ins), count($work), count($notes)
            );

            for ($i = 0; $i < $count; $i++) {
                $values = [
                    $dates[$i] ?? '', $outs[$i] ?? '', $equipment[$i] ?? '',
                    $locations[$i] ?? '', $hazards[$i] ?? '', $locks[$i] ?? '',
                    $employees[$i] ?? '', $verified[$i] ?? '', $ins[$i] ?? '',
                    $work[$i] ?? '', $notes[$i] ?? ''
                ];
                // Do not store completely blank rows.
                if (trim(implode('', $values)) !== '') {
                    $stmt->execute($values);
                }
            }

            $stmtMeta = $pdo->prepare("INSERT INTO loto_settings
                (id, month_year, area, supervisor, review_signature, date_reviewed, additional_notes)
                VALUES (1, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                month_year=VALUES(month_year), area=VALUES(area), supervisor=VALUES(supervisor),
                review_signature=VALUES(review_signature), date_reviewed=VALUES(date_reviewed),
                additional_notes=VALUES(additional_notes)");
            $stmtMeta->execute([
                $_POST['month'] ?? '',
                $_POST['area'] ?? '',
                $_POST['supervisor'] ?? '',
                $_POST['review_signature'] ?? '',
                $_POST['date_reviewed'] ?? '',
                $_POST['additional_notes'] ?? ''
            ]);

            $pdo->commit();
            $message = 'LOTO log saved successfully.';
        } catch (Throwable $e) {
            $pdo->rollBack();
            $error = 'Could not save the log: ' . $e->getMessage();
        }
    }

    if ($action === 'clear') {
        $pdo->exec("DELETE FROM loto_entries");
        $pdo->exec("DELETE FROM loto_settings");
        $message = 'LOTO log cleared.';
    }
}

$entries = [];
$settings = [
    'month_year' => '', 'area' => '', 'supervisor' => '',
    'review_signature' => '', 'date_reviewed' => '', 'additional_notes' => ''
];

if ($pdo) {
    $entries = $pdo->query("SELECT * FROM loto_entries ORDER BY id")->fetchAll();
    $row = $pdo->query("SELECT * FROM loto_settings WHERE id=1")->fetch();
    if ($row) $settings = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Southbridge Rehabilitation - Lockout/Tagout Log</title>
<style>
@page { size: landscape; margin: .45in; }
* { box-sizing:border-box; }
body { font-family:Arial,Helvetica,sans-serif; margin:0; color:#111; background:#f3f4f6; }
.page { max-width:1400px; margin:20px auto; background:white; padding:24px; box-shadow:0 2px 10px rgba(0,0,0,.12); }
h1 { text-align:center; margin:0 0 4px; font-size:25px; }
h2 { text-align:center; margin:0 0 18px; font-size:18px; font-weight:normal; }
.top { display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px; margin-bottom:12px; }
label { font-weight:bold; font-size:13px; display:block; margin-bottom:4px; }
input,select,textarea { width:100%; border:1px solid #777; padding:7px; font:inherit; background:#fff; }
textarea { min-height:55px; resize:vertical; }
table { width:100%; border-collapse:collapse; table-layout:fixed; font-size:11px; }
th,td { border:1px solid #555; padding:5px; vertical-align:top; }
th { background:#e5e7eb; text-align:center; font-weight:bold; }
td input,td select,td textarea { border:0; padding:2px; min-width:0; }
td textarea { min-height:42px; }
.controls { display:flex; gap:8px; margin:12px 0; flex-wrap:wrap; }
button { border:0; padding:9px 14px; cursor:pointer; font-weight:bold; border-radius:4px; }
.add { background:#166534; color:#fff; }
.print { background:#1d4ed8; color:#fff; }
.clear { background:#b91c1c; color:#fff; }
.save { background:#7c3aed; color:#fff; }
.instructions { margin-top:14px; border:1px solid #777; padding:10px; font-size:11px; }
.signature { margin-top:14px; display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; }
.footer { margin-top:10px; font-size:10px; text-align:center; }
.alert { padding:10px; margin-bottom:12px; border-radius:4px; font-size:13px; }
.success { background:#dcfce7; border:1px solid #86efac; }
.error { background:#fee2e2; border:1px solid #fca5a5; }
@media print {
 body { background:#fff; }
 .page { margin:0; padding:0; max-width:none; box-shadow:none; }
 .controls,.no-print { display:none !important; }
 input,select,textarea { background:#fff; }
 tr { page-break-inside:avoid; }
}
</style>
</head>
<body>
<div class="page">
<h1>SOUTHBRIDGE REHABILITATION</h1>
<h2>Facility Maintenance</h2>

<?php if ($message): ?><div class="alert success"><?=h($message)?></div><?php endif; ?>
<?php if ($error): ?><div class="alert error"><?=h($error)?></div><?php endif; ?>

<form method="post" id="lotoForm">
<input type="hidden" name="action" value="save">

<div class="top">
<div><label>Month / Year</label><input name="month" id="month" type="month" value="<?=h($settings['month_year'])?>"></div>
<div><label>Department / Area</label><input name="area" id="area" type="text" value="<?=h($settings['area'])?>"></div>
<div><label>Supervisor</label><input name="supervisor" id="supervisor" type="text" value="<?=h($settings['supervisor'])?>"></div>
</div>

<div class="controls no-print">
<button type="button" class="add" onclick="addRow()">+ Add LOTO Entry</button>
<button type="button" class="save" onclick="document.getElementById('lotoForm').submit()">Save to Database</button>
<button type="button" class="print" onclick="window.print()">Print Log</button>
<button type="button" class="add" onclick="exportToExcel()">Export to Excel</button>
<button type="button" class="clear" onclick="clearLog()">Clear Entries</button>
</div>

<table id="lotoTable">
<thead><tr>
<th style="width:5%">Date</th><th style="width:6%">Time<br>Out</th>
<th style="width:12%">Equipment / Machine</th><th style="width:10%">Location / Room</th>
<th style="width:11%">Energy Source / Hazard</th><th style="width:9%">Lock / Tag<br>Number</th>
<th style="width:11%">Authorized Employee</th><th style="width:9%">Isolation<br>Verified</th>
<th style="width:6%">Time<br>In</th><th style="width:10%">Work Performed</th>
<th style="width:11%">Return to Service / Notes</th>
</tr></thead>
<tbody>
<?php foreach ($entries as $e): ?>
<tr>
<td><input name="date[]" type="date" value="<?=h($e['entry_date'])?>"></td>
<td><input name="out[]" type="time" value="<?=h($e['time_out'])?>"></td>
<td><textarea name="equipment[]"><?=h($e['equipment'])?></textarea></td>
<td><input name="location[]" type="text" value="<?=h($e['location'])?>"></td>
<td><textarea name="hazard[]"><?=h($e['hazard'])?></textarea></td>
<td><input name="lock[]" type="text" value="<?=h($e['lock_tag'])?>"></td>
<td><input name="employee[]" type="text" value="<?=h($e['employee'])?>"></td>
<td><select name="verified[]"><option value=""></option>
<option <?= $e['verified']==='Yes'?'selected':'' ?>>Yes</option>
<option <?= $e['verified']==='No'?'selected':'' ?>>No</option>
<option <?= $e['verified']==='N/A'?'selected':'' ?>>N/A</option></select></td>
<td><input name="in[]" type="time" value="<?=h($e['time_in'])?>"></td>
<td><textarea name="work[]"><?=h($e['work_performed'])?></textarea></td>
<td><textarea name="notes[]"><?=h($e['notes'])?></textarea></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<div class="instructions">
<strong>LOTO Log Reminder:</strong>
Before servicing equipment, identify all hazardous energy sources, shut down the equipment,
isolate the energy source(s), apply locks/tags, release or restrain stored energy, and verify
zero-energy state before work begins. Document completion and return-to-service information.
</div>

<div class="signature">
<div><label>Supervisor Review / Signature</label><input name="review_signature" type="text" value="<?=h($settings['review_signature'])?>"></div>
<div><label>Date Reviewed</label><input name="date_reviewed" type="date" value="<?=h($settings['date_reviewed'])?>"></div>
<div><label>Additional Notes</label><input name="additional_notes" type="text" value="<?=h($settings['additional_notes'])?>"></div>
</div>
</form>

<div class="footer">Southbridge Rehabilitation</div>
</div>

<script>
const tbody = document.querySelector('#lotoTable tbody');

function addRow(data={}) {
 const tr=document.createElement('tr');
 tr.innerHTML=`
 <td><input name="date[]" type="date" value="${esc(data.date||'')}"></td>
 <td><input name="out[]" type="time" value="${esc(data.out||'')}"></td>
 <td><textarea name="equipment[]">${esc(data.equipment||'')}</textarea></td>
 <td><input name="location[]" type="text" value="${esc(data.location||'')}"></td>
 <td><textarea name="hazard[]">${esc(data.hazard||'')}</textarea></td>
 <td><input name="lock[]" type="text" value="${esc(data.lock||'')}"></td>
 <td><input name="employee[]" type="text" value="${esc(data.employee||'')}"></td>
 <td><select name="verified[]"><option value=""></option><option>Yes</option><option>No</option><option>N/A</option></select></td>
 <td><input name="in[]" type="time" value="${esc(data.in||'')}"></td>
 <td><textarea name="work[]">${esc(data.work||'')}</textarea></td>
 <td><textarea name="notes[]">${esc(data.notes||'')}</textarea></td>`;
 tbody.appendChild(tr);
}

function esc(v) {
 return String(v).replace(/[&<>"']/g, ch => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[ch]));
}

function exportToExcel() {
 const rows=[];
 const headers=Array.from(document.querySelectorAll('#lotoTable thead th')).map(th=>th.innerText.replace(/\n/g,' ').trim());
 rows.push(headers);
 document.querySelectorAll('#lotoTable tbody tr').forEach(tr=>{
   rows.push(Array.from(tr.querySelectorAll('input,select,textarea')).map(el=>el.value));
 });
 const month=document.getElementById('month').value||'';
 const area=document.getElementById('area').value||'';
 const supervisor=document.getElementById('supervisor').value||'';
 let html='<table border="1"><tr><th colspan="'+headers.length+'">SOUTHBRIDGE REHABILITATION - LOCKOUT / TAGOUT (LOTO) LOG</th></tr>';
 html+='<tr><td>Month / Year</td><td>'+esc(month)+'</td><td>Department / Area</td><td>'+esc(area)+'</td><td>Supervisor</td><td>'+esc(supervisor)+'</td></tr>';
 html+='<tr>'+headers.map(h=>'<th>'+esc(h)+'</th>').join('')+'</tr>';
 rows.slice(1).forEach(row=>{ html+='<tr>'+row.map(v=>'<td>'+esc(v)+'</td>').join('')+'</tr>'; });
 html+='</table>';
 const blob=new Blob(['\ufeff'+html],{type:'application/vnd.ms-excel'});
 const url=URL.createObjectURL(blob), a=document.createElement('a');
 a.href=url; a.download='Southbridge_Rehabilitation_LOTO_Log_'+new Date().toISOString().slice(0,10)+'.xls';
 document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url);
}

function clearLog() {
 if(confirm('Clear all LOTO entries from the database?')) {
   const f=document.createElement('form'); f.method='post';
   f.innerHTML='<input name="action" value="clear">';
   document.body.appendChild(f); f.submit();
 }
}
if (tbody.children.length===0) for(let i=0;i<8;i++) addRow();
</script>
</body>
</html>
