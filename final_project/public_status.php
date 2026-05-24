<?php
require_once 'includes/db.php';

$result = null;
$error  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['cnic'])) {
    $cnic = trim($_POST['cnic'] ?? $_GET['cnic'] ?? '');
    if ($cnic) {
        $stmt = $conn->prepare("SELECT * FROM blood_requests WHERE requester_cnic = ?");
        $stmt->bind_param("s", $cnic);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        if (!$result) {
            $error = "No request found with CNIC: <strong>" . htmlspecialchars($cnic) . "</strong>";
        }
    } else {
        $error = "Please enter your CNIC.";
    }
}

$status_colors = [
    'Pending'  => '#d97706',
    'Approved' => '#16a34a',
    'Rejected' => '#dc2626',
];
$status_icons = [
    'Pending'  => '⏳',
    'Approved' => '✅',
    'Rejected' => '❌',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Track Request — Blood Donation System</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="track-page">
  <div class="track-card">
    <div style="font-size:2.5rem;margin-bottom:12px;">🔍</div>
    <h2>Track Your Request</h2>
    <p style="color:var(--gray);margin-bottom:32px;">Enter your CNIC to check blood request status</p>

    <form method="POST">
      <div class="form-group">
        <label>Your CNIC (Requester)</label>
        <input type="text" name="cnic" class="form-control" 
               placeholder="e.g. 37405-1234567-1"
               value="<?= htmlspecialchars($_POST['cnic'] ?? $_GET['cnic'] ?? '') ?>" required>
      </div>
      <button type="submit" class="btn btn-blood" style="width:100%;">Check Status</button>
    </form>

    <?php if ($error): ?>
      <div class="alert alert-danger" style="margin-top:20px;"><?= $error ?></div>
    <?php endif; ?>

    <?php if ($result): ?>
      <div class="result-box" style="border-color:<?= $status_colors[$result['status']] ?>;">
        <div style="text-align:center;margin-bottom:20px;">
          <div style="font-size:2.5rem;"><?= $status_icons[$result['status']] ?></div>
          <div style="font-size:1.4rem;font-weight:700;color:<?= $status_colors[$result['status']] ?>;margin-top:4px;">
            <?= $result['status'] ?>
          </div>
          <div style="font-size:0.85rem;color:var(--gray);">Submitted: <?= date('d M Y, h:i A', strtotime($result['created_at'])) ?></div>
        </div>

        <div class="result-row">
          <span class="label">Request #</span>
          <span class="value"><?= $result['id'] ?></span>
        </div>
        <div class="result-row">
          <span class="label">Patient Name</span>
          <span class="value"><?= htmlspecialchars($result['patient_name']) ?></span>
        </div>
        <div class="result-row">
          <span class="label">Blood Group</span>
          <span class="value"><?= htmlspecialchars($result['blood_group']) ?></span>
        </div>
        <div class="result-row">
          <span class="label">Units Required</span>
          <span class="value"><?= $result['required_units'] ?></span>
        </div>
        <div class="result-row">
          <span class="label">Hospital</span>
          <span class="value"><?= htmlspecialchars($result['hospital_name']) ?></span>
        </div>
        <div class="result-row">
          <span class="label">Urgency</span>
          <span class="value"><?= $result['urgency'] ?></span>
        </div>
        <div class="result-row">
          <span class="label">Requester</span>
          <span class="value"><?= htmlspecialchars($result['requester_name']) ?></span>
        </div>
        <div class="result-row">
          <span class="label">Contact</span>
          <span class="value"><?= htmlspecialchars($result['contact_number']) ?></span>
        </div>
        <?php if ($result['admin_note']): ?>
        <div class="result-row">
          <span class="label">Admin Note</span>
          <span class="value" style="color:var(--blood);"><?= htmlspecialchars($result['admin_note']) ?></span>
        </div>
        <?php endif; ?>
        <div class="result-row">
          <span class="label">Last Updated</span>
          <span class="value"><?= date('d M Y, h:i A', strtotime($result['updated_at'])) ?></span>
        </div>
      </div>
    <?php endif; ?>

    <p style="text-align:center;margin-top:24px;font-size:0.85rem;">
      <a href="index.php" style="color:var(--blood);text-decoration:none;">← Back to Home</a>
    </p>
  </div>
</div>


<!-- Footer -->
<footer style="text-align:center;padding:24px;color:#9ca3af;font-size:0.88rem;margin-top:40px;border-top:1px solid #f0f0f0;">
  Developed By: <strong style="color:#C0152A;">Imad Khan</strong> &nbsp;|&nbsp; Free Blood Donation System
</footer>

</body>
</html>
