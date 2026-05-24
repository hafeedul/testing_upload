<?php
require_once 'includes/db.php';

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requester_name  = trim($_POST['requester_name'] ?? '');
    $patient_name    = trim($_POST['patient_name'] ?? '');
    $blood_group     = trim($_POST['blood_group'] ?? '');
    $required_units  = intval($_POST['required_units'] ?? 1);
    $patient_age     = intval($_POST['patient_age'] ?? 0);
    $urgency         = $_POST['urgency'] ?? 'Normal';
    $hospital_name   = trim($_POST['hospital_name'] ?? '');
    $doctor_name     = trim($_POST['doctor_name'] ?? '');
    $patient_reason  = trim($_POST['patient_reason'] ?? '');
    $patient_cnic    = trim($_POST['patient_cnic'] ?? '');
    $requester_cnic  = trim($_POST['requester_cnic'] ?? '');
    $contact_number  = trim($_POST['contact_number'] ?? '');

    // Validate required fields
    if (!$requester_name || !$patient_name || !$blood_group || !$hospital_name || !$patient_cnic || !$requester_cnic || !$contact_number) {
        $error = "Please fill in all required fields.";
    } else {
        // Check unique requester CNIC
        $check = $conn->prepare("SELECT id FROM blood_requests WHERE requester_cnic = ?");
        $check->bind_param("s", $requester_cnic);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error = "A request with this Requester CNIC already exists. Please track your existing request.";
        } else {
            // Handle doctor sheet upload
            $doctor_sheet = '';
            if (!empty($_FILES['doctor_sheet']['name'])) {
                $upload_dir = 'uploads/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                $ext = strtolower(pathinfo($_FILES['doctor_sheet']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg','jpeg','png','pdf'];
                if (!in_array($ext, $allowed)) {
                    $error = "Doctor sheet must be JPG, PNG, or PDF.";
                } elseif ($_FILES['doctor_sheet']['size'] > 5 * 1024 * 1024) {
                    $error = "File too large. Max 5MB.";
                } else {
                    $filename = 'doc_' . time() . '_' . uniqid() . '.' . $ext;
                    move_uploaded_file($_FILES['doctor_sheet']['tmp_name'], $upload_dir . $filename);
                    $doctor_sheet = $filename;
                }
            }

            if (!$error) {
                $stmt = $conn->prepare("INSERT INTO blood_requests 
                    (requester_name, patient_name, blood_group, required_units, patient_age, urgency,
                     hospital_name, doctor_name, patient_reason, patient_cnic, requester_cnic, contact_number, doctor_sheet)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
                $stmt->bind_param("sssiiisssssss",
                    $requester_name, $patient_name, $blood_group, $required_units, $patient_age, $urgency,
                    $hospital_name, $doctor_name, $patient_reason, $patient_cnic, $requester_cnic, $contact_number, $doctor_sheet);

                if ($stmt->execute()) {
                    $success = "Request submitted successfully! Use your CNIC <strong>$requester_cnic</strong> to track status.";
                } else {
                    $error = "Database error: " . $conn->error;
                }
            }
        }
    }
}

