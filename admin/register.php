<?php
session_start();
require '../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  echo json_encode(['status' => 'error', 'message' => 'Access denied. Only admin can create accounts.']);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username']);
  $password = trim($_POST['password']);
  $peso     = trim($_POST['peso']);
  $role     = $_POST['role'] === 'admin' ? 'admin' : 'user';

  if (!$username || !$password || !$peso) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
    exit;
  }

  $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
  $stmt->execute([$username]);

  if ($stmt->rowCount() > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Username already exists.']);
  } else {
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $insert = $pdo->prepare("INSERT INTO users (username, password, peso, role) VALUES (?, ?, ?, ?)");
    $insert->execute([$username, $hashedPassword, $peso, $role]);
    echo json_encode(['status' => 'success', 'message' => "User '$username' successfully created."]);
  }
}
?>
