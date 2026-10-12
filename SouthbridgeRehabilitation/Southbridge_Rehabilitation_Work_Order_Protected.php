<?php
require_once 'auth.php';
require_permission('work_order_create');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Southbridge Rehabilitation - Work Order</title>
<style>
  @page { size: letter; margin: 0.5in; }
  * { box-sizing: border-box; }
  body {
    font-family: Arial, Helvetica, sans-serif;
    margin: 0;
    color: #111;
    background: #f3f4f6;
  }
  .page {
    max-width: 1000px;
    margin: 20px auto;
    background: #fff;
    padding: 28px;
    box-shadow: 0 2px 10px rgba(0,0,0,.12);
  }
  h1 { text-align:center; margin:0 0 5px; font-size:28px; }
  h2 { text-align:center; margin:0 0 22px; font-size:20px; font-weight:normal; }
  .top, .two, .signature {
    display:grid;
    gap:14px;
  }
  .top { grid-template-columns: 1fr 1fr; }
  .two { grid-template-columns: 1fr 1fr; }
  .field { margin-bottom:14px; }
  label {
    font-weight:bold;
    font-size:14px;
    display:block;
    margin-bottom:5px;
  }
  input, select, textarea {
    width:100%;
    border:1px solid #777;
    border-radius:3px;
    padding:9px;
    font:inherit;
    background:#fff;
  }
  textarea { min-height:105px; resize:vertical; }
  .section-title {
    background:#e5e7eb;
    border:1px solid #777;
    padding:8px 10px;
    font-weight:bold;
    margin:8px 0 12px;
  }
  .controls {
    display:flex;
    flex-wrap:wrap;
    gap:9px;
    margin:18px 0 8px;
  }
  button {
    border:0;
    padding:10px 15px;
    cursor:pointer;
    font-weight:bold;
    border-radius:4px;
  }
  .save { background:#166534; color:#fff; }
  .print { background:#1d4ed8; color:#fff; }
  .loto { background:#7c3aed; color:#fff; }
  .clear { background:#b91c1c; color:#fff; }
  .status {
    display:none;
    margin:10px 0;
    padding:9px;
    border:1px solid #166534;
    background:#ecfdf5;
    color:#166534;
    font-weight:bold;
  }
  .signature {
    grid-template-columns: 1fr 1fr 1fr;
    margin-top:15px;
  }
  .footer {
    margin-top:18px;
    font-size:11px;
    text-align:center;
  }
  @media print {
    body { background:#fff; }
    .page { margin:0; padding:0; max-width:none; box-shadow:none; }
    .controls, .status { display:none !important; }
    input, select, textarea { background:#fff; }
  }
</style>
</head>
<body>
<div class="page">
  <h1>SOUTHBRIDGE REHABILITATION</h1>
  <h2>FACILITY MAINTENANCE WORK ORDER</h2>

  <div class="section-title">Work Order Information</div>

  <div class="top">
    <div class="field">
      <label for="date">Date</label>
      <input id="date" type="date">
    </div>
    <div class="field">
      <label for="workOrder">Work Order #</label>
      <input id="workOrder" type="text" placeholder="Optional">
    </div>
  </div>

  <div class="two">
    <div class="field">
      <label for="floor">Floor Location</label>
      <select id="floor">
        <option value="">Select floor</option>
        <option>1st floor</option>
        <option>2nd floor</option>
        <option>3rd floor</option>
        <option>4th floor</option>
      </select>
    </div>

    <div class="field">
      <label for="room">Room Number</label>
      <select id="room">
        <option value="">Select room</option>
      </select>
    </div>
  </div>

  <div class="field">
    <label for="problem">Problem / Work Requested</label>
    <textarea id="problem" placeholder="Describe the problem or work that needs to be completed..."></textarea>
  </div>

  <div class="field">
    <label for="workDone">Work Completed</label>
    <textarea id="workDone" placeholder="Describe the work that was completed..."></textarea>
  </div>

  <div class="field">
    <label for="parts">Parts / Materials Used</label>
    <textarea id="parts" placeholder="List parts or materials installed or used..."></textarea>
  </div>

  <div class="field">
    <label for="notes">Notes</label>
    <textarea id="notes" placeholder="Additional notes..."></textarea>
  </div>

  <div class="two">
    <div class="field">
      <label for="completedBy">Completed By</label>
      <input id="completedBy" type="text">
    </div>
    <div class="field">
      <label for="completionDate">Completion Date</label>
      <input id="completionDate" type="date">
    </div>
  </div>

  <div class="section-title">Completed Work Order Sign-Off</div>

  <div class="signature">
    <div class="field">
      <label for="signature">Supervisor / Authorized Signature</label>
      <input id="signature" type="text">
    </div>
    <div class="field">
      <label for="signDate">Date Signed</label>
      <input id="signDate" type="date">
    </div>
    <div class="field">
      <label for="status">Status</label>
      <select id="status">
        <option>Completed</option>
        <option>Pending</option>
        <option>Needs Follow-Up</option>
      </select>
    </div>
  </div>

  <div id="message" class="status">Work order saved on this computer.</div>

  <div class="controls">
    <button class="save" onclick="saveWorkOrder()">Save Work Order</button>
    <button class="print" onclick="window.print()">Print Work Order</button>
    <?php if (can_access('work_order_log')): ?>
    <button class="loto" onclick="window.location.href='work_order_log.php'">View Work Order Log</button>
    <?php endif; ?>
	<button class="clear" onclick="clearForm()">Clear Form</button>
    <button class="loto" onclick="window.location.href='SouthbridgeRehab.php'">Go to Home Page</button>
  </div>

  <div class="footer">Southbridge Rehabilitation — Facility Maintenance Work Order</div>
</div>

<script>
  const roomSelect = document.getElementById('room');

  for (let room = 200; room <= 420; room++) {
    const option = document.createElement('option');
    option.value = room;
    option.textContent = room;
    roomSelect.appendChild(option);
  }

  async function saveWorkOrder() {
    const data = {};
    document.querySelectorAll('input, select, textarea').forEach(el => {
      data[el.id] = el.value;
    });

    try {
      const response = await fetch('save_work_order_PROTECTED.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      });

      const result = await response.json();

      if (!response.ok || !result.success) {
        throw new Error(result.message || 'Could not save work order.');
      }

      const message = document.getElementById('message');
      message.textContent = 'Work order #' + result.id + ' saved to the database.';
      message.style.display = 'block';
      setTimeout(() => message.style.display = 'none', 4000);
    } catch (error) {
      alert('Database save failed: ' + error.message +
        '\\n\\nMake sure XAMPP Apache and MySQL are running and save_work_order_PROTECTED.php is in the same folder.');
    }
  }

  function clearForm() {
    if (confirm('Clear this work order form?')) {
      document.querySelectorAll('input, textarea').forEach(el => el.value = '');
      document.querySelectorAll('select').forEach(el => el.selectedIndex = 0);
    }
  }

  // Put today's date into the date fields.
  const today = new Date().toISOString().slice(0, 10);
  document.getElementById('date').value = today;
</script>
</body>
</html>
