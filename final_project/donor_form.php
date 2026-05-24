<?php
require_once 'includes/auth.php';
requireLogin();
require_once 'includes/db.php';

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name'] ?? '');
    $blood_group = trim($_POST['blood_group'] ?? '');
    $hospital    = trim($_POST['hospital'] ?? '');
    $contact     = trim($_POST['contact'] ?? '');
    $city        = trim($_POST['city'] ?? '');
    $cnic        = trim($_POST['cnic'] ?? '');
    $status      = $_POST['status'] ?? 'Available';

    if (!$name || !$blood_group || !$contact) {
        $error = "Name, blood group and contact are required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO donors (name, blood_group, hospital, contact, city, cnic, status) VALUES (?,?,?,?,?,?,?)");
        $stmt->bind_param("sssssss", $name, $blood_group, $hospital, $contact, $city, $cnic, $status);
        if ($stmt->execute()) {
            $success = "Donor <strong>" . htmlspecialchars($name) . "</strong> added successfully!";
        } else {
            $error = "Error: " . $conn->error;
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
<title>Add Donor — Admin</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body style="background:var(--cream);min-height:100vh;">

<nav class="topbar">
  <div class="topbar-brand">🩸 <span>Blood Bank</span></div>
  <div class="topbar-nav">
    <a href="dashboard.php">Dashboard</a>
    <a href="donor_form.php" class="active">Add Donor</a>
    <a href="view_donors.php">Donors</a>
    <a href="view_requests.php">Requests</a>
    <a href="settings.php">⚙️ Settings</a>
    <a href="settings.php">⚙️ Settings</a>
    <a href="logout.php" class="logout">Logout</a>
  </div>
</nav>

<div class="main-content">
  <div class="page-card">
    <h2>Add New Donor</h2>
    <p class="form-subtitle">Register a blood donor in the system.</p>

    <?php if ($success): ?>
      <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-row">
        <div class="form-group">
          <label>Donor Name *</label>
          <input type="text" name="name" class="form-control" placeholder="Full name" required>
        </div>
        <div class="form-group">
          <label>Blood Group *</label>
          <select name="blood_group" class="form-control" required>
            <option value="">Select</option>
            <?php foreach($blood_groups as $bg): ?>
              <option value="<?= $bg ?>"><?= $bg ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Contact Number *</label>
          <input type="text" name="contact" class="form-control" placeholder="03XX-XXXXXXX" required>
        </div>
        <div class="form-group">
          <label>CNIC</label>
          <input type="text" name="cnic" class="form-control" placeholder="XXXXX-XXXXXXX-X">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Hospital / Organization</label>
          <input type="text" name="hospital" class="form-control" placeholder="Hospital or work place">
        </div>
        <div class="form-group">
          <label>City</label>
          <input type="text" name="city" class="form-control" placeholder="City">
        </div>
      </div>
      <div class="form-group">
        <label>Status</label>
        <select name="status" class="form-control">
          <option value="Available">Available</option>
          <option value="Unavailable">Unavailable</option>
        </select>
      </div>
      <button type="submit" class="btn btn-blood" style="width:100%;">Add Donor</button>
    </form>
  </div>
</div>


<!-- Footer -->
<footer style="text-align:center;padding:24px;color:var(--gray);font-size:0.88rem;margin-top:40px;border-top:1px solid #f0f0f0;">
  Developed By: <strong style="color:var(--blood);">Imad Khan</strong> &nbsp;|&nbsp; Free Blood Donation System
</footer>

</body>
</html>
