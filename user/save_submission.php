<?php
session_start();
require '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
  die("Access denied");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $peso = $_SESSION['peso'] ?? '';
  $school = $_POST['school'];
  $students = $_POST['students'];
  $female_students = $_POST['female_students'];
  $parents = $_POST['parents'];
  $female_parents = $_POST['female_parents'];
  $date_conducted = $_POST['date_conducted'];

  // Handle file upload
  $upload_dir = 'uploads/';

  if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
  }

  $filename = time() . '_' . basename($_FILES['attendance']['name']);
  $target_path = $upload_dir . $filename;

  if (move_uploaded_file($_FILES['attendance']['tmp_name'], $target_path)) {
    // Adjust column names to your table's actual columns
    $stmt = $pdo->prepare("INSERT INTO submissions (peso, school, students, female_students, parents_covered, female_parents, date_conducted, file_path) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$peso, $school, $students, $female_students, $parents, $female_parents, $date_conducted, $target_path]);

    header("Location: user.php?success=1");
    exit;
  } else {
    echo "File upload failed.";
  }
}
