<?php
session_start();
require 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = trim($_POST['password'] ?? '');

  if (empty($username) || empty($password)) {
    $error = "Please fill in both fields.";
  } else {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
      $_SESSION['username'] = $user['username'];
      $_SESSION['role'] = $user['role'];
      $_SESSION['peso'] = $user['peso']; // ✅ Add this line

      if ($user['role'] === 'admin') {
        header("Location: admin/admin.php");
        exit;
      } else {
        header("Location: user/user.php");
        exit;
      }
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>CDSP Monitoring - Login</title>
  <link rel="stylesheet" href="css/index.css">
  <script>
    function validateForm() {
      const username = document.forms["loginForm"]["username"].value.trim();
      const password = document.forms["loginForm"]["password"].value.trim();
      if (!username || !password) {
        alert("Both username and password are required.");
        return false;
      }
      return true;
    }
  </script>
</head>
<body>
  <div class="login-box">
    <h2>CDSP Monitoring</h2>
    <form name="loginForm" method="POST" onsubmit="return validateForm();">
      <input type="text" name="username" placeholder="Username" autocomplete="off" />
      <input type="password" name="password" placeholder="Password" />
      <button type="submit">Login</button>
      <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
    </form>
  </div>

  <div id="toast" class="toast"></div>

<script>
function showToast(message, isError = false) {
  const toast = document.getElementById("toast");
  toast.textContent = message;
  toast.style.backgroundColor = isError ? "#d9534f" : "#28a745";
  toast.className = "toast show";
  setTimeout(() => {
    toast.className = toast.className.replace("show", "");
  }, 3000);
}
</script>
</body>
</html>
