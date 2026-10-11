<?php
$pageTitle = 'My Profile — CHN101';
require_once 'includes/db.php';
requireLogin();

$studentId = $_SESSION['student_id'];
$msg = ''; $error = '';

// Fetch current student data
$student = $pdo->prepare("SELECT * FROM students WHERE student_id=?");
$student->execute([$studentId]);
$student = $student->fetch();

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_lang'])) {
        $lang = in_array($_POST['preferred_language'],['english','bangla']) ? $_POST['preferred_language'] : 'english';
        $pdo->prepare("UPDATE students SET preferred_language=? WHERE student_id=?")->execute([$lang,$studentId]);
        $_SESSION['preferred_lang'] = $lang;
        $msg = 'Language preference updated!';
        $student['preferred_language'] = $lang;
    }
    if (isset($_POST['change_password'])) {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password']     ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if (!password_verify($current, $student['password_hash'])) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($new) < 6) {
            $error = 'New password must be at least 6 characters.';
        } elseif ($new !== $confirm) {
            $error = 'Passwords do not match.';
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE students SET password_hash=? WHERE student_id=?")->execute([$hash,$studentId]);
            $msg = 'Password changed successfully!';
        }
    }
}

// Stats for this student
$stats = $pdo->prepare("
  SELECT COUNT(DISTINCT qa.attempt_id) AS total_quizzes,
         COUNT(DISTINCT qa.lesson_id)  AS lessons_done,
         ROUND(AVG(qa.score/qa.total_questions*100),1) AS avg_score,
         (SELECT COUNT(*) FROM personal_flashcards WHERE student_id=:sid) AS personal_cards
  FROM quiz_attempts qa WHERE qa.student_id=:sid
");
$stats->execute([':sid' => $studentId]);
$stats = $stats->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title><?= $pageTitle ?></title>
  <link rel="stylesheet" href="css/style.css"/>
</head>
<body>

<nav class="navbar">
  <a href="index.php" class="navbar-brand">
    <span class="logo-char">中</span>
    <span class="logo-text">CHN<span>101</span> <em style="font-style:italic;font-weight:300;font-size:0.9em;">Student Aide</em></span>
  </a>
  <ul class="nav-links">
    <li><a href="index.php">🏠 Home</a></li>
    <li><a href="flashcards/index.php">📚 Flashcards</a></li>
    <li><a href="quiz/index.php">✏️ Quiz</a></li>
    <li><a href="leaderboard/index.php">🏆 Leaderboard</a></li>
  </ul>
  <div class="nav-user">
    <span class="nav-streak">🔥 <?= (int)$student['current_streak'] ?> day streak</span>
    <a href="logout.php" class="btn-logout">Logout</a>
  </div>
</nav>

<div class="page-header" data-char="我">
  <div class="container">
    <div class="breadcrumb"><a href="index.php">Home</a> / My Profile</div>
    <h1>👤 My Profile</h1>
    <p>Manage your account, track your achievements, and update preferences</p>
  </div>
</div>

<section class="section">
  <div class="container">

    <?php if ($msg): ?><div class="alert alert-success">✅ <?= h($msg) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error">⚠️ <?= h($error) ?></div><?php endif; ?>

    <div style="display:grid;grid-template-columns:300px 1fr;gap:1.5rem;align-items:start">

      <!-- LEFT: Profile card -->
      <div>
        <div class="card" style="text-align:center">
          <div class="card-body" style="padding:2rem">
            <div style="width:80px;height:80px;border-radius:50%;background:var(--ink);display:flex;align-items:center;justify-content:center;font-family:'Playfair Display',serif;font-size:2rem;font-weight:700;color:var(--gold);margin:0 auto 1rem;border:3px solid var(--gold)">
              <?= strtoupper(substr($student['username'],0,1)) ?>
            </div>
            <h2 style="font-family:'Playfair Display',serif;font-size:1.4rem;margin-bottom:0.2rem"><?= h($student['username']) ?></h2>
            <p style="color:var(--muted);font-size:0.85rem;margin-bottom:1.5rem"><?= h($student['email']) ?></p>
            <span class="badge badge-blue" style="font-size:0.82rem;padding:0.4rem 0.9rem">
              <?= $student['preferred_language']==='bangla'?'🇧🇩 Bangla':'🇬🇧 English' ?>
            </span>
            <div style="margin-top:1.25rem;font-size:0.8rem;color:var(--muted)">
              Member since <?= date('F Y', strtotime($student['created_at'])) ?>
            </div>
          </div>
        </div>

        <!-- Achievement stats -->
        <div class="card" style="margin-top:1rem">
          <div class="card-header"><h3>🏅 Achievements</h3></div>
          <div class="card-body" style="display:flex;flex-direction:column;gap:1rem">
            <?php
            $achievements = [
              ['🔥','Current Streak',$student['current_streak'].' days','var(--vermillion)'],
              ['🏆','Longest Streak',$student['longest_streak'].' days','var(--gold)'],
              ['⭐','Total Points',number_format($student['total_points']),'var(--sky)'],
              ['✏️','Quizzes Taken',$stats['total_quizzes']??0,'var(--jade)'],
              ['📚','Lessons Done',$stats['lessons_done']??0,'var(--vermillion)'],
              ['📇','Personal Cards',$stats['personal_cards']??0,'var(--gold)'],
            ];
            foreach ($achievements as $a): ?>
            <div style="display:flex;align-items:center;gap:0.75rem">
              <div style="width:36px;height:36px;border-radius:10px;background:rgba(0,0,0,0.04);display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0"><?= $a[0] ?></div>
              <div style="flex:1">
                <div style="font-size:0.78rem;color:var(--muted)"><?= $a[1] ?></div>
                <div style="font-weight:700;color:<?= $a[3] ?>"><?= $a[2] ?></div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- RIGHT: Settings -->
      <div style="display:flex;flex-direction:column;gap:1.25rem">

        <!-- Language preference -->
        <div class="card">
          <div class="card-header"><h3>🌐 Language Preference</h3></div>
          <div class="card-body">
            <p style="color:var(--muted);font-size:0.88rem;margin-bottom:1.25rem">
              Choose the language for quiz questions, answer options, and flashcard meanings.
            </p>
            <form method="POST" style="display:flex;align-items:flex-end;gap:1rem;flex-wrap:wrap">
              <div class="form-group" style="margin:0;flex:1;min-width:200px">
                <label class="form-label">Study Language</label>
                <select name="preferred_language" class="form-control">
                  <option value="english" <?= $student['preferred_language']!=='bangla'?'selected':'' ?>>🇬🇧 English</option>
                  <option value="bangla"  <?= $student['preferred_language']==='bangla'?'selected':'' ?>>🇧🇩 Bangla (বাংলা)</option>
                </select>
              </div>
              <button type="submit" name="update_lang" class="btn btn-jade" style="flex-shrink:0">Save Preference</button>
            </form>
          </div>
        </div>

        <!-- Avg score card -->
        <div class="card">
          <div class="card-header"><h3>📊 Quiz Performance</h3></div>
          <div class="card-body">
            <?php $avg = $stats['avg_score'] ?? 0; ?>
            <div style="display:flex;align-items:center;gap:2rem;flex-wrap:wrap">
              <div style="text-align:center">
                <div style="font-family:'Playfair Display',serif;font-size:3rem;font-weight:900;color:var(--gold)"><?= $avg ?>%</div>
                <div style="color:var(--muted);font-size:0.82rem">Average Score</div>
              </div>
              <div style="flex:1;min-width:180px">
                <div style="margin-bottom:0.75rem">
                  <div style="display:flex;justify-content:space-between;font-size:0.82rem;color:var(--muted);margin-bottom:0.3rem"><span>Performance</span><span><?= $avg ?>%</span></div>
                  <div class="progress-bar-wrap"><div class="progress-bar-fill" style="width:<?= $avg ?>%"></div></div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem">
                  <div style="font-size:0.82rem;color:var(--muted)">Quizzes taken</div><strong><?= $stats['total_quizzes']??0 ?></strong>
                  <div style="font-size:0.82rem;color:var(--muted)">Lessons done</div><strong><?= $stats['lessons_done']??0 ?></strong>
                  <div style="font-size:0.82rem;color:var(--muted)">Total points</div><strong style="color:var(--gold)"><?= number_format($student['total_points']) ?></strong>
                </div>
              </div>
            </div>
            <div style="margin-top:1.25rem;display:flex;gap:0.75rem;flex-wrap:wrap">
              <a href="quiz/index.php" class="btn btn-primary btn-sm">Take a Quiz</a>
              <a href="quiz/history.php" class="btn btn-dark btn-sm">View History</a>
            </div>
          </div>
        </div>

        <!-- Change password -->
        <div class="card">
          <div class="card-header"><h3>🔐 Change Password</h3></div>
          <div class="card-body">
            <form method="POST">
              <div class="form-group">
                <label class="form-label">Current Password</label>
                <input type="password" name="current_password" class="form-control" required placeholder="Your current password"/>
              </div>
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                <div class="form-group">
                  <label class="form-label">New Password</label>
                  <input type="password" name="new_password" class="form-control" required placeholder="At least 6 characters"/>
                </div>
                <div class="form-group">
                  <label class="form-label">Confirm New Password</label>
                  <input type="password" name="confirm_password" class="form-control" required placeholder="Repeat new password"/>
                </div>
              </div>
              <button type="submit" name="change_password" class="btn btn-vermillion" style="background:var(--vermillion);color:white">🔐 Update Password</button>
            </form>
          </div>
        </div>

        <!-- Quick links -->
        <div class="card">
          <div class="card-header"><h3>⚡ Quick Links</h3></div>
          <div class="card-body" style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem">
            <a href="flashcards/index.php" class="btn btn-jade" style="justify-content:center">📚 Flashcards</a>
            <a href="flashcards/my_cards.php" class="btn btn-dark" style="justify-content:center">✨ My Cards</a>
            <a href="quiz/index.php" class="btn btn-primary" style="justify-content:center">✏️ Take Quiz</a>
            <a href="leaderboard/index.php" class="btn btn-gold" style="justify-content:center">🏆 Leaderboard</a>
          </div>
        </div>

      </div>
    </div>
  </div>
</section>

<footer>
  <span class="footer-char">中</span>
  <p>CHN101 Student Aide &copy; <?= date('Y') ?></p>
</footer>
<script src="js/main.js"></script>
</body>
</html>
