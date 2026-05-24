<?php
require_once 'includes/auth.php';
requireLogin();
require_once 'includes/db.php';

$message = '';

// ── Handle status update ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $id     = intval($_POST['request_id']);
    $status = $_POST['status'] ?? '';
    $note   = trim($_POST['admin_note'] ?? '');

    if (in_array($status, ['Pending','Approved','Rejected'])) {
        $stmt = $conn->prepare("UPDATE blood_requests SET status=?, admin_note=? WHERE id=?");
        $stmt->bind_param("ssi", $status, $note, $id);
        $stmt->execute();
        $message = "Request #$id updated to <strong>$status</strong>.";
    }
}

// ── Fetch detail view ──
$detail = null;
if (isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT * FROM blood_requests WHERE id=?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $detail = $stmt->get_result()->fetch_assoc();
}

// ── Filters ──
$filter_status = $_GET['status'] ?? '';
$search        = trim($_GET['search'] ?? '');

$where = "WHERE 1=1";
$params = [];
$types  = '';

if ($filter_status) {
    $where   .= " AND status=?";
    $params[] = $filter_status;
    $types   .= 's';
}
if ($search) {
    $like = "%$search%";
    $where   .= " AND (patient_name LIKE ? OR requester_name LIKE ? OR requester_cnic LIKE ? OR hospital_name LIKE ?)";
    $params   = array_merge($params, [$like, $like, $like, $like]);
    $types   .= 'ssss';
}

$sql = "SELECT * FROM blood_requests $where ORDER BY created_at DESC";
if ($types) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $requests = $stmt->get_result();
} else {
    $requests = $conn->query($sql);
}

$badge = ['Pending'=>'badge-pending','Approved'=>'badge-approved','Rejected'=>'badge-rejected'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Blood Requests — Admin</title>
<link rel="stylesheet" href="css/style.css">
<style>
.modal-overlay { display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:200;align-items:center;justify-content:center; }
.modal-overlay.open { display:flex; }
.modal { background:#fff;border-radius:20px;padding:36px;max-width:680px;width:95%;max-height:90vh;overflow-y:auto;position:relative; }
.modal-close { position:absolute;top:16px;right:20px;font-size:1.4rem;cursor:pointer;background:none;border:none;color:#9ca3af; }
.modal-close:hover { color:var(--dark); }
</style>
</head>
<body>

<nav class="topbar">
  <div class="topbar-brand">🩸 <span>Blood Bank</span></div>
  <div class="topbar-nav">
    <a href="dashboard.php">Dashboard</a>
    <a href="donor_form.php">Add Donor</a>
    <a href="view_donors.php">Donors</a>
    <a href="view_requests.php" class="active">Requests</a>
    <a href="settings.php">⚙️ Settings</a>
    <a href="settings.php">⚙️ Settings</a>
    <a href="logout.php" class="logout">Logout</a>
  </div>
</nav>

<div class="main-content">
  <div class="page-header">
    <h2>Blood Requests</h2>
    <p>View, approve, or reject blood requests. Only admins can see full details.</p>
  </div>

  <?php if ($message): ?>
    <div class="alert alert-success"><?= $message ?></div>
  <?php endif; ?>

  <!-- Filters -->
  <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:24px;align-items:flex-end;">
    <div style="flex:1;min-width:200px;">
      <label style="font-size:0.8rem;font-weight:600;display:block;margin-bottom:6px;">Search</label>
      <input type="text" name="search" class="form-control" placeholder="Name, CNIC, Hospital…" value="<?= htmlspecialchars($search) ?>">
    </div>
    <div>
      <label style="font-size:0.8rem;font-weight:600;display:block;margin-bottom:6px;">Status</label>
      <select name="status" class="form-control" style="width:160px;">
        <option value="">All Status</option>
        <option value="Pending"  <?= $filter_status=='Pending'  ?'selected':'' ?>>Pending</option>
        <option value="Approved" <?= $filter_status=='Approved' ?'selected':'' ?>>Approved</option>
        <option value="Rejected" <?= $filter_status=='Rejected' ?'selected':'' ?>>Rejected</option>
      </select>
    </div>
    <button type="submit" class="btn btn-blood btn-sm">Filter</button>
    <a href="view_requests.php" class="btn btn-outline btn-sm">Clear</a>
  </form>

  <div class="table-card">
    <div class="table-card-header">
      <h3>All Requests</h3>
    </div>
    <div style="overflow-x:auto;">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Requester</th>
          <th>Patient</th>
          <th>Blood</th>
          <th>Units</th>
          <th>Hospital</th>
          <th>Urgency</th>
          <th>CNIC</th>
          <th>Date</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php $count = 0; while($r = $requests->fetch_assoc()): $count++; ?>
        <tr>
          <td><?= $r['id'] ?></td>
          <td><?= htmlspecialchars($r['requester_name']) ?></td>
          <td><strong><?= htmlspecialchars($r['patient_name']) ?></strong></td>
          <td><span class="badge badge-pending"><?= $r['blood_group'] ?></span></td>
          <td><?= $r['required_units'] ?></td>
          <td><?= htmlspecialchars($r['hospital_name']) ?></td>
          <td>
            <?php
              $uc = ['Normal'=>'#6b7280','Urgent'=>'#d97706','Emergency'=>'#dc2626'];
            ?>
            <span style="color:<?= $uc[$r['urgency']] ?>;font-weight:600;"><?= $r['urgency'] ?></span>
          </td>
          <td><small><?= htmlspecialchars($r['requester_cnic']) ?></small></td>
          <td><small><?= date('d M Y', strtotime($r['created_at'])) ?></small></td>
          <td><span class="badge <?= $badge[$r['status']] ?>"><?= $r['status'] ?></span></td>
          <td>
            <button class="btn btn-outline btn-sm" onclick="openModal(<?= htmlspecialchars(json_encode($r), ENT_QUOTES) ?>)">
              Edit
            </button>
          </td>
        </tr>
        <?php endwhile; ?>
        <?php if ($count === 0): ?>
          <tr><td colspan="11" style="text-align:center;color:#9ca3af;padding:40px;">No requests found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
    </div>
  </div>
</div>

<!-- ── MODAL: View & Edit Request ── -->
<div class="modal-overlay" id="modal">
  <div class="modal">
    <button class="modal-close" onclick="closeModal()">✕</button>
    <h2 style="font-family:'Playfair Display',serif;color:var(--blood-dark);margin-bottom:4px;" id="m-title">Request #</h2>
    <p style="color:var(--gray);font-size:0.85rem;margin-bottom:24px;" id="m-date"></p>

    <!-- Detail Grid -->
    <div class="detail-grid" id="m-details"></div>

    <!-- Doctor Sheet -->
    <div id="m-doc-section" style="display:none;margin-bottom:24px;">
      <div class="section-title">Doctor's Sheet</div>
      <div class="doc-preview">
        <a id="m-doc-link" href="#" target="_blank" class="btn btn-info btn-sm">📄 View Doctor's Sheet</a>
      </div>
    </div>

    <hr class="form-divider">

    <!-- Edit Form -->
    <div class="section-title">Update Status</div>
    <form method="POST" id="edit-form">
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="request_id" id="m-request-id">
      <div class="form-row">
        <div class="form-group">
          <label>Status</label>
          <select name="status" id="m-status" class="form-control">
            <option value="Pending">Pending</option>
            <option value="Approved">Approved</option>
            <option value="Rejected">Rejected</option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label>Admin Note (visible to requester)</label>
        <textarea name="admin_note" id="m-note" class="form-control" rows="3" placeholder="Optional note for the requester…"></textarea>
      </div>
      <div style="display:flex;gap:12px;">
        <button type="submit" class="btn btn-blood">Save Changes</button>
        <button type="button" class="btn btn-outline" onclick="closeModal()">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(r) {
  document.getElementById('m-title').textContent = 'Request #' + r.id;
  document.getElementById('m-date').textContent = 'Submitted: ' + r.created_at;
  document.getElementById('m-request-id').value = r.id;
  document.getElementById('m-status').value = r.status;
  document.getElementById('m-note').value = r.admin_note || '';

  const fields = [
    ['Requester', r.requester_name],
    ['Requester CNIC', r.requester_cnic],
    ['Contact', r.contact_number],
    ['Patient Name', r.patient_name],
    ['Patient CNIC', r.patient_cnic],
    ['Patient Age', r.patient_age + ' yrs'],
    ['Blood Group', r.blood_group],
    ['Units Required', r.required_units],
    ['Urgency', r.urgency],
    ['Hospital', r.hospital_name],
    ['Doctor', r.doctor_name],
    ['Reason / Diagnosis', r.patient_reason],
  ];

  let html = '';
  fields.forEach(([label, val]) => {
    html += `<div class="detail-item"><label>${label}</label><p>${val || '—'}</p></div>`;
  });
  document.getElementById('m-details').innerHTML = html;

  // Doctor sheet
  const docSec = document.getElementById('m-doc-section');
  if (r.doctor_sheet) {
    docSec.style.display = 'block';
    document.getElementById('m-doc-link').href = 'uploads/' + r.doctor_sheet;
  } else {
    docSec.style.display = 'none';
  }

  document.getElementById('modal').classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closeModal() {
  document.getElementById('modal').classList.remove('open');
  document.body.style.overflow = '';
}

document.getElementById('modal').addEventListener('click', function(e) {
  if (e.target === this) closeModal();
});
</script>


<!-- Footer -->
<footer style="text-align:center;padding:24px;color:var(--gray);font-size:0.88rem;margin-top:40px;border-top:1px solid #f0f0f0;">
  Developed By: <strong style="color:var(--blood);">Imad Khan</strong> &nbsp;|&nbsp; Free Blood Donation System
</footer>

</body>
</html>
