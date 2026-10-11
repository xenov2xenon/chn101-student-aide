<?php
$pageTitle = 'All Students — Admin';
require_once '../includes/db.php';
requireAdmin();

$students = $pdo->query("
  SELECT s.*, COUNT(DISTINCT qa.attempt_id) AS total_quizzes,
         ROUND(AVG(qa.score/qa.total_questions*100),1) AS avg_score
  FROM students s
  LEFT JOIN quiz_attempts qa ON s.student_id=qa.student_id
  GROUP BY s.student_id ORDER BY s.total_points DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title><?= $pageTitle ?></title>
  <link rel="stylesheet" href="../css/style.css"/>
</head>
<body>
<div class="admin-layout">
<aside class="admin-sidebar">
  <div class="sidebar-brand"><h2>🛠️ Admin Panel</h2><p><?= h($_SESSION['admin_name']) ?></p></div>
  <ul class="sidebar-nav">
    <li><a href="index.php"><span class="nav-icon">📊</span> Dashboard</a></li>
    <li><a href="students.php" class="active"><span class="nav-icon">👥</span> All Students</a></li>
    <li><a href="questions.php"><span class="nav-icon">❓</span> Quiz Questions</a></li>
    <li><a href="add_question.php"><span class="nav-icon">➕</span> Add Question</a></li>
    <li><a href="lessons.php"><span class="nav-icon">📖</span> Lessons</a></li>
    <li><a href="flashcards.php"><span class="nav-icon">📇</span> Flashcards</a></li>
    <li><a href="reports.php"><span class="nav-icon">📈</span> Reports</a></li>
    <li><a href="../index.php"><span class="nav-icon">🌐</span> View Site</a></li>
    <li><a href="logout.php"><span class="nav-icon">🚪</span> Logout</a></li>
  </ul>
</aside>
<main class="admin-main">
  <div class="admin-topbar">
    <h1>👥 All Students <span style="font-size:1rem;font-weight:400;color:var(--muted)">(<?= count($students) ?> total)</span></h1>
    <input id="table-search" type="text" class="form-control" placeholder="Search..." style="width:200px"/>
  </div>
  <div class="card">
    <div class="table-wrap">
      <table>
        <thead>
          <tr><th>#</th><th>Username</th><th>Email</th><th>Lang</th><th>Streak</th><th>Quizzes</th><th>Avg%</th><th>Points</th><th>Joined</th><th>Action</th></tr>
        </thead>
        <tbody>
          <?php foreach ($students as $i => $s): ?>
          <tr>
            <td><?= $i+1 ?></td>
            <td><strong><?= h($s['username']) ?></strong></td>
            <td style="font-size:0.85rem;color:var(--muted)"><?= h($s['email']) ?></td>
            <td><span class="badge badge-blue"><?= $s['preferred_language'] ?></span></td>
            <td><span class="badge badge-gold">🔥 <?= $s['current_streak'] ?></span></td>
            <td><?= $s['total_quizzes'] ?></td>
            <td><?= $s['avg_score']??'—' ?><?= $s['avg_score']?'%':'' ?></td>
            <td><strong><?= number_format($s['total_points']) ?></strong></td>
            <td style="font-size:0.8rem;color:var(--muted)"><?= date('M j, Y', strtotime($s['created_at'])) ?></td>
            <td><a href="student_detail.php?id=<?= $s['student_id'] ?>" class="btn btn-sky btn-sm">View</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>
</div>
<script src="../js/main.js"></script>
</body>
</html>
