<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
  header("Location: index.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User Dashboard</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="login-box">
    <h2>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></h2>
    <p>This is your user dashboard.</p>
    <a href="logout.php">🔓 Logout</a>
  </div>
</body>
</html>
