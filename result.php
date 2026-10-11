<?php
$pageTitle = 'Quiz Result — CHN101';
$base = '../';
require_once '../includes/db.php';
requireLogin();

// Get the most recent attempt for this student (or a specific attempt_id)
$studentId = $_SESSION['student_id'];
$attemptId = (int)($_GET['attempt'] ?? 0);

if ($attemptId) {
    $stmt = $pdo->prepare("SELECT qa.*, l.lesson_title, l.lesson_number FROM quiz_attempts qa JOIN lessons l ON l.lesson_id=qa.lesson_id WHERE qa.attempt_id=? AND qa.student_id=?");
    $stmt->execute([$attemptId, $studentId]);
} else {
    $stmt = $pdo->prepare("SELECT qa.*, l.lesson_title, l.lesson_number FROM quiz_attempts qa JOIN lessons l ON l.lesson_id=qa.lesson_id WHERE qa.student_id=? ORDER BY qa.attempt_date DESC LIMIT 1");
    $stmt->execute([$studentId]);
}
$attempt = $stmt->fetch();
if (!$attempt) { header('Location: index.php'); exit; }

// Get answers for this attempt (if recorded)
$answers = $pdo->prepare("
  SELECT qa2.*, qq.question_text_english, qq.question_text_bangla,
         qq.correct_answer_english, qq.correct_answer_bangla
  FROM quiz_answers qa2
  JOIN quiz_questions qq ON qq.question_id=qa2.question_id
  WHERE qa2.attempt_id=?
");
$answers->execute([$attempt['attempt_id']]);
$answers = $answers->fetchAll();

$lang = $_SESSION['preferred_lang'] ?? 'english';
$pct  = round($attempt['score'] / $attempt['total_questions'] * 100);
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
  <a href="../index.php" class="navbar-brand">
    <span class="logo-char">中</span>
    <span class="logo-text">CHN<span>101</span></span>
  </a>
  <ul class="nav-links">
    <li><a href="../index.php">🏠 Home</a></li>
    <li><a href="index.php" class="active">✏️ Quiz</a></li>
  </ul>
  <div class="nav-user">
    <span class="nav-streak">🔥 <?= (int)($_SESSION['student_streak']??0) ?></span>
    <a href="../logout.php" class="btn-logout">Logout</a>
  </div>
</nav>

<div class="page-header" data-char="分">
  <div class="container">
    <div class="breadcrumb"><a href="../index.php">Home</a> / <a href="index.php">Quiz</a> / Result</div>
    <h1>📊 Quiz Result</h1>
    <p>Lesson <?= $attempt['lesson_number'] ?>: <?= h($attempt['lesson_title']) ?> &mdash; <?= date('M j, Y',strtotime($attempt['attempt_date'])) ?></p>
  </div>
</div>

<section class="section">
  <div class="container" style="max-width:760px">

    <!-- Score summary card -->
    <div class="card" style="margin-bottom:1.5rem;text-align:center">
      <div class="card-body" style="padding:2.5rem">
        <div style="font-size:3.5rem;margin-bottom:0.75rem"><?= $pct>=80?'🏆':($pct>=60?'🎉':'📚') ?></div>
        <div class="score-circle" style="--pct:<?= $pct*3.6 ?>deg;margin:0 auto 1.25rem">
          <div class="score-num"><?= $attempt['score'] ?>/<?= $attempt['total_questions'] ?></div>
        </div>
        <h2 style="font-family:'Playfair Display',serif;font-size:1.8rem;margin-bottom:0.3rem">
          <?= $pct>=80?'Excellent!':($pct>=60?'Good Job!':'Keep Practising!') ?>
        </h2>
        <p style="color:var(--muted)"><?= $pct ?>% — <?= $attempt['score'] ?> out of <?= $attempt['total_questions'] ?> correct</p>

        <div style="display:flex;gap:1rem;justify-content:center;margin-top:1.5rem;flex-wrap:wrap">
          <span class="badge badge-<?= $pct>=70?'green':($pct>=40?'gold':'red') ?>" style="padding:0.5rem 1.25rem;font-size:0.9rem">
            <?= $pct>=70?'✅ Pass':($pct>=40?'⚠️ Fair':'❌ Needs Work') ?>
          </span>
          <span class="badge badge-blue" style="padding:0.5rem 1.25rem;font-size:0.9rem">
            +<?= $attempt['score']*10 + ($attempt['score']==$attempt['total_questions']?20:0) ?> pts earned
          </span>
        </div>

        <div style="display:flex;gap:0.75rem;justify-content:center;margin-top:1.5rem;flex-wrap:wrap">
          <a href="take.php?lesson=<?= $attempt['lesson_id'] ?>" class="btn btn-primary">🔄 Retake Quiz</a>
          <a href="index.php" class="btn btn-dark">← All Lessons</a>
          <a href="history.php" class="btn btn-jade">📋 Full History</a>
          <a href="../leaderboard/index.php" class="btn btn-gold">🏆 Leaderboard</a>
        </div>
      </div>
    </div>

    <!-- Performance breakdown -->
    <div class="card" style="margin-bottom:1.5rem">
      <div class="card-header">
        <h3>📈 Performance Breakdown</h3>
      </div>
      <div class="card-body">
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;text-align:center">
          <div>
            <div style="font-family:'Playfair Display',serif;font-size:2rem;font-weight:900;color:var(--jade)"><?= $attempt['score'] ?></div>
            <div style="color:var(--muted);font-size:0.82rem">Correct</div>
          </div>
          <div>
            <div style="font-family:'Playfair Display',serif;font-size:2rem;font-weight:900;color:var(--vermillion)"><?= $attempt['total_questions']-$attempt['score'] ?></div>
            <div style="color:var(--muted);font-size:0.82rem">Incorrect</div>
          </div>
          <div>
            <div style="font-family:'Playfair Display',serif;font-size:2rem;font-weight:900;color:var(--gold)"><?= $pct ?>%</div>
            <div style="color:var(--muted);font-size:0.82rem">Score</div>
          </div>
        </div>
        <div style="margin-top:1.25rem">
          <div style="display:flex;justify-content:space-between;font-size:0.82rem;margin-bottom:0.3rem">
            <span style="color:var(--jade)">✅ Correct: <?= $attempt['score'] ?></span>
            <span style="color:var(--vermillion)">❌ Wrong: <?= $attempt['total_questions']-$attempt['score'] ?></span>
          </div>
          <div style="height:12px;border-radius:6px;background:var(--vermillion);overflow:hidden">
            <div style="height:100%;background:var(--jade);width:<?= $pct ?>%;border-radius:6px;transition:width 1s ease"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Answer-by-answer breakdown (if answers were saved) -->
    <?php if ($answers): ?>
    <div class="card">
      <div class="card-header">
        <h3>📝 Question-by-Question Review</h3>
        <div style="display:flex;align-items:center;gap:0.5rem">
          <span style="font-size:0.82rem;color:var(--muted)">Language:</span>
          <select class="form-control" style="width:auto" onchange="setReviewLang(this.value)">
            <option value="en" <?= $lang!=='bangla'?'selected':'' ?>>🇬🇧 English</option>
            <option value="bn" <?= $lang==='bangla'?'selected':'' ?>>🇧🇩 Bangla</option>
          </select>
        </div>
      </div>
      <div class="card-body" style="display:flex;flex-direction:column;gap:1rem">
        <?php foreach ($answers as $i => $a): ?>
        <div style="border:2px solid <?= $a['is_correct']?'var(--jade)':'var(--vermillion)' ?>;border-radius:10px;padding:1rem;background:<?= $a['is_correct']?'rgba(26,122,94,0.04)':'rgba(192,57,43,0.04)' ?>">
          <div style="display:flex;align-items:flex-start;gap:0.75rem">
            <div style="font-size:1.2rem;flex-shrink:0"><?= $a['is_correct']?'✅':'❌' ?></div>
            <div style="flex:1">
              <div style="font-weight:600;margin-bottom:0.5rem">
                <span class="rev-en"><?= h($a['question_text_english']) ?></span>
                <span class="rev-bn hidden"><?= h($a['question_text_bangla'] ?: $a['question_text_english']) ?></span>
              </div>
              <?php if (!$a['is_correct']): ?>
              <div style="font-size:0.85rem;color:var(--jade)">
                <strong>Correct answer:</strong>
                <span class="rev-en"><?= h($a['correct_answer_english']) ?></span>
                <span class="rev-bn hidden"><?= h($a['correct_answer_bangla'] ?: $a['correct_answer_english']) ?></span>
              </div>
              <div style="font-size:0.85rem;color:var(--vermillion);margin-top:0.2rem">
                <strong>Your answer:</strong> <?= h($a['selected_answer'] ?: 'No answer') ?>
              </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php else: ?>
    <div class="card">
      <div class="card-body" style="text-align:center;color:var(--muted);padding:2rem">
        <p>Detailed per-question breakdown is saved for future quizzes. Retake this quiz to see it!</p>
        <a href="take.php?lesson=<?= $attempt['lesson_id'] ?>" class="btn btn-primary" style="margin-top:1rem">Retake Quiz</a>
      </div>
    </div>
    <?php endif; ?>

  </div>
</section>

<footer>
  <span class="footer-char">中</span>
  <p>CHN101 Student Aide &copy; <?= date('Y') ?></p>
</footer>
<script src="../js/main.js"></script>
<script>
function setReviewLang(val) {
  document.querySelectorAll('.rev-en').forEach(e=>e.classList.toggle('hidden',val==='bn'));
  document.querySelectorAll('.rev-bn').forEach(e=>e.classList.toggle('hidden',val!=='bn'));
}
// Animate score circle
window.addEventListener('load', () => {
  const c = document.querySelector('.score-circle');
  if (c) {
    const pct = <?= $pct ?>;
    c.style.background = `conic-gradient(var(--gold) ${pct*3.6}deg, var(--border) 0deg)`;
  }
});
</script>
</body>
</html>
