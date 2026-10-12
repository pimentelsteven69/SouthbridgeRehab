<?php
require_once 'auth.php';
require_permission('loto');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Southbridge Rehabilitation - Lockout/Tagout Log</title>
<style>
  @page { size: landscape; margin: 0.45in; }
  * { box-sizing: border-box; }
  body {
    font-family: Arial, Helvetica, sans-serif;
    margin: 0;
    color: #111;
    background: #f3f4f6;
  }
  .page {
    max-width: 1400px;
    margin: 20px auto;
    background: white;
    padding: 24px;
    box-shadow: 0 2px 10px rgba(0,0,0,.12);
  }
  h1 { text-align:center; margin:0 0 4px; font-size:25px; }
  h2 { text-align:center; margin:0 0 18px; font-size:18px; font-weight:normal; }
  .top {
    display:grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap:10px;
    margin-bottom:12px;
  }
  label { font-weight:bold; font-size:13px; display:block; margin-bottom:4px; }
  input, select, textarea {
    width:100%;
    border:1px solid #777;
    padding:7px;
    font:inherit;
    background:#fff;
  }
  textarea { min-height:55px; resize:vertical; }
  table {
    width:100%;
    border-collapse:collapse;
    table-layout:fixed;
    font-size:11px;
  }
  th, td {
    border:1px solid #555;
    padding:5px;
    vertical-align:top;
  }
  th {
    background:#e5e7eb;
    text-align:center;
    font-weight:bold;
  }
  td input, td select, td textarea {
    border:0;
    padding:2px;
    min-width:0;
  }
  td textarea { min-height:42px; }
  .controls { display:flex; gap:8px; margin:12px 0; }
  button {
    border:0; padding:9px 14px; cursor:pointer;
    font-weight:bold; border-radius:4px;
  }
  .add { background:#166534; color:#fff; }
  .print { background:#1d4ed8; color:#fff; }
  .clear { background:#b91c1c; color:#fff; }
  .instructions {
    margin-top:14px; border:1px solid #777; padding:10px;
    font-size:11px;
  }
  .signature {
    margin-top:14px;
    display:grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap:12px;
  }
  .footer { margin-top:10px; font-size:10px; text-align:center; }
  @media print {
    body { background:#fff; }
    .page { margin:0; padding:0; max-width:none; box-shadow:none; }
    .controls { display:none; }
    input, select, textarea { background:#fff; }
    .no-print { display:none !important; }
    tr { page-break-inside:avoid; }
  }
</style>
</head>
<body>
<div class="page">
  <h1>SOUTHBRIDGE REHABILITATION</h1>
  <h2>LOCKOUT / TAGOUT (LOTO) LOG</h2>

  <div class="top">
    <div><label>Month / Year</label><input id="month" type="month"></div>
    <div><label>Department / Area</label><input id="area" type="text"></div>
    <div><label>Supervisor</label><input id="supervisor" type="text"></div>
  </div>

  <div class="controls no-print">
    <button class="add" onclick="addRow()">+ Add LOTO Entry</button>
    <button class="print" onclick="window.print()">Print Log</button>
    <button class="print" onclick="window.location.href='LOTO_Page_2.html'">Go to Page 2</button>
    <button class="print" onclick="window.location.href='Pest_Control_Log.html'">Pest Control Log</button>
    <button class="clear" onclick="clearLog()">Clear Entries</button>
  </div>

  <table id="lotoTable">
    <thead>
      <tr>
        <th style="width:5%">Date</th>
        <th style="width:6%">Time<br>Out</th>
        <th style="width:12%">Equipment / Machine</th>
        <th style="width:10%">Location / Room</th>
        <th style="width:11%">Energy Source / Hazard</th>
        <th style="width:9%">Lock / Tag<br>Number</th>
        <th style="width:11%">Authorized Employee</th>
        <th style="width:9%">Isolation<br>Verified</th>
        <th style="width:6%">Time<br>In</th>
        <th style="width:10%">Work Performed</th>
        <th style="width:11%">Return to Service / Notes</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>

  <div class="instructions">
    <strong>LOTO Log Reminder:</strong>
    Before servicing equipment, identify all hazardous energy sources, shut down the equipment,
    isolate the energy source(s), apply locks/tags, release or restrain stored energy, and verify
    zero-energy state before work begins. Document completion and return-to-service information.
  </div>

  <div class="signature">
    <div><label>Supervisor Review / Signature</label><input type="text"></div>
    <div><label>Date Reviewed</label><input type="date"></div>
    <div><label>Additional Notes</label><input type="text"></div>
  </div>

  <div class="footer">Southbridge Rehabilitation — Lockout/Tagout Log</div>
</div>

<script>
const tbody = document.querySelector('#lotoTable tbody');

function addRow(data={}) {
  const tr = document.createElement('tr');
  tr.innerHTML = `
    <td><input type="date" value="${data.date||''}"></td>
    <td><input type="time" value="${data.out||''}"></td>
    <td><textarea>${data.equipment||''}</textarea></td>
    <td><input type="text" value="${data.location||''}"></td>
    <td><textarea>${data.hazard||''}</textarea></td>
    <td><input type="text" value="${data.lock||''}"></td>
    <td><input type="text" value="${data.employee||''}"></td>
    <td>
      <select>
        <option value=""></option>
        <option ${data.verified==='Yes'?'selected':''}>Yes</option>
        <option ${data.verified==='No'?'selected':''}>No</option>
        <option ${data.verified==='N/A'?'selected':''}>N/A</option>
      </select>
    </td>
    <td><input type="time" value="${data.in||''}"></td>
    <td><textarea>${data.work||''}</textarea></td>
    <td><textarea>${data.notes||''}</textarea></td>`;
  tbody.appendChild(tr);
}

function clearLog() {
  if (confirm('Clear all LOTO entries?')) {
    tbody.innerHTML = '';
    for (let i=0; i<8; i++) addRow();
  }
}

for (let i=0; i<8; i++) addRow();
</script>
</body>
</html>