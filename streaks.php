<?php
$pageTitle = 'Streak Leaderboard — CHN101';
$base = '../';
require_once '../includes/db.php';
requireLogin();

$studentId = $_SESSION['student_id'];

// Top 10 by current streak
$streakLeaders = $pdo->query("
  SELECT s.student_id, s.username, s.current_streak, s.longest_streak, s.total_points,
         COUNT(DISTINCT qa.attempt_id) AS total_quizzes,
         s.last_quiz_date
  FROM students s
  LEFT JOIN quiz_attempts qa ON s.student_id=qa.student_id
  GROUP BY s.student_id
  ORDER BY s.current_streak DESC, s.total_points DESC
  LIMIT 10
")->fetchAll();

// Top 10 by LONGEST streak ever
$longestLeaders = $pdo->query("
  SELECT student_id, username, longest_streak, current_streak, total_points
  FROM students ORDER BY longest_streak DESC, total_points DESC LIMIT 10
")->fetchAll();

// My streak rank
$myStreakRank = $pdo->prepare("SELECT COUNT(*)+1 FROM students WHERE current_streak>(SELECT current_streak FROM students WHERE student_id=?)");
$myStreakRank->execute([$studentId]);
$myStreakRank = $myStreakRank->fetchColumn();

$me = $pdo->prepare("SELECT * FROM students WHERE student_id=?");
$me->execute([$studentId]);
$me = $me->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title><?= $pageTitle ?></title>
  <link rel="stylesheet" href="../css/style.css"/>
  <style>
    .streak-fire { font-size:1.3rem; }
    .streak-bar { height:8px;border-radius:4px;background:var(--border);overflow:hidden;margin-top:0.3rem; }
    .streak-bar-fill { height:100%;background:linear-gradient(90deg,#ff6b35,#ff0000);border-radius:4px; }
    .tab-btns { display:flex;gap:0;border:2px solid var(--border);border-radius:10px;overflow:hidden;width:fit-content; }
    .tab-btn { padding:0.55rem 1.4rem;font-weight:600;font-size:0.88rem;border:none;cursor:pointer;background:transparent;color:var(--muted);transition:var(--transition); }
    .tab-btn.active { background:var(--ink);color:var(--gold); }
  </style>
</head>
<body>

<nav class="navbar">
  <a href="../index.php" class="navbar-brand">
    <span class="logo-char">中</span>
    <span class="logo-text">CHN<span>101</span></span>
  </a>
  <ul class="nav-links">
    <li><a href="../index.php">🏠 Home</a></li>
    <li><a href="../flashcards/index.php">📚 Flashcards</a></li>
    <li><a href="../quiz/index.php">✏️ Quiz</a></li>
    <li><a href="index.php" class="active">🏆 Leaderboard</a></li>
    <li><a href="../profile.php">👤 Profile</a></li>
  </ul>
  <div class="nav-user">
    <span class="nav-streak">🔥 <?= (int)$me['current_streak'] ?></span>
    <a href="../logout.php" class="btn-logout">Logout</a>
  </div>
</nav>

<div class="page-header" data-char="火">
  <div class="container">
    <div class="breadcrumb"><a href="../index.php">Home</a> / <a href="index.php">Leaderboard</a> / Streaks</div>
    <h1>🔥 Streak Leaderboard</h1>
    <p>Who's been studying the longest without missing a day?</p>
  </div>
</div>

<!-- MY STREAK BAR -->
<div style="background:linear-gradient(135deg,var(--ink),#3d1a1a);padding:1.5rem 2rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem">
  <div style="display:flex;align-items:center;gap:2rem;flex-wrap:wrap">
    <div style="text-align:center">
      <div style="font-size:0.75rem;color:rgba(255,255,255,0.5);text-transform:uppercase;letter-spacing:0.1em">Your Streak Rank</div>
      <div style="font-family:'Playfair Display',serif;font-size:2.2rem;font-weight:900;color:var(--gold)">#<?= $myStreakRank ?></div>
    </div>
    <div style="width:1px;height:40px;background:rgba(255,255,255,0.15)"></div>
    <div>
      <div style="font-size:0.75rem;color:rgba(255,255,255,0.5)">Current Streak</div>
      <div style="font-size:1.4rem;font-weight:700;color:#ff6b35">🔥 <?= $me['current_streak'] ?> days</div>
    </div>
    <div style="width:1px;height:40px;background:rgba(255,255,255,0.15)"></div>
    <div>
      <div style="font-size:0.75rem;color:rgba(255,255,255,0.5)">Longest Ever</div>
      <div style="font-size:1.4rem;font-weight:700;color:var(--gold)">⭐ <?= $me['longest_streak'] ?> days</div>
    </div>
    <div style="width:1px;height:40px;background:rgba(255,255,255,0.15)"></div>
    <div>
      <div style="font-size:0.75rem;color:rgba(255,255,255,0.5)">Last Studied</div>
      <div style="font-size:1rem;font-weight:600;color:rgba(255,255,255,0.8)">
        <?= $me['last_quiz_date'] ? date('M j, Y',strtotime($me['last_quiz_date'])) : 'Never' ?>
      </div>
    </div>
  </div>
  <a href="../quiz/index.php" class="btn btn-gold">Study Now →</a>
</div>

<section class="section">
  <div class="container">

    <!-- Tab toggle -->
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem">
      <div>
        <span class="section-label">Rankings</span>
        <h2 style="font-family:'Playfair Display',serif;font-size:1.5rem;font-weight:700">Streak Champions</h2>
      </div>
      <div class="d-flex align-center" style="gap:0.75rem;flex-wrap:wrap">
        <div class="tab-btns">
          <button class="tab-btn active" onclick="showTab('current',this)">🔥 Current Streak</button>
          <button class="tab-btn" onclick="showTab('longest',this)">⭐ Longest Ever</button>
        </div>
        <a href="index.php" class="btn btn-dark btn-sm">← Points Board</a>
      </div>
    </div>

    <!-- CURRENT STREAK TABLE -->
    <div id="tab-current">
      <div class="card">
        <div style="padding:0.75rem 1.5rem;background:var(--ink);display:grid;grid-template-columns:52px 1fr 120px 120px 120px;gap:1rem;font-size:0.75rem;font-weight:700;letter-spacing:0.08em;color:rgba(255,255,255,0.6);text-transform:uppercase">
          <div>Rank</div><div>Student</div><div style="text-align:center">Current 🔥</div><div style="text-align:center">Longest ⭐</div><div style="text-align:center">Points</div>
        </div>
        <?php foreach ($streakLeaders as $i => $s): ?>
        <?php $isMe = $s['student_id'] == $studentId; $maxStreak = $streakLeaders[0]['current_streak'] ?: 1; ?>
        <div style="display:grid;grid-template-columns:52px 1fr 120px 120px 120px;gap:1rem;padding:1rem 1.5rem;align-items:center;border-bottom:1px solid var(--border);<?= $isMe?'background:rgba(212,160,23,0.06);border-left:3px solid var(--gold)':'' ?>">
          <div style="text-align:center;font-family:'Playfair Display',serif;font-size:1.1rem;font-weight:900;color:<?= ['#FFD700','#C0C0C0','#CD7F32'][$i]??'var(--muted)' ?>">
            <?= ['🥇','🥈','🥉'][$i] ?? '#'.($i+1) ?>
          </div>
          <div style="display:flex;align-items:center;gap:0.75rem">
            <div style="width:38px;height:38px;border-radius:50%;background:var(--ink);display:flex;align-items:center;justify-content:center;font-family:'Playfair Display',serif;font-weight:700;color:var(--gold);font-size:0.95rem;border:2px solid <?= $isMe?'var(--gold)':'transparent' ?>">
              <?= strtoupper(substr($s['username'],0,1)) ?>
            </div>
            <div>
              <div style="font-weight:600"><?= h($s['username']) ?><?= $isMe?' <span style="color:var(--gold);font-size:0.72rem">(You)</span>':'' ?></div>
              <div style="font-size:0.75rem;color:var(--muted)"><?= $s['total_quizzes'] ?> quizzes</div>
            </div>
          </div>
          <div style="text-align:center">
            <div style="font-size:1.2rem;font-weight:700;color:#ff6b35">🔥 <?= $s['current_streak'] ?></div>
            <div class="streak-bar"><div class="streak-bar-fill" style="width:<?= round($s['current_streak']/$maxStreak*100) ?>%"></div></div>
          </div>
          <div style="text-align:center;color:var(--gold);font-weight:600">⭐ <?= $s['longest_streak'] ?></div>
          <div style="text-align:center;font-family:'Playfair Display',serif;font-weight:700;color:var(--gold)"><?= number_format($s['total_points']) ?></div>
        </div>
        <?php endforeach; ?>
        <?php if (!$streakLeaders): ?>
        <div style="text-align:center;padding:3rem;color:var(--muted)">No streak data yet — take a quiz to start!</div>
        <?php endif; ?>
      </div>
    </div>

    <!-- LONGEST STREAK TABLE -->
    <div id="tab-longest" class="hidden">
      <div class="card">
        <div style="padding:0.75rem 1.5rem;background:var(--ink);display:grid;grid-template-columns:52px 1fr 140px 140px;gap:1rem;font-size:0.75rem;font-weight:700;letter-spacing:0.08em;color:rgba(255,255,255,0.6);text-transform:uppercase">
          <div>Rank</div><div>Student</div><div style="text-align:center">Longest ⭐</div><div style="text-align:center">Points</div>
        </div>
        <?php $maxLong = $longestLeaders[0]['longest_streak'] ?? 1; ?>
        <?php foreach ($longestLeaders as $i => $s): ?>
        <?php $isMe = $s['student_id'] == $studentId; ?>
        <div style="display:grid;grid-template-columns:52px 1fr 140px 140px;gap:1rem;padding:1rem 1.5rem;align-items:center;border-bottom:1px solid var(--border);<?= $isMe?'background:rgba(212,160,23,0.06);border-left:3px solid var(--gold)':'' ?>">
          <div style="text-align:center;font-family:'Playfair Display',serif;font-size:1.1rem;font-weight:900;color:<?= ['#FFD700','#C0C0C0','#CD7F32'][$i]??'var(--muted)' ?>">
            <?= ['🥇','🥈','🥉'][$i] ?? '#'.($i+1) ?>
          </div>
          <div style="display:flex;align-items:center;gap:0.75rem">
            <div style="width:38px;height:38px;border-radius:50%;background:var(--ink);display:flex;align-items:center;justify-content:center;font-family:'Playfair Display',serif;font-weight:700;color:var(--gold);font-size:0.95rem">
              <?= strtoupper(substr($s['username'],0,1)) ?>
            </div>
            <div>
              <div style="font-weight:600"><?= h($s['username']) ?><?= $isMe?' <span style="color:var(--gold);font-size:0.72rem">(You)</span>':'' ?></div>
              <div style="font-size:0.75rem;color:var(--muted)">Current: 🔥 <?= $s['current_streak'] ?> days</div>
            </div>
          </div>
          <div style="text-align:center">
            <div style="font-size:1.2rem;font-weight:700;color:var(--gold)">⭐ <?= $s['longest_streak'] ?></div>
            <div class="streak-bar"><div class="streak-bar-fill" style="width:<?= round($s['longest_streak']/$maxLong*100) ?>%;background:linear-gradient(90deg,var(--gold),#a07010)"></div></div>
          </div>
          <div style="text-align:center;font-family:'Playfair Display',serif;font-weight:700;color:var(--gold)"><?= number_format($s['total_points']) ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Streak tips card -->
    <div class="card" style="margin-top:2rem;border-left:4px solid var(--vermillion)">
      <div class="card-body">
        <h3 style="font-family:'Playfair Display',serif;margin-bottom:0.75rem">🔥 How Streaks Work</h3>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;font-size:0.88rem;color:var(--muted)">
          <div>✅ <strong>Complete at least 1 quiz</strong> per day to keep your streak alive</div>
          <div>💔 <strong>Miss a day</strong> and your streak resets to 1</div>
          <div>⭐ <strong>Longest streak</strong> is your all-time personal best — never resets</div>
          <div>🏆 <strong>Daily quizzes</strong> earn 10 pts per correct answer + 20 bonus for perfect score</div>
        </div>
      </div>
    </div>

  </div>
</section>

<footer>
  <span class="footer-char">中</span>
  <p>CHN101 Student Aide &copy; <?= date('Y') ?></p>
</footer>
<script src="../js/main.js"></script>
<script>
function showTab(tab, btn) {
  document.getElementById('tab-current').classList.toggle('hidden', tab !== 'current');
  document.getElementById('tab-longest').classList.toggle('hidden', tab !== 'longest');
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
}
</script>
</body>
</html>
