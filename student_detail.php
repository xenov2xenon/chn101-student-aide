<?php
$pageTitle = 'Student Detail — Admin';
require_once '../includes/db.php';
requireAdmin();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: index.php'); exit; }

$student = $pdo->prepare("SELECT * FROM students WHERE student_id=?");
$student->execute([$id]);
$student = $student->fetch();
if (!$student) { header('Location: index.php'); exit; }

// Quiz history
$history = $pdo->prepare("
  SELECT qa.*, l.lesson_title, qa.attempt_date
  FROM quiz_attempts qa
  JOIN lessons l ON l.lesson_id=qa.lesson_id
  WHERE qa.student_id=?
  ORDER BY qa.attempt_date DESC
");
$history->execute([$id]);
$history = $history->fetchAll();

// Progress per lesson
$progress = $pdo->prepare("
  SELECT l.lesson_title, COUNT(qa.attempt_id) AS attempts,
         MAX(qa.score) AS best_score, MAX(qa.total_questions) AS total_q
  FROM lessons l
  LEFT JOIN quiz_attempts qa ON qa.lesson_id=l.lesson_id AND qa.student_id=?
  GROUP BY l.lesson_id ORDER BY l.lesson_number
");
$progress->execute([$id]);
$progress = $progress->fetchAll();
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
    <h1>👤 <?= h($student['username']) ?>'s Profile</h1>
    <a href="students.php" class="btn btn-dark btn-sm">← Back</a>
  </div>

  <!-- PROFILE STATS -->
  <div class="grid-4 mb-3">
    <div class="card" style="border-top:4px solid var(--gold)">
      <div class="card-body text-center">
        <div style="font-size:2rem;font-weight:900;font-family:'Playfair Display',serif;color:var(--gold)"><?= $student['current_streak'] ?>🔥</div>
        <div style="color:var(--muted);font-size:0.82rem">Current Streak</div>
      </div>
    </div>
    <div class="card" style="border-top:4px solid var(--vermillion)">
      <div class="card-body text-center">
        <div style="font-size:2rem;font-weight:900;font-family:'Playfair Display',serif;color:var(--vermillion)"><?= count($history) ?></div>
        <div style="color:var(--muted);font-size:0.82rem">Total Quizzes</div>
      </div>
    </div>
    <div class="card" style="border-top:4px solid var(--jade)">
      <div class="card-body text-center">
        <?php
          $avg = count($history) ? round(array_sum(array_map(fn($h)=>$h['score']/$h['total_questions']*100,$history))/count($history),1) : 0;
        ?>
        <div style="font-size:2rem;font-weight:900;font-family:'Playfair Display',serif;color:var(--jade)"><?= $avg ?>%</div>
        <div style="color:var(--muted);font-size:0.82rem">Avg Score</div>
      </div>
    </div>
    <div class="card" style="border-top:4px solid var(--sky)">
      <div class="card-body text-center">
        <div style="font-size:2rem;font-weight:900;font-family:'Playfair Display',serif;color:var(--sky)"><?= number_format($student['total_points']) ?></div>
        <div style="color:var(--muted);font-size:0.82rem">Total Points</div>
      </div>
    </div>
  </div>

  <div class="grid-2">
    <!-- LESSON PROGRESS -->
    <div class="card">
      <div class="card-header"><h3>📚 Lesson Progress</h3></div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Lesson</th><th>Attempts</th><th>Best Score</th></tr></thead>
          <tbody>
            <?php foreach ($progress as $p): ?>
            <tr>
              <td><?= h($p['lesson_title']) ?></td>
              <td><?= $p['attempts'] ?: '—' ?></td>
              <td>
                <?php if ($p['best_score'] !== null): ?>
                  <span class="badge badge-<?= ($p['best_score']/$p['total_q']*100)>=70?'green':'red' ?>">
                    <?= $p['best_score'] ?>/<?= $p['total_q'] ?>
                  </span>
                <?php else: ?>—<?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- QUIZ HISTORY -->
    <div class="card">
      <div class="card-header"><h3>📋 Quiz History</h3></div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Lesson</th><th>Score</th><th>%</th><th>Date</th></tr></thead>
          <tbody>
            <?php foreach ($history as $h): ?>
            <tr>
              <td><?= h($h['lesson_title']) ?></td>
              <td><?= $h['score'] ?>/<?= $h['total_questions'] ?></td>
              <td><?= round($h['score']/$h['total_questions']*100) ?>%</td>
              <td style="font-size:0.8rem;color:var(--muted)"><?= date('M j, Y', strtotime($h['attempt_date'])) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$history): ?>
            <tr><td colspan="4" style="text-align:center;color:var(--muted);padding:2rem">No quiz attempts yet</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</main>
</div>
<script src="../js/main.js"></script>
</body>
</html>
