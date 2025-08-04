<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  die("Access denied");
}

if (!isset($_GET['file'])) {
  die("File not specified.");
}

$filepath = '../uploads/' . basename($_GET['file']);

if (file_exists($filepath)) {
  header('Content-Type: application/pdf');
  header('Content-Disposition: inline; filename="' . basename($filepath) . '"');
  readfile($filepath);
  exit;
} else {
  echo "File not found.";
}
