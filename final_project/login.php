<?php
session_start();
require_once 'includes/db.php';

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username && $password) {
        $stmt = $conn->prepare("SELECT id, username, password FROM admin WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();

        if ($admin && password_verify($password, $admin['password'])) {
            // ── Start fresh session ──
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id']        = $admin['id'];
            $_SESSION['admin_username']  = $admin['username'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}

// Messages after settings change
$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login — Blood Bank</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="login-page">
  <div class="login-card">
    <div style="text-align:center;margin-bottom:20px;">
      <div style="width:60px;height:60px;background:linear-gradient(135deg,#C0152A,#ff4d6d);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:26px;margin:0 auto 16px;">🩸</div>
    </div>
    <h2>Blood Bank Login</h2>
    <p class="subtitle">Admin access only</p>

    <?php if ($msg === 'updated'): ?>
      <div class="alert alert-success">✅ Password changed! Login with your new password.</div>
    <?php elseif ($msg === 'username_updated'): ?>
      <div class="alert alert-success">✅ Username changed! Login with your new username.</div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" class="form-control"
               placeholder="Enter username"
               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" class="form-control"
               placeholder="Enter password" required>
      </div>
      <button type="submit" class="btn btn-blood" style="width:100%;margin-top:8px;">Login</button>
    </form>

    <p style="text-align:center;margin-top:20px;font-size:0.85rem;">
      <a href="index.php" style="color:#C0152A;text-decoration:none;">← Back to Home</a>
    </p>
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
