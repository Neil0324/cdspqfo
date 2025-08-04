<?php
session_start();
require '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
  header("Location: ../index.php");
  exit;
}

$username = $_SESSION['username'];
$peso = $_SESSION['peso'] ?? '';

// Fetch overall submission summary
$stmt = $pdo->query("
  SELECT 
    COUNT(*) AS total_schools,
    SUM(students) AS total_students,
    MAX(date_conducted) AS last_date
  FROM submissions
");
$summary = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch all submission history
$history = $pdo->query("SELECT * FROM submissions ORDER BY date_conducted DESC");
$rows = $history->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User Dashboard - CDSP Monitoring</title>
  <link rel="stylesheet" href="../css/user.css">
</head>
<body>
  <header>
    <h1>Welcome, <?= htmlspecialchars($username) ?> 👋</h1>
    <a href="../logout.php" style="color:white; text-decoration:none">Logout</a>
  </header>

  <div class="card-container">
    <div class="card">
      <h2>Total Schools</h2>
      <p><?= $summary['total_schools'] ?? 0 ?></p>
    </div>
    <div class="card">
      <h2>Total Students</h2>
      <p><?= $summary['total_students'] ?? 0 ?></p>
    </div>
    <div class="card">
      <h2>Last Submission</h2>
      <p><?= $summary['last_date'] ?? 'N/A' ?></p>
    </div>
  </div>

  <button class="btn" onclick="document.getElementById('submissionModal').style.display='flex'">➕ Submit Report</button>

  <!-- Modal Form -->
  <div id="submissionModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="this.closest('.modal').style.display='none'">&times;</span>
      <h2>Submit School Report</h2>
      <form method="POST" action="save_submission.php" enctype="multipart/form-data">
        <input type="text" name="peso" value="<?= htmlspecialchars($peso) ?>" readonly>
        <input type="text" name="school" placeholder="Name of School" required>
        <input type="number" name="students" placeholder="No. of Students" required>
        <input type="number" name="female_students" placeholder="No. of Female Students" required>
        <input type="number" name="parents" placeholder="No. of Parents Covered" required>
        <input type="number" name="female_parents" placeholder="No. of Female Parents" required>
        <input type="date" name="date_conducted" required>
        <label>Upload Attendance Sheet (PDF)</label>
        <input type="file" name="attendance" accept="application/pdf" required>
        <button type="submit">Submit</button>
      </form>
    </div>
  </div>

  <!-- Submission History -->
  <table class="table">
    <thead>
      <tr>  
        <th>School</th>
        <th>Date Conducted</th>
        <th>Total Students</th>
        <th>Parents</th>
        <th>Status</th>
        <th>Attendance</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $row): ?>
        <tr>
          <td><?= htmlspecialchars($row['school']) ?></td>
          <td><?= $row['date_conducted'] ?></td>
          <td><?= $row['students'] ?> (<?= $row['female_students'] ?>♀)</td>
          <td><?= $row['parents_covered'] ?> (<?= $row['female_parents'] ?>♀)</td>
          <td><?= $row['status'] ?? 'Submitted' ?></td>
          <td><a href="../uploads/<?= htmlspecialchars($row['file_path']) ?>" target="_blank">View</a></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</body>
</html>
