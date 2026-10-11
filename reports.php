<?php
$pageTitle = 'Reports — Admin';
require_once '../includes/db.php';
requireAdmin();

// Overall stats
$stats = $pdo->query("
  SELECT
    (SELECT COUNT(*) FROM students) AS total_students,
    (SELECT COUNT(*) FROM quiz_attempts) AS total_attempts,
    (SELECT ROUND(AVG(score/total_questions*100),1) FROM quiz_attempts) AS overall_avg,
    (SELECT COUNT(*) FROM flashcards WHERE is_official=1) AS official_cards,
    (SELECT COUNT(*) FROM personal_flashcards) AS personal_cards,
    (SELECT COUNT(*) FROM quiz_questions) AS total_questions,
    (SELECT MAX(current_streak) FROM students) AS top_streak,
    (SELECT COUNT(*) FROM students WHERE last_quiz_date=CURDATE()) AS active_today
")->fetch();

// Per-lesson breakdown
$lessonStats = $pdo->query("
  SELECT l.lesson_number, l.lesson_title,
         COUNT(DISTINCT qa.attempt_id) AS attempts,
         COUNT(DISTINCT qa.student_id) AS unique_students,
         ROUND(AVG(qa.score/qa.total_questions*100),1) AS avg_score,
         MAX(qa.score/qa.total_questions*100) AS best_score,
         COUNT(DISTINCT f.flashcard_id) AS card_count,
         COUNT(DISTINCT q.question_id) AS q_count
  FROM lessons l
  LEFT JOIN quiz_attempts qa ON qa.lesson_id=l.lesson_id
  LEFT JOIN flashcards f ON f.lesson_id=l.lesson_id AND f.is_official=1
  LEFT JOIN quiz_questions q ON q.lesson_id=l.lesson_id
  GROUP BY l.lesson_id ORDER BY l.lesson_number
")->fetchAll();

// Top performers
$topStudents = $pdo->query("
  SELECT s.username, s.current_streak, s.total_points, s.preferred_language,
         COUNT(DISTINCT qa.attempt_id) AS total_quizzes,
         ROUND(AVG(qa.score/qa.total_questions*100),1) AS avg_score
  FROM students s
  LEFT JOIN quiz_attempts qa ON s.student_id=qa.student_id
  GROUP BY s.student_id
  ORDER BY s.total_points DESC LIMIT 5
")->fetchAll();

// Quiz activity over last 7 days
$dailyActivity = $pdo->query("
  SELECT DATE(attempt_date) AS day, COUNT(*) AS count
  FROM quiz_attempts
  WHERE attempt_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
  GROUP BY DATE(attempt_date) ORDER BY day
")->fetchAll();

// Language distribution
$langDist = $pdo->query("SELECT preferred_language, COUNT(*) AS cnt FROM students GROUP BY preferred_language")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title><?= $pageTitle ?></title>
  <link rel="stylesheet" href="../css/style.css"/>
  <style>
    .mini-bar-wrap { display:flex;align-items:center;gap:0.75rem; }
    .mini-bar { flex:1;height:10px;background:var(--border);border-radius:5px;overflow:hidden; }
    .mini-bar-fill { height:100%;background:linear-gradient(90deg,var(--gold),var(--vermillion));border-radius:5px; }
    .chart-bar-group { display:flex;align-items:flex-end;gap:6px;height:80px;margin-top:0.5rem; }
    .chart-bar { flex:1;background:var(--gold);border-radius:4px 4px 0 0;min-width:20px;position:relative;transition:var(--transition); }
    .chart-bar:hover { background:var(--vermillion); }
    .chart-bar-label { font-size:0.65rem;color:var(--muted);text-align:center;margin-top:0.3rem; }
    .stat-highlight { font-family:'Playfair Display',serif;font-size:1.8rem;font-weight:900; }
    @media print { .admin-sidebar,.admin-topbar a { display:none; } .admin-main { padding:0; } }
  </style>
</head>
<body>
<div class="admin-layout">
<aside class="admin-sidebar">
  <div class="sidebar-brand"><h2>🛠️ Admin Panel</h2><p><?= h($_SESSION['admin_name']) ?></p></div>
  <ul class="sidebar-nav">
    <li><a href="index.php"><span class="nav-icon">📊</span> Dashboard</a></li>
    <li><a href="students.php"><span class="nav-icon">👥</span> All Students</a></li>
    <li><a href="flashcards.php"><span class="nav-icon">📇</span> Flashcards</a></li>
    <li><a href="add_flashcard.php"><span class="nav-icon">➕</span> Add Flashcard</a></li>
    <li><a href="questions.php"><span class="nav-icon">❓</span> Quiz Questions</a></li>
    <li><a href="add_question.php"><span class="nav-icon">➕</span> Add Question</a></li>
    <li><a href="lessons.php"><span class="nav-icon">📖</span> Lessons</a></li>
    <li><a href="reports.php" class="active"><span class="nav-icon">📈</span> Reports</a></li>
    <li><a href="../index.php"><span class="nav-icon">🌐</span> View Site</a></li>
    <li><a href="logout.php"><span class="nav-icon">🚪</span> Logout</a></li>
  </ul>
</aside>

<main class="admin-main">
  <div class="admin-topbar">
    <h1>📈 Platform Reports</h1>
    <div style="display:flex;gap:0.5rem">
      <button onclick="window.print()" class="btn btn-dark btn-sm">🖨️ Print</button>
      <a href="students.php" class="btn btn-jade btn-sm">📥 Student Data</a>
    </div>
  </div>

  <!-- KEY METRICS -->
  <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem">
    <?php
    $metrics = [
      ['Total Students',     $stats['total_students'],  'var(--gold)',       '👥'],
      ['Active Today',       $stats['active_today'],    'var(--jade)',       '🟢'],
      ['Quiz Attempts',      $stats['total_attempts'],  'var(--vermillion)', '✏️'],
      ['Overall Avg Score',  ($stats['overall_avg']??0).'%', 'var(--sky)', '📊'],
      ['Official Flashcards',$stats['official_cards'],  'var(--gold)',       '📇'],
      ['Personal Cards',     $stats['personal_cards'],  'var(--jade)',       '✨'],
      ['Quiz Questions',     $stats['total_questions'], 'var(--vermillion)', '❓'],
      ['Top Streak',         $stats['top_streak'].' 🔥','var(--sky)',       '🏆'],
    ];
    foreach ($metrics as $m): ?>
    <div class="card" style="border-top:4px solid <?= $m[2] ?>">
      <div class="card-body" style="text-align:center;padding:1.25rem">
        <div style="font-size:1.4rem;margin-bottom:0.3rem"><?= $m[3] ?></div>
        <div class="stat-highlight" style="color:<?= $m[2] ?>"><?= $m[1] ?></div>
        <div style="color:var(--muted);font-size:0.78rem;margin-top:0.2rem"><?= $m[0] ?></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;margin-bottom:1.5rem">

    <!-- LESSON BREAKDOWN -->
    <div class="card">
      <div class="card-header"><h3>📚 Per-Lesson Statistics</h3></div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>Lesson</th><th>Attempts</th><th>Students</th><th>Avg Score</th><th>Performance</th><th>Cards</th><th>Questions</th></tr>
          </thead>
          <tbody>
            <?php foreach ($lessonStats as $ls): ?>
            <?php $avg = $ls['avg_score'] ?? 0; ?>
            <tr>
              <td>
                <strong>L<?= $ls['lesson_number'] ?></strong><br>
                <span style="font-size:0.8rem;color:var(--muted)"><?= h($ls['lesson_title']) ?></span>
              </td>
              <td><?= $ls['attempts'] ?: '—' ?></td>
              <td><?= $ls['unique_students'] ?: '—' ?></td>
              <td><strong><?= $avg ?>%</strong></td>
              <td style="min-width:120px">
                <div class="mini-bar-wrap">
                  <div class="mini-bar">
                    <div class="mini-bar-fill" style="width:<?= $avg ?>%"></div>
                  </div>
                  <span style="font-size:0.78rem;color:var(--muted);min-width:30px"><?= $avg ?>%</span>
                </div>
              </td>
              <td><span class="badge badge-green"><?= $ls['card_count'] ?></span></td>
              <td><span class="badge badge-blue"><?= $ls['q_count'] ?></span></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- SIDE PANELS -->
    <div style="display:flex;flex-direction:column;gap:1rem">
      <!-- Language distribution -->
      <div class="card">
        <div class="card-header"><h3>🌐 Language Preference</h3></div>
        <div class="card-body">
          <?php
          $total = array_sum(array_column($langDist,'cnt'));
          foreach ($langDist as $ld):
            $pct = $total ? round($ld['cnt']/$total*100) : 0;
            $color = $ld['preferred_language']==='bangla' ? 'var(--jade)' : 'var(--sky)';
          ?>
          <div style="margin-bottom:1rem">
            <div style="display:flex;justify-content:space-between;font-size:0.85rem;margin-bottom:0.3rem">
              <span><?= $ld['preferred_language']==='bangla'?'🇧🇩 Bangla':'🇬🇧 English' ?></span>
              <strong><?= $ld['cnt'] ?> students (<?= $pct ?>%)</strong>
            </div>
            <div style="height:12px;background:var(--border);border-radius:6px;overflow:hidden">
              <div style="height:100%;width:<?= $pct ?>%;background:<?= $color ?>;border-radius:6px"></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Activity chart (last 7 days) -->
      <div class="card">
        <div class="card-header"><h3>📅 Quiz Activity (7 Days)</h3></div>
        <div class="card-body">
          <?php
          $maxCount = max(array_column($dailyActivity,'count') ?: [1]);
          ?>
          <div class="chart-bar-group">
            <?php foreach ($dailyActivity as $day): ?>
            <div style="display:flex;flex-direction:column;align-items:center;flex:1">
              <div class="chart-bar"
                   title="<?= $day['day'] ?>: <?= $day['count'] ?> quizzes"
                   style="height:<?= round($day['count']/$maxCount*100) ?>%"></div>
              <div class="chart-bar-label"><?= date('M j',strtotime($day['day'])) ?></div>
            </div>
            <?php endforeach; ?>
            <?php if (!$dailyActivity): ?>
            <div style="width:100%;text-align:center;color:var(--muted);font-size:0.85rem;padding:1rem">No quiz activity in the last 7 days</div>
            <?php endif; ?>
          </div>
          <?php if ($dailyActivity): ?>
          <div style="font-size:0.78rem;color:var(--muted);margin-top:0.5rem">
            Total: <?= array_sum(array_column($dailyActivity,'count')) ?> quizzes in last 7 days
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- TOP 5 STUDENTS -->
  <div class="card">
    <div class="card-header">
      <h3>🏆 Top 5 Students</h3>
      <a href="students.php" class="btn btn-dark btn-sm">View All →</a>
    </div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Rank</th><th>Student</th><th>Language</th><th>Total Quizzes</th><th>Avg Score</th><th>Streak</th><th>Points</th></tr></thead>
        <tbody>
          <?php $medals = ['🥇','🥈','🥉','4️⃣','5️⃣']; ?>
          <?php foreach ($topStudents as $i => $s): ?>
          <tr>
            <td style="font-size:1.2rem"><?= $medals[$i] ?></td>
            <td><strong><?= h($s['username']) ?></strong></td>
            <td><span class="badge badge-blue"><?= $s['preferred_language'] ?></span></td>
            <td><?= $s['total_quizzes'] ?></td>
            <td><?= $s['avg_score'] ?? '—' ?><?= $s['avg_score']?'%':'' ?></td>
            <td><span class="badge badge-gold">🔥 <?= $s['current_streak'] ?></span></td>
            <td><strong style="color:var(--gold)"><?= number_format($s['total_points']) ?></strong></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div style="margin-top:1rem;color:var(--muted);font-size:0.82rem;text-align:right">
    Report generated: <?= date('l, F j Y \a\t g:ia') ?>
  </div>
</main>
</div>
<script src="../js/main.js"></script>
</body>
</html>
