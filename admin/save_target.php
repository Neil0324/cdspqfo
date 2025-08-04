<?php
session_start();
require '../db.php';

// Only allow admins
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $peso = trim($_POST['peso'] ?? '');
    $school = trim($_POST['school'] ?? '');
    $students = intval($_POST['students'] ?? 0);

    // Basic validation
    if ($peso === '' || $school === '' || $students <= 0) {
        $_SESSION['error'] = "All fields are required and number of students must be greater than zero.";
        header("Location: admin.php");
        exit;
    }

    // Insert into targets table
    $stmt = $pdo->prepare("INSERT INTO targets (peso, school, students) VALUES (?, ?, ?)");
    try {
        $stmt->execute([$peso, $school, $students]);
        $_SESSION['success'] = "Target saved successfully.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error saving target: " . $e->getMessage();
    }

    header("Location: admin.php");
    exit;
} else {
    // If not POST request, redirect back
    header("Location: admin.php");
    exit;
}
