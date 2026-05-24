<?php
require_once 'includes/auth.php';
requireLogin();
require_once 'includes/db.php';

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = trim($_POST['current_password'] ?? '');
    $new_username     = trim($_POST['new_username'] ?? '');
    $new_password     = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    if (!$current_password || !$new_username) {
        $error = "Current password and new username are required.";
    } elseif ($new_password && strlen($new_password) < 6) {
        $error = "New password must be at least 6 characters.";
    } elseif ($new_password && $new_password !== $confirm_password) {
        $error = "New password and confirm password do not match.";
    } else {
        $id   = $_SESSION['admin_id'];
        $stmt = $conn->prepare("SELECT password FROM admin WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row  = $stmt->get_result()->fetch_assoc();

        if (!$row || !password_verify($current_password, $row['password'])) {
            $error = "Current password is incorrect.";
        } else {
            $chk = $conn->prepare("SELECT id FROM admin WHERE username = ? AND id != ?");
            $chk->bind_param("si", $new_username, $id);
            $chk->execute();
            if ($chk->get_result()->num_rows > 0) {
                $error = "That username is already taken.";
            } else {
                if ($new_password) {
                    $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt2  = $conn->prepare("UPDATE admin SET username = ?, password = ? WHERE id = ?");
                    $stmt2->bind_param("ssi", $new_username, $hashed, $id);
                } else {
                    $stmt2 = $conn->prepare("UPDATE admin SET username = ? WHERE id = ?");
                    $stmt2->bind_param("si", $new_username, $id);
                }

                if ($stmt2->execute()) {
                    // ── Completely destroy old session so old password never works ──
                    session_unset();
                    session_destroy();
                    session_start();
                    session_regenerate_id(true);

                    if ($new_password) {
                        header("Location: login.php?msg=updated");
                        exit();
                    } else {
                        // Only username changed - re-login with new username
                        header("Location: login.php?msg=username_updated");
                        exit();
                    }
                } else {
                    $error = "Database error: " . $conn->error;
                }
            }
        }
    }
}

$id           = $_SESSION['admin_id'];
$cur          = $conn->query("SELECT username FROM admin WHERE id=$id")->fetch_assoc();
$cur_username = $cur['username'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Settings — Blood Bank Admin</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body style="background:var(--cream);min-height:100vh;">

<nav class="topbar">
  <div class="topbar-brand">🩸 <span>Blood Bank</span></div>
  <div class="topbar-nav">
    <a href="dashboard.php">Dashboard</a>
    <a href="donor_form.php">Add Donor</a>
    <a href="view_donors.php">Donors</a>
    <a href="view_requests.php">Requests</a>
    <a href="settings.php" class="active">⚙️ Settings</a>
    <a href="logout.php" class="logout">Logout</a>
  </div>
</nav>

<div class="main-content">
  <div style="margin-bottom:20px;">
    <a href="dashboard.php" style="color:var(--blood);text-decoration:none;font-weight:600;font-size:0.95rem;">← Back to Dashboard</a>
  </div>

  <div class="page-card">
    <h2>⚙️ Account Settings</h2>
    <p class="form-subtitle">Change your admin username and password.</p>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div style="background:#f9fafb;border-radius:12px;padding:16px;margin-bottom:28px;border-left:4px solid var(--blood);">
      <p style="font-size:0.85rem;color:var(--gray);margin-bottom:4px;">Currently logged in as</p>
      <p style="font-size:1.2rem;font-weight:700;color:var(--dark);">👤 <?= htmlspecialchars($cur_username) ?></p>
    </div>

    <form method="POST">

      <div class="section-title">Verify Identity</div>
      <div class="form-group">
        <label>Current Password <span style="color:var(--blood);">*</span></label>
        <input type="password" name="current_password" class="form-control"
               placeholder="Enter your current password" required>
        <small style="color:var(--gray);font-size:0.82rem;">Required to save any changes</small>
      </div>

      <hr class="form-divider">

      <div class="section-title">Change Username</div>
      <div class="form-group">
        <label>New Username <span style="color:var(--blood);">*</span></label>
        <input type="text" name="new_username" class="form-control"
               placeholder="Enter new username"
               value="<?= htmlspecialchars($_POST['new_username'] ?? $cur_username) ?>" required>
        <small style="color:var(--gray);font-size:0.82rem;">
          Current: <strong><?= htmlspecialchars($cur_username) ?></strong> — leave same if only changing password
        </small>
      </div>

      <hr class="form-divider">

      <div class="section-title">
        Change Password
        <span style="font-size:0.75rem;font-weight:400;color:var(--gray);text-transform:none;">(leave blank to keep current)</span>
      </div>
      <div class="form-group">
        <label>New Password</label>
        <input type="password" name="new_password" class="form-control"
               placeholder="Minimum 6 characters">
      </div>
      <div class="form-group">
        <label>Confirm New Password</label>
        <input type="password" name="confirm_password" class="form-control"
               placeholder="Repeat new password">
      </div>

      <button type="submit" class="btn btn-blood" style="width:100%;font-size:1rem;padding:16px;margin-top:8px;">
        💾 Save Changes
      </button>
    </form>
  </div>
</div>

<!-- Footer -->
<footer style="text-align:center;padding:28px 20px;color:var(--gray);font-size:0.88rem;margin-top:40px;border-top:1px solid #f0f0f0;background:#fff;">
  <p style="margin-bottom:6px;">Developed By: <strong style="color:#C0152A;">Imad Khan</strong></p>
  <p style="margin-bottom:4px;">📞 <a href="tel:+923436001015" style="color:#C0152A;text-decoration:none;">+92-3436001015</a> &nbsp;|&nbsp;
     ✉️ <a href="mailto:imadkhan.vx@gmail.com" style="color:#C0152A;text-decoration:none;">imadkhan.vx@gmail.com</a></p>
  <p style="color:#9ca3af;font-size:0.82rem;">Working Hours: Mon–Fri &nbsp;|&nbsp; 9:00 AM – 4:00 PM &nbsp;|&nbsp; Saturday Off</p>
</footer>

</body>
</html>
