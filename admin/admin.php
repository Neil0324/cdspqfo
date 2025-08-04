<?php
session_start();
require '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../index.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Admin Dashboard - CDSP Monitoring</title>

  <!-- Your CSS -->
  <link rel="stylesheet" href="../css/admin.css" />

  <!-- DataTables CSS CDN -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">

 </head>
<body>

  <aside class="sidebar">
    <ul class="menu">
      <li><a href="admin.php" class="active">Dashboard</a></li>
      <li><a href="#" id="openTargetModal">Input Target</a></li>
      <li><a href="#" id="openRegisterModal">Create Account</a></li>
    </ul>
    <a href="../logout.php" class="logout">Logout</a>
  </aside>

  <section class="main-content">
    <h1>Welcome, Admin <?= htmlspecialchars($_SESSION['username']) ?>!</h1>

    <div class="cards-container">
      <?php
        $peso_list = ['Aglipay', 'Cabarroguis', 'Diffun', 'Maddela', 'Nagtipunan', 'Saguday'];
        foreach ($peso_list as $peso) {
          // Get target count
          $stmtTarget = $pdo->prepare("SELECT COUNT(*) FROM targets WHERE peso = ?");
          $stmtTarget->execute([$peso]);
          $target = $stmtTarget->fetchColumn();

          // Get conducted count
          $stmtConducted = $pdo->prepare("SELECT COUNT(DISTINCT school) FROM submissions WHERE peso = ?");
          $stmtConducted->execute([$peso]);
          $conducted = $stmtConducted->fetchColumn();

          // Calculate percentage
          $percentage = ($target > 0) ? round(($conducted / $target) * 100) : 0;
      ?>
        <div class="card">
          <h3><?= $peso ?></h3>
          <p><strong>Schools Conducted:</strong> <?= $conducted ?></p>
          <p><strong>Completion:</strong> <?= $percentage ?>%</p>
        </div>
      <?php } ?>
    </div>

    <!-- DataTable below cards -->
    <h2>All Submissions</h2>
    <table id="submissionsTable" class="display" style="width:100%">
      <thead>
        <tr>
          <th>PESO</th>
          <th>School</th>
          <th>No. of Students</th>
          <th>Date Conducted</th>
          <th>Status</th>
          <th>Attendance File</th>
        </tr>
      </thead>
      <tbody>
        <?php
        // Fetch all submissions
       $stmt = $pdo->query("SELECT peso, school, students, date_conducted, status, attendance_path FROM submissions ORDER BY date_conducted DESC");
          while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['peso']) . "</td>";
            echo "<td>" . htmlspecialchars($row['school']) . "</td>";
            echo "<td>" . (int)$row['students'] . "</td>";
            echo "<td>" . htmlspecialchars($row['date_conducted']) . "</td>";
            echo "<td>" . htmlspecialchars($row['status'] ?? 'Submitted') . "</td>";

            // Correct usage of attendance_path
            $fileName = basename($row['attendance_path']);
            $encodedName = urlencode($fileName);
            echo "<td><a href='view_file.php?file=$encodedName' target='_blank'>View PDF</a></td>";
            echo "</tr>";
          }
        ?>
      </tbody>
    </table>

    <!-- Input Target Modal -->
    <div id="targetModal" class="modal">
      <div class="modal-content">
        <span class="close" onclick="closeModal('targetModal')">&times;</span>
        <h2>Input School</h2>
        <form method="POST" action="save_target.php">
          <label for="peso">Select PESO:</label>
          <select name="peso" id="peso" required>
            <option value="">-- Choose PESO --</option>
            <option value="Select All">Select All</option>
            <option value="Aglipay">Aglipay</option>
            <option value="Cabarroguis">Cabarroguis</option>
            <option value="Diffun">Diffun</option>
            <option value="Maddela">Maddela</option>
            <option value="Nagtipunan">Nagtipunan</option>
            <option value="Saguday">Saguday</option>
          </select>

          <label for="school">Name of School:</label>
          <input type="text" id="school" name="school" required>

          <label for="students">No. of Students:</label>
          <input type="number" id="students" name="students" min="1" required>

          <button type="submit">Save Target</button>
        </form>
      </div>
    </div>

    <!-- Register Modal -->
    <div id="registerModal" class="modal">
      <div class="modal-content">
        <span class="close" onclick="closeModal('registerModal')">&times;</span>
        <h2>Create Account</h2>
        <form id="registerForm">
          <label>Username:</label>
          <input type="text" name="username" required>

          <label>Password:</label>
          <input type="password" name="password" required>

          <label>PESO:</label>
          <select name="peso" required>
            <option value="">-- Select PESO --</option>
            <option value="Aglipay">Aglipay</option>
            <option value="Cabarroguis">Cabarroguis</option>
            <option value="Diffun">Diffun</option>
            <option value="Maddela">Maddela</option>
            <option value="Nagtipunan">Nagtipunan</option>
            <option value="Saguday">Saguday</option>
          </select>

          <label>Role:</label>
          <select name="role" required>
            <option value="">-- Select Role --</option>
            <option value="admin">Admin</option>
            <option value="user">PESO</option>
          </select>

          <button type="submit">Register</button>
        </form>
        <div id="registerMessage" style="margin-top: 10px;"></div>
      </div>
    </div>
  </section>

  <!-- JQuery & DataTables JS CDN -->
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>

  <script>
    $(document).ready(function() {
      $('#submissionsTable').DataTable({
        pageLength: 10,
        order: [[3, 'desc']]  // order by date_conducted descending
      });
    });

    // Modal open/close logic
    document.getElementById('openTargetModal').addEventListener('click', function(e) {
      e.preventDefault();
      document.getElementById('targetModal').style.display = 'flex';
    });

    document.getElementById('openRegisterModal').addEventListener('click', function(e) {
      e.preventDefault();
      document.getElementById('registerModal').style.display = 'flex';
    });

    function closeModal(modalId) {
      document.getElementById(modalId).style.display = 'none';
    }

    window.onclick = function(event) {
      const modals = document.querySelectorAll('.modal');
      modals.forEach(modal => {
        if (event.target === modal) modal.style.display = 'none';
      });
    };

    // Register Form Submit via fetch()
    document.getElementById('registerForm').addEventListener('submit', async function(e) {
      e.preventDefault();

      const form = e.target;
      const formData = new FormData(form);

      const res = await fetch('register.php', {
        method: 'POST',
        body: formData
      });

      const result = await res.json();
      const messageDiv = document.getElementById('registerMessage');

      messageDiv.innerHTML = result.message;
      messageDiv.style.color = result.status === 'success' ? 'green' : 'red';

      if (result.status === 'success') {
        form.reset();
        setTimeout(() => {
          closeModal('registerModal');
          messageDiv.innerHTML = '';
        }, 2000);
      }
    });
  </script>

</body>
</html>
