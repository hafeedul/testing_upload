<?php
require_once 'includes/db.php';
$pass = password_hash('imad2007', PASSWORD_DEFAULT);
$result = $conn->query("UPDATE admin SET username='imad', password='$pass' WHERE id=1");
if ($result) {
    echo "<h2 style='color:green;'>✅ Done!</h2><p>Username: <b>imad</b><br>Password: <b>imad2007</b></p><p style='color:red;'><b>⚠️ DELETE THIS FILE NOW!</b></p>";
} else {
    // Try insert if no admin exists
    $conn->query("INSERT INTO admin (username, password) VALUES ('imad', '$pass')");
    echo "<h2 style='color:green;'>✅ Admin Created!</h2><p>Username: <b>imad</b><br>Password: <b>imad2007</b></p><p style='color:red;'><b>⚠️ DELETE THIS FILE NOW!</b></p>";
}
?>
