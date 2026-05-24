<?php
define('DB_HOST', 'sql309.infinityfree.com');
define('DB_USER', 'if0_41948295');
define('DB_PASS', 'QIkzO73oxgA');
define('DB_NAME', 'if0_41948295_bloodbank');
mysqli_report(MYSQLI_REPORT_OFF);
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("<h2>Database Error</h2><p>" . htmlspecialchars($conn->connect_error) . "</p>");
}
$conn->set_charset("utf8mb4");
?>