$blood_groups = ['A+','A-','B+','B-','AB+','AB-','O+','O-'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Request Blood — Blood Donation System</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body style="background:linear-gradient(135deg,#fff0f3 0%,#fdf6f0 100%);min-height:100vh;padding:40px 20px;">

<div class="page-card">
  <h2>🩸 Blood Request Form</h2>
  <p class="form-subtitle">Fill in all details carefully. Your CNIC is used to track your request.</p>

  <?php if ($success): ?>
    <div class="alert alert-success"><?= $success ?></div>
    <div style="text-align:center;margin-top:16px;">
      <a href="public_status.php" class="btn btn-dark btn-sm">Track My Request</a>
      <a href="index.php" class="btn btn-outline btn-sm" style="margin-left:8px;">Back Home</a>
    </div>
  <?php else: ?>

  <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data">

    <!-- REQUESTER INFO -->
    <div class="section-title">Requester Information</div>
    <div class="form-row">
      <div class="form-group">
        <label>Requester Name *</label>
        <input type="text" name="requester_name" class="form-control" placeholder="Your full name" value="<?= htmlspecialchars($_POST['requester_name'] ?? '') ?>" required>
      </div>
      <div class="form-group">
        <label>Requester CNIC * <small style="color:var(--blood)">(Unique — for tracking)</small></label>
        <input type="text" name="requester_cnic" class="form-control" placeholder="e.g. 37405-1234567-1" value="<?= htmlspecialchars($_POST['requester_cnic'] ?? '') ?>" required>
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>Contact Number *</label>
        <input type="text" name="contact_number" class="form-control" placeholder="e.g. 03XX-XXXXXXX" value="<?= htmlspecialchars($_POST['contact_number'] ?? '') ?>" required>
      </div>
    </div>

    <hr class="form-divider">

    <!-- PATIENT INFO -->
    <div class="section-title">Patient Information</div>
    <div class="form-row">
      <div class="form-group">
        <label>Patient Name *</label>
        <input type="text" name="patient_name" class="form-control" placeholder="Patient full name" value="<?= htmlspecialchars($_POST['patient_name'] ?? '') ?>" required>
      </div>
      <div class="form-group">
        <label>Patient CNIC *</label>
        <input type="text" name="patient_cnic" class="form-control" placeholder="Patient CNIC" value="<?= htmlspecialchars($_POST['patient_cnic'] ?? '') ?>" required>
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>Patient Age</label>
        <input type="number" name="patient_age" class="form-control" placeholder="Age in years" value="<?= htmlspecialchars($_POST['patient_age'] ?? '') ?>" min="1" max="120">
      </div>
      <div class="form-group">
        <label>Patient Reason / Diagnosis</label>
        <input type="text" name="patient_reason" class="form-control" placeholder="e.g. Surgery, Accident, Thalassemia" value="<?= htmlspecialchars($_POST['patient_reason'] ?? '') ?>">
      </div>
    </div>

    <hr class="form-divider">

    <!-- BLOOD INFO -->
    <div class="section-title">Blood Requirements</div>
    <div class="form-row">
      <div class="form-group">
        <label>Blood Group *</label>
        <select name="blood_group" class="form-control" required>
          <option value="">Select Blood Group</option>
          <?php foreach($blood_groups as $bg): ?>
            <option value="<?= $bg ?>" <?= (($_POST['blood_group'] ?? '') == $bg) ? 'selected' : '' ?>><?= $bg ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Required Units *</label>
        <input type="number" name="required_units" class="form-control" placeholder="Number of units" value="<?= htmlspecialchars($_POST['required_units'] ?? '1') ?>" min="1" max="20" required>
      </div>
    </div>
    <div class="form-group">
      <label>Urgency Level</label>
      <select name="urgency" class="form-control">
        <option value="Normal" <?= (($_POST['urgency'] ?? 'Normal') == 'Normal') ? 'selected' : '' ?>>Normal</option>
        <option value="Urgent" <?= (($_POST['urgency'] ?? '') == 'Urgent') ? 'selected' : '' ?>>Urgent</option>
        <option value="Emergency" <?= (($_POST['urgency'] ?? '') == 'Emergency') ? 'selected' : '' ?>>Emergency</option>
      </select>
    </div>

    <hr class="form-divider">

    <!-- HOSPITAL / DOCTOR -->
    <div class="section-title">Hospital & Doctor Details</div>
    <div class="form-row">
      <div class="form-group">
        <label>Hospital Name *</label>
        <input type="text" name="hospital_name" class="form-control" placeholder="e.g. PIMS, Shifa, CMH" value="<?= htmlspecialchars($_POST['hospital_name'] ?? '') ?>" required>
      </div>
      <div class="form-group">
        <label>Doctor Name</label>
        <input type="text" name="doctor_name" class="form-control" placeholder="Attending doctor" value="<?= htmlspecialchars($_POST['doctor_name'] ?? '') ?>">
      </div>
    </div>

    <hr class="form-divider">

    <!-- DOCTOR SHEET UPLOAD -->
    <div class="section-title">Doctor's Recommendation Sheet</div>
    <p style="font-size:0.88rem;color:var(--gray);margin-bottom:12px;">
      Upload the doctor's prescription/stamp sheet. Accepted: JPG, PNG, PDF (max 5MB)
    </p>
    <div class="doc-upload-area" onclick="document.getElementById('doc_file').click()">
      <input type="file" id="doc_file" name="doctor_sheet" accept=".jpg,.jpeg,.png,.pdf" onchange="showFileName(this)">
      <div class="upload-icon">📄</div>
      <p><strong>Click to upload</strong> doctor's sheet</p>
      <p id="file_name" style="color:var(--blood);font-weight:600;margin-top:8px;"></p>
    </div>

    <hr class="form-divider">

    <button type="submit" class="btn btn-blood" style="width:100%;font-size:1.1rem;padding:16px;">
      Submit Blood Request
    </button>

    <p style="text-align:center;margin-top:16px;font-size:0.85rem;">
      Already submitted? <a href="public_status.php" style="color:var(--blood);">Track your request →</a>
    </p>
  </form>

  <?php endif; ?>
</div>

<script>
function showFileName(input) {
  const el = document.getElementById('file_name');
  el.textContent = input.files[0] ? '📎 ' + input.files[0].name : '';
}
</script>


<!-- Footer -->
<footer style="text-align:center;padding:24px;color:#9ca3af;font-size:0.88rem;margin-top:40px;border-top:1px solid #f0f0f0;">
  Developed By: <strong style="color:#C0152A;">Imad Khan</strong> &nbsp;|&nbsp; Free Blood Donation System
</footer>

</body>
</html>
