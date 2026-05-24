<?php
require_once 'includes/auth.php';
requireLogin();
require_once 'includes/db.php';

$message = '';

// Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM donors WHERE id=$id");
    $message = "Donor deleted.";
}

// Toggle status
if (isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    $conn->query("UPDATE donors SET status = IF(status='Available','Unavailable','Available') WHERE id=$id");
    header("Location: view_donors.php");
    exit();
}

$search = trim($_GET['search'] ?? '');
$bg_filter = $_GET['blood'] ?? '';

$where = "WHERE 1=1";
if ($search) $where .= " AND (name LIKE '%".  $conn->real_escape_string($search) ."%' OR city LIKE '%". $conn->real_escape_string($search) ."%')";
if ($bg_filter) $where .= " AND blood_group='". $conn->real_escape_string($bg_filter) ."'";

$donors = $conn->query("SELECT * FROM donors $where ORDER BY created_at DESC");
$blood_groups = ['A+','A-','B+','B-','AB+','AB-','O+','O-'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>View Donors — Admin</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body style="background:var(--cream);min-height:100vh;">

<nav class="topbar">
  <div class="topbar-brand">🩸 <span>Blood Bank</span></div>
  <div class="topbar-nav">
    <a href="dashboard.php">Dashboard</a>
    <a href="donor_form.php">Add Donor</a>
    <a href="view_donors.php" class="active">Donors</a>
    <a href="view_requests.php">Requests</a>
    <a href="settings.php">⚙️ Settings</a>
    <a href="settings.php">⚙️ Settings</a>
    <a href="logout.php" class="logout">Logout</a>
  </div>
</nav>

<div class="main-content">
  <div class="page-header">
    <h2>All Donors</h2>
    <p>Manage registered blood donors.</p>
  </div>

  <?php if ($message): ?>
    <div class="alert alert-info"><?= $message ?></div>
  <?php endif; ?>

  <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:24px;align-items:flex-end;">
    <div style="flex:1;min-width:200px;">
      <label style="font-size:0.8rem;font-weight:600;display:block;margin-bottom:6px;">Search</label>
      <input type="text" name="search" class="form-control" placeholder="Name or city…" value="<?= htmlspecialchars($search) ?>">
    </div>
    <div>
      <label style="font-size:0.8rem;font-weight:600;display:block;margin-bottom:6px;">Blood Group</label>
      <select name="blood" class="form-control" style="width:140px;">
        <option value="">All Groups</option>
        <?php foreach($blood_groups as $bg): ?>
          <option value="<?= $bg ?>" <?= $bg_filter==$bg?'selected':'' ?>><?= $bg ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button type="submit" class="btn btn-blood btn-sm">Filter</button>
    <a href="view_donors.php" class="btn btn-outline btn-sm">Clear</a>
    <a href="donor_form.php" class="btn btn-success btn-sm">+ Add Donor</a>
  </form>

  <div class="table-card">
    <div style="overflow-x:auto;">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Name</th>
          <th>Blood</th>
          <th>Hospital</th>
          <th>Contact</th>
          <th>City</th>
          <th>CNIC</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php $count=0; while($d = $donors->fetch_assoc()): $count++; ?>
        <tr>
          <td><?= $d['id'] ?></td>
          <td><strong><?= htmlspecialchars($d['name']) ?></strong></td>
          <td><span class="badge badge-pending"><?= $d['blood_group'] ?></span></td>
          <td><?= htmlspecialchars($d['hospital']) ?></td>
          <td><?= htmlspecialchars($d['contact']) ?></td>
          <td><?= htmlspecialchars($d['city']) ?></td>
          <td><small><?= htmlspecialchars($d['cnic']) ?></small></td>
          <td>
            <span class="badge <?= $d['status']=='Available' ? 'badge-approved' : 'badge-rejected' ?>">
              <?= $d['status'] ?>
            </span>
          </td>
          <td style="display:flex;gap:6px;">
            <a href="view_donors.php?toggle=<?= $d['id'] ?>" class="btn btn-warning btn-sm">Toggle</a>
            <a href="view_donors.php?delete=<?= $d['id'] ?>" class="btn btn-sm" 
               style="background:#fee2e2;color:#991b1b;"
               onclick="return confirm('Delete this donor?')">Delete</a>
          </td>
        </tr>
        <?php endwhile; ?>
        <?php if($count===0): ?>
          <tr><td colspan="9" style="text-align:center;color:#9ca3af;padding:40px;">No donors found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
    </div>
  </div>
</div>


<!-- Footer -->
<footer style="text-align:center;padding:24px;color:var(--gray);font-size:0.88rem;margin-top:40px;border-top:1px solid #f0f0f0;">
  Developed By: <strong style="color:var(--blood);">Imad Khan</strong> &nbsp;|&nbsp; Free Blood Donation System
</footer>

</body>
</html>
