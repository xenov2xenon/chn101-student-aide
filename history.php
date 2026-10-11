<?php
$pageTitle = 'Quiz History — CHN101';
$base = '../';
require_once '../includes/db.php';
requireLogin();

$studentId = $_SESSION['student_id'];

$history = $pdo->prepare("
  SELECT qa.*, l.lesson_title, l.lesson_number
  FROM quiz_attempts qa
  JOIN lessons l ON l.lesson_id=qa.lesson_id
  WHERE qa.student_id=?
  ORDER BY qa.attempt_date DESC
");
$history->execute([$studentId]);
$history = $history->fetchAll();

// Summary stats
$student = $pdo->prepare("SELECT * FROM students WHERE student_id=?");
$student->execute([$studentId]);
$student = $student->fetch();

$totalAttempts = count($history);
$avgPct = $totalAttempts ? round(array_sum(array_map(fn($h)=>$h['score']/$h['total_questions']*100,$history))/$totalAttempts,1) : 0;
$bestPct = $totalAttempts ? max(array_map(fn($h)=>round($h['score']/$h['total_questions']*100),$history)) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title><?= $pageTitle ?></title>
  <link rel="stylesheet" href="../css/style.css"/>
</head>
<body>
<nav class="navbar">
  <a href="../index.php" class="navbar-brand"><span class="logo-char">中</span><span class="logo-text">CHN<span>101</span></span></a>
  <ul class="nav-links">
    <li><a href="../index.php">🏠 Home</a></li>
    <li><a href="../flashcards/index.php">📚 Flashcards</a></li>
    <li><a href="index.php" class="active">✏️ Quiz</a></li>
    <li><a href="../leaderboard/index.php">🏆 Leaderboard</a></li>
    <li><a href="../profile.php">👤 Profile</a></li>
  </ul>
  <div class="nav-user">
    <span class="nav-streak">🔥 <?= (int)$student['current_streak'] ?></span>
    <a href="../logout.php" class="btn-logout">Logout</a>
  </div>
</nav>

<div class="page-header" data-char="历">
  <div class="container">
    <div class="breadcrumb"><a href="../index.php">Home</a> / <a href="index.php">Quiz</a> / History</div>
    <h1>📋 My Quiz History</h1>
    <p>Track your progress across all lessons</p>
  </div>
</div>

<section class="section">
  <div class="container">
    <!-- Stats row -->
    <div class="grid-4 mb-3" style="margin-bottom:1.5rem">
      <div class="card" style="border-top:4px solid var(--gold)">
        <div class="card-body text-center">
          <div style="font-size:2rem;font-family:'Playfair Display',serif;font-weight:900;color:var(--gold)"><?= $student['current_streak'] ?>🔥</div>
          <div style="color:var(--muted);font-size:0.82rem">Current Streak</div>
        </div>
      </div>
      <div class="card" style="border-top:4px solid var(--vermillion)">
        <div class="card-body text-center">
          <div style="font-size:2rem;font-family:'Playfair Display',serif;font-weight:900;color:var(--vermillion)"><?= $totalAttempts ?></div>
          <div style="color:var(--muted);font-size:0.82rem">Total Quizzes</div>
        </div>
      </div>
      <div class="card" style="border-top:4px solid var(--jade)">
        <div class="card-body text-center">
          <div style="font-size:2rem;font-family:'Playfair Display',serif;font-weight:900;color:var(--jade)"><?= $avgPct ?>%</div>
          <div style="color:var(--muted);font-size:0.82rem">Avg Score</div>
        </div>
      </div>
      <div class="card" style="border-top:4px solid var(--sky)">
        <div class="card-body text-center">
          <div style="font-size:2rem;font-family:'Playfair Display',serif;font-weight:900;color:var(--sky)"><?= number_format($student['total_points']) ?></div>
          <div style="color:var(--muted);font-size:0.82rem">Total Points</div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h3>All Attempts</h3>
        <a href="index.php" class="btn btn-primary btn-sm">Take a Quiz →</a>
      </div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>#</th><th>Lesson</th><th>Score</th><th>Percentage</th><th>Result</th><th>Date</th></tr></thead>
          <tbody>
            <?php if (!$history): ?>
            <tr><td colspan="6" style="text-align:center;padding:2.5rem;color:var(--muted)">
              No quizzes taken yet. <a href="index.php" style="color:var(--vermillion)">Take your first quiz!</a>
            </td></tr>
            <?php endif; ?>
            <?php foreach ($history as $i => $h): ?>
            <?php $pct = round($h['score']/$h['total_questions']*100); ?>
            <tr>
              <td><?= $i+1 ?></td>
              <td>
                <strong>Lesson <?= $h['lesson_number'] ?></strong><br>
                <span style="font-size:0.82rem;color:var(--muted)"><?= h($h['lesson_title']) ?></span>
              </td>
              <td><strong><?= $h['score'] ?> / <?= $h['total_questions'] ?></strong></td>
              <td>
                <div style="display:flex;align-items:center;gap:0.75rem">
                  <div class="progress-bar-wrap" style="flex:1;margin:0">
                    <div class="progress-bar-fill" style="width:<?= $pct ?>%"></div>
                  </div>
                  <span style="font-weight:700;font-size:0.9rem;min-width:36px"><?= $pct ?>%</span>
                </div>
              </td>
              <td>
                <span class="badge badge-<?= $pct>=70?'green':($pct>=40?'gold':'red') ?>">
                  <?= $pct>=70?'✅ Pass':($pct>=40?'⚠️ Fair':'❌ Fail') ?>
                </span>
              </td>
              <td style="font-size:0.82rem;color:var(--muted)"><?= date('M j, Y · g:ia', strtotime($h['attempt_date'])) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>

<footer>
  <span class="footer-char">中</span>
  <p>CHN101 Student Aide &copy; <?= date('Y') ?></p>
</footer>
<script src="../js/main.js"></script>
</body>
</html>
