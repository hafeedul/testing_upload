<?php
require_once 'includes/auth.php';
requireLogin();
require_once 'includes/db.php';

$total_donors   = $conn->query("SELECT COUNT(*) as c FROM donors")->fetch_assoc()['c'];
$total_requests = $conn->query("SELECT COUNT(*) as c FROM blood_requests")->fetch_assoc()['c'];
$pending        = $conn->query("SELECT COUNT(*) as c FROM blood_requests WHERE status='Pending'")->fetch_assoc()['c'];
$approved       = $conn->query("SELECT COUNT(*) as c FROM blood_requests WHERE status='Approved'")->fetch_assoc()['c'];

// Recent 5 requests
$recent = $conn->query("SELECT * FROM blood_requests ORDER BY created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard — Blood Bank</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<nav class="topbar">
  <div class="topbar-brand">🩸 <span>Blood Bank</span></div>
  <div class="topbar-nav">
    <a href="dashboard.php" class="active">Dashboard</a>
    <a href="donor_form.php">Add Donor</a>
    <a href="view_donors.php">Donors</a>
    <a href="view_requests.php">Requests</a>
    <a href="settings.php">⚙️ Settings</a>
    <a href="settings.php">⚙️ Settings</a>
    <a href="logout.php" class="logout">Logout</a>
  </div>
</nav>

<div class="main-content">
  <div class="page-header">
    <h2>Admin Dashboard</h2>
    <p>Welcome back, <?= htmlspecialchars($_SESSION['admin_username']) ?>! Here's your overview.</p>
  </div>

  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-number"><?= $total_donors ?></div>
      <div class="stat-label">Total Donors</div>
    </div>
    <div class="stat-card blue">
      <div class="stat-number"><?= $total_requests ?></div>
      <div class="stat-label">Total Requests</div>
    </div>
    <div class="stat-card orange">
      <div class="stat-number"><?= $pending ?></div>
      <div class="stat-label">Pending</div>
    </div>
    <div class="stat-card green">
      <div class="stat-number"><?= $approved ?></div>
      <div class="stat-label">Approved</div>
    </div>
  </div>

  <div class="action-bar">
    <a href="donor_form.php" class="btn btn-blood btn-sm">+ Add Donor</a>
    <a href="view_donors.php" class="btn btn-success btn-sm">👥 View Donors</a>
    <a href="view_requests.php" class="btn btn-info btn-sm">📋 View Requests</a>
    <a href="settings.php" class="btn btn-dark btn-sm">⚙️ Settings</a>
    <a href="logout.php" class="btn btn-warning btn-sm">Logout</a>
  </div>

  <div class="table-card">
    <div class="table-card-header">
      <h3>Recent Blood Requests</h3>
      <a href="view_requests.php" class="btn btn-outline btn-sm">View All</a>
    </div>
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Patient</th>
          <th>Blood</th>
          <th>Hospital</th>
          <th>Urgency</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php while($r = $recent->fetch_assoc()): ?>
        <tr>
          <td><?= $r['id'] ?></td>
          <td><strong><?= htmlspecialchars($r['patient_name']) ?></strong><br>
              <small style="color:#9ca3af;"><?= htmlspecialchars($r['requester_name']) ?></small></td>
          <td><span class="badge badge-pending"><?= htmlspecialchars($r['blood_group']) ?></span></td>
          <td><?= htmlspecialchars($r['hospital_name']) ?></td>
          <td><?= $r['urgency'] ?></td>
          <td>
            <?php
              $cls = ['Pending'=>'badge-pending','Approved'=>'badge-approved','Rejected'=>'badge-rejected'];
            ?>
            <span class="badge <?= $cls[$r['status']] ?>"><?= $r['status'] ?></span>
          </td>
          <td><a href="view_requests.php?id=<?= $r['id'] ?>" class="btn btn-outline btn-sm">View</a></td>
        </tr>
        <?php endwhile; ?>
        <?php if ($total_requests == 0): ?>
        <tr><td colspan="7" style="text-align:center;color:#9ca3af;padding:32px;">No requests yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>


<!-- Footer -->
<footer style="text-align:center;padding:24px;color:var(--gray);font-size:0.88rem;margin-top:40px;border-top:1px solid #f0f0f0;">
  Developed By: <strong style="color:var(--blood);">Imad Khan</strong> &nbsp;|&nbsp; Free Blood Donation System
</footer>

</body>
</html>
