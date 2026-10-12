<?php
require_once 'auth.php';
require_permission('pest_control');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Southbridge Rehabilitation - Pest Control Log</title>
<style>
  @page { size: landscape; margin: 0.45in; }
  * { box-sizing: border-box; }
  body { font-family: Arial, Helvetica, sans-serif; margin:0; background:#f3f4f6; color:#111; }
  .page { max-width:1450px; margin:20px auto; background:#fff; padding:24px; box-shadow:0 2px 10px rgba(0,0,0,.12); }
  h1 { text-align:center; margin:0 0 4px; font-size:25px; }
  h2 { text-align:center; margin:0 0 18px; font-size:18px; font-weight:normal; }
  .top { display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px; margin-bottom:12px; }
  label { font-weight:bold; font-size:13px; display:block; margin-bottom:4px; }
  input, select, textarea { width:100%; border:1px solid #777; padding:7px; font:inherit; background:#fff; }
  textarea { min-height:48px; resize:vertical; }
  table { width:100%; border-collapse:collapse; table-layout:fixed; font-size:11px; }
  th, td { border:1px solid #555; padding:5px; vertical-align:top; }
  th { background:#e5e7eb; text-align:center; font-weight:bold; }
  td input, td select, td textarea { border:0; padding:2px; min-width:0; }
  td textarea { min-height:40px; }
  .controls { display:flex; gap:8px; margin:12px 0; flex-wrap:wrap; }
  button { border:0; padding:9px 14px; cursor:pointer; font-weight:bold; border-radius:4px; color:#fff; background:#1d4ed8; }
  .add { background:#166534; }
  .clear { background:#b91c1c; }
  .note { margin-top:14px; border:1px solid #777; padding:10px; font-size:11px; }
  .footer { margin-top:10px; font-size:10px; text-align:center; }
  @media print {
    body { background:#fff; }
    .page { margin:0; padding:0; max-width:none; box-shadow:none; }
    .controls { display:none; }
    tr { page-break-inside:avoid; }
  }
</style>
</head>
<body>
<div class="page">
  <h1>SOUTHBRIDGE REHABILITATION</h1>
  <h2>PEST CONTROL LOG</h2>

  <div class="top">
    <div><label>Month / Year</label><input type="month"></div>
    <div><label>Department / Area</label><input type="text"></div>
    <div><label>Supervisor</label><input type="text"></div>
  </div>

  <div class="controls">
    <button class="add" onclick="addRow()">+ Add Pest Control Entry</button>
    <button onclick="window.print()">Print Pest Control Log</button>
    <button onclick="window.location.href='LOTO.html'">Back to LOTO Log</button>
    <button onclick="window.location.href='LOTO2.html'">Go to LOTO Page 2</button>
    <button class="clear" onclick="clearLog()">Clear Entries</button>
  </div>

  <table id="pestTable">
    <thead>
      <tr>
        <th style="width:6%">Date</th>
        <th style="width:8%">Time</th>
        <th style="width:11%">Location / Room</th>
        <th style="width:11%">Pest / Problem</th>
        <th style="width:13%">Treatment / Service Performed</th>
        <th style="width:10%">Product / Material Used</th>
        <th style="width:8%">Amount</th>
        <th style="width:10%">Technician / Company</th>
        <th style="width:9%">Follow-Up Date</th>
        <th style="width:14%">Notes / Corrective Action</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>

  <div class="note">
    <strong>Pest Control Record:</strong> Document inspections, pest activity, service performed,
    products/materials used, technician/company, follow-up needs, and corrective actions.
    Follow the facility's approved pest-management procedures and applicable product-label requirements.
  </div>

  <div class="footer">Southbridge Rehabilitation — Pest Control Log</div>
</div>

<script>
const tbody = document.querySelector('#pestTable tbody');

function addRow(data={}) {
  const tr = document.createElement('tr');
  tr.innerHTML = `
    <td><input type="date" value="${data.date||''}"></td>
    <td><input type="time" value="${data.time||''}"></td>
    <td><input type="text" value="${data.location||''}"></td>
    <td><textarea>${data.pest||''}</textarea></td>
    <td><textarea>${data.treatment||''}</textarea></td>
    <td><input type="text" value="${data.product||''}"></td>
    <td><input type="text" value="${data.amount||''}"></td>
    <td><input type="text" value="${data.tech||''}"></td>
    <td><input type="date" value="${data.followup||''}"></td>
    <td><textarea>${data.notes||''}</textarea></td>`;
  tbody.appendChild(tr);
}

function clearLog() {
  if (confirm('Clear all pest control entries?')) {
    tbody.innerHTML = '';
    for (let i=0; i<8; i++) addRow();
  }
}

for (let i=0; i<8; i++) addRow();
</script>
</body>
</html>