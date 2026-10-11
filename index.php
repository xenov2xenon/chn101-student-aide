<?php
$pageTitle = 'CHN101 Student Aide — Learn Mandarin';
$base = '';
require_once 'includes/db.php';

// Fetch quick stats for homepage
$totalStudents = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$totalLessons  = $pdo->query("SELECT COUNT(*) FROM lessons")->fetchColumn();
$totalQuizzes  = $pdo->query("SELECT COUNT(*) FROM quiz_attempts")->fetchColumn();
$totalCards    = $pdo->query("SELECT COUNT(*) FROM flashcards")->fetchColumn();

// Top 3 leaderboard preview
$topStudents = $pdo->query("SELECT username, current_streak, total_points FROM students ORDER BY total_points DESC LIMIT 3")->fetchAll();

// All lessons
$lessons = $pdo->query("SELECT * FROM lessons ORDER BY lesson_number")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title><?= $pageTitle ?></title>
  <link rel="stylesheet" href="css/style.css"/>
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🀄</text></svg>"/>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
  <a href="index.php" class="navbar-brand">
    <span class="logo-char">中</span>
    <span class="logo-text">CHN<span>101</span> <em style="font-style:italic;font-weight:300;font-size:0.9em;">Student Aide</em></span>
  </a>
  <ul class="nav-links">
    <li><a href="index.php" class="active">🏠 Home</a></li>
    <li><a href="flashcards/index.php">📚 Flashcards</a></li>
    <li><a href="quiz/index.php">✏️ Quiz</a></li>
    <li><a href="leaderboard/index.php">🏆 Leaderboard</a></li>
    <?php if (isLoggedIn()): ?><li><a href="profile.php">👤 Profile</a></li><?php endif; ?>
  </ul>
  <div class="nav-user">
    <?php if (isLoggedIn()): ?>
      <span class="nav-streak">🔥 <?= (int)($_SESSION['student_streak']??0) ?> day streak</span>
      <span style="color:rgba(255,255,255,0.6);font-size:0.88rem;">Hi, <strong style="color:white"><?= h($_SESSION['student_name']??'') ?></strong></span>
      <a href="logout.php" class="btn-logout">Logout</a>
    <?php else: ?>
      <a href="login.php" class="btn btn-gold btn-sm">Login</a>
      <a href="register.php" class="btn btn-outline btn-sm">Register</a>
    <?php endif; ?>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-tag">🎓 Chinese Language Learning Platform</div>
  <h1>Master Mandarin,<br><span class="accent">One Lesson at a Time</span></h1>
  <p>Interactive flashcards, quizzes with daily streaks, and a live leaderboard — built for CHN101 students.</p>
  <div class="hero-actions">
    <?php if (isLoggedIn()): ?>
      <a href="quiz/index.php" class="btn btn-primary">Start a Quiz →</a>
      <a href="flashcards/index.php" class="btn btn-outline">Browse Flashcards</a>
    <?php else: ?>
      <a href="register.php" class="btn btn-gold">Get Started Free →</a>
      <a href="login.php" class="btn btn-outline">Login</a>
    <?php endif; ?>
  </div>
</section>

<!-- STATS BAR -->
<div class="stats-bar">
  <div class="stats-grid container">
    <div class="stat-item"><div class="stat-value"><?= $totalStudents ?></div><div class="stat-label">Active Students</div></div>
    <div class="stat-item"><div class="stat-value"><?= $totalLessons ?></div><div class="stat-label">Lessons</div></div>
    <div class="stat-item"><div class="stat-value"><?= $totalCards ?>+</div><div class="stat-label">Flashcards</div></div>
    <div class="stat-item"><div class="stat-value"><?= $totalQuizzes ?></div><div class="stat-label">Quizzes Taken</div></div>
  </div>
</div>

<!-- FEATURES OVERVIEW -->
<section class="section">
  <div class="container">
    <div class="section-header">
      <span class="section-label">What You Can Do</span>
      <h2>Four Powerful Features</h2>
      <p>Everything you need to learn Mandarin effectively</p>
    </div>
    <div class="features-grid">
      <a href="admin/index.php" class="feature-card animate-fadeup delay-1" style="--card-accent:#d4a017;--icon-bg:rgba(212,160,23,0.1)">
        <div class="feature-icon">🛠️</div>
        <h3>Admin Dashboard</h3>
        <p>View student progress, manage quiz questions, track scores and streaks across all lessons.</p>
        <span class="card-arrow">Manage →</span>
      </a>
      <a href="flashcards/index.php" class="feature-card animate-fadeup delay-2" style="--card-accent:#1a7a5e;--icon-bg:rgba(26,122,94,0.1)">
        <div class="feature-icon">📚</div>
        <h3>Personalized Flashcards</h3>
        <p>Browse official flashcards by lesson and create your own personal study cards in English or Bangla.</p>
        <span class="card-arrow" style="color:var(--jade)">Study →</span>
      </a>
      <a href="quiz/index.php" class="feature-card animate-fadeup delay-3" style="--card-accent:#c0392b;--icon-bg:rgba(192,57,43,0.1)">
        <div class="feature-icon">✏️</div>
        <h3>Interactive Quiz</h3>
        <p>Take lesson quizzes in English or Bangla. Earn points, build daily streaks, and track improvement.</p>
        <span class="card-arrow" style="color:var(--vermillion)">Quiz Now →</span>
      </a>
      <a href="leaderboard/index.php" class="feature-card animate-fadeup delay-4" style="--card-accent:#2980b9;--icon-bg:rgba(41,128,185,0.1)">
        <div class="feature-icon">🏆</div>
        <h3>Leaderboard</h3>
        <p>Compete with classmates. See the top 10 performers ranked by streak and total points.</p>
        <span class="card-arrow" style="color:var(--sky)">View Rankings →</span>
      </a>
    </div>
  </div>
</section>

<!-- LESSONS GRID -->
<section class="section section-alt">
  <div class="container">
    <div class="section-header">
      <span class="section-label">Course Content</span>
      <h2>All Lessons</h2>
      <p>Select any lesson to study flashcards or take a quiz</p>
    </div>
    <div class="grid-3">
      <?php foreach ($lessons as $lesson): ?>
      <div class="lesson-card" data-num="<?= $lesson['lesson_number'] ?>">
        <span class="lesson-num">Lesson <?= $lesson['lesson_number'] ?></span>
        <h3><?= h($lesson['lesson_title']) ?></h3>
        <p><?= h($lesson['description']) ?></p>
        <div class="d-flex gap-1 mt-2" style="gap:0.5rem">
          <a href="flashcards/lesson.php?id=<?= $lesson['lesson_id'] ?>" class="btn btn-jade btn-sm">📚 Flashcards</a>
          <a href="quiz/take.php?lesson=<?= $lesson['lesson_id'] ?>" class="btn btn-primary btn-sm">✏️ Quiz</a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- LEADERBOARD PREVIEW -->
<section class="section">
  <div class="container">
    <div class="d-flex justify-between align-center mb-3" style="flex-wrap:wrap;gap:1rem">
      <div>
        <span class="section-label">Top Performers</span>
        <h2 style="font-family:'Playfair Display',serif;font-size:1.8rem;font-weight:700">Live Leaderboard</h2>
      </div>
      <a href="leaderboard/index.php" class="btn btn-dark">View Full Leaderboard →</a>
    </div>
    <div class="card">
      <?php foreach ($topStudents as $i => $s): ?>
      <div class="lb-row">
        <div class="lb-rank <?= ['top-1','top-2','top-3'][$i] ?>"><?= ['🥇','🥈','🥉'][$i] ?></div>
        <div class="lb-avatar"><?= strtoupper(substr($s['username'],0,1)) ?></div>
        <div>
          <div class="lb-name"><?= h($s['username']) ?></div>
          <div class="lb-detail">Total Points</div>
        </div>
        <div class="lb-streak">🔥 <?= $s['current_streak'] ?></div>
        <div class="lb-points"><?= number_format($s['total_points']) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- HOW IT WORKS -->
<section class="section section-alt">
  <div class="container">
    <div class="section-header">
      <span class="section-label">Getting Started</span>
      <h2>How It Works</h2>
    </div>
    <div class="grid-4" style="text-align:center">
      <div class="animate-fadeup delay-1">
        <div style="font-size:2.5rem;margin-bottom:0.75rem">📝</div>
        <h4 style="font-family:'Playfair Display',serif;margin-bottom:0.4rem">1. Register</h4>
        <p style="color:var(--muted);font-size:0.88rem">Create your account and choose English or Bangla as your study language.</p>
      </div>
      <div class="animate-fadeup delay-2">
        <div style="font-size:2.5rem;margin-bottom:0.75rem">📚</div>
        <h4 style="font-family:'Playfair Display',serif;margin-bottom:0.4rem">2. Study Flashcards</h4>
        <p style="color:var(--muted);font-size:0.88rem">Browse official cards per lesson or create personal ones.</p>
      </div>
      <div class="animate-fadeup delay-3">
        <div style="font-size:2.5rem;margin-bottom:0.75rem">✏️</div>
        <h4 style="font-family:'Playfair Display',serif;margin-bottom:0.4rem">3. Take Quizzes</h4>
        <p style="color:var(--muted);font-size:0.88rem">Answer questions, earn points, and build your daily streak.</p>
      </div>
      <div class="animate-fadeup delay-4">
        <div style="font-size:2.5rem;margin-bottom:0.75rem">🏆</div>
        <h4 style="font-family:'Playfair Display',serif;margin-bottom:0.4rem">4. Climb the Board</h4>
        <p style="color:var(--muted);font-size:0.88rem">Compete with classmates and reach the top of the leaderboard.</p>
      </div>
    </div>
  </div>
</section>

<footer>
  <span class="footer-char">中</span>
  <p>CHN101 Student Aide — Learn Mandarin with Confidence</p>
  <p style="margin-top:0.4rem;font-size:0.78rem;">Powered by PHP &amp; MySQL · XAMPP · &copy; <?= date('Y') ?></p>
</footer>
<script src="js/main.js"></script>
</body>
</html>
