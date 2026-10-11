<?php
$base = '../';
require_once '../includes/db.php';
requireLogin();

$lessonId = (int)($_GET['lesson'] ?? 0);
if (!$lessonId) { header('Location: index.php'); exit; }

$lesson = $pdo->prepare("SELECT * FROM lessons WHERE lesson_id=?");
$lesson->execute([$lessonId]);
$lesson = $lesson->fetch();
if (!$lesson) { header('Location: index.php'); exit; }

$lang = $_SESSION['preferred_lang'] ?? 'english';

// Fetch questions
$questions = $pdo->prepare("SELECT * FROM quiz_questions WHERE lesson_id=? ORDER BY RAND()");
$questions->execute([$lessonId]);
$questions = $questions->fetchAll();

if (!$questions) { header('Location: index.php?no_questions=1'); exit; }

$pageTitle = "Quiz: {$lesson['lesson_title']}";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title><?= $pageTitle ?></title>
  <link rel="stylesheet" href="../css/style.css"/>
  <style>
    .quiz-wrap { max-width:680px; margin:0 auto; }
    .q-header { margin-bottom:2rem; }
    .q-number { font-size:0.82rem;color:var(--muted);margin-bottom:0.35rem;font-weight:600 }
    .q-text { font-family:'Playfair Display',serif;font-size:1.4rem;font-weight:700;line-height:1.4; }
    #result-area { text-align:center; }
    .result-icon { font-size:4rem; margin-bottom:1rem; }
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
    <li><a href="index.php">✏️ Quiz</a></li>
    <li><a href="../profile.php">👤 Profile</a></li>
  </ul>
  <div class="nav-user">
    <span class="nav-streak">🔥 <?= (int)($_SESSION['student_streak']??0) ?></span>
    <span style="color:rgba(255,255,255,0.6);font-size:0.88rem"><strong style="color:white"><?= h($_SESSION['student_name']) ?></strong></span>
    <a href="../logout.php" class="btn-logout">Logout</a>
  </div>
</nav>

<div class="page-header" data-char="答">
  <div class="container">
    <div class="breadcrumb"><a href="../index.php">Home</a> / <a href="index.php">Quiz</a> / Lesson <?= $lesson['lesson_number'] ?></div>
    <h1>✏️ <?= h($lesson['lesson_title']) ?></h1>
    <p><?= count($questions) ?> questions &mdash; answers in <?= $lang==='bangla'?'Bangla':'English' ?></p>
  </div>
</div>

<section class="section">
  <div class="container">
    <div class="quiz-wrap">

      <!-- Lang toggle -->
      <div class="d-flex justify-between align-center mb-3" style="margin-bottom:1.5rem;flex-wrap:wrap;gap:0.75rem">
        <div class="d-flex align-center" style="gap:0.5rem">
          <span style="font-size:0.88rem;color:var(--muted)">Language:</span>
          <select id="quiz-lang-sel" class="form-control" style="width:auto">
            <option value="english" <?= $lang!=='bangla'?'selected':'' ?>>🇬🇧 English</option>
            <option value="bangla"  <?= $lang==='bangla'?'selected':'' ?>>🇧🇩 Bangla</option>
          </select>
        </div>
        <a href="index.php" class="btn btn-dark btn-sm">← Exit Quiz</a>
      </div>

      <!-- QUIZ AREA -->
      <div id="quiz-area">
        <div class="q-header">
          <div class="q-number">
            Question <span id="q-num">1</span> of <?= count($questions) ?>
          </div>
          <div class="progress-bar-wrap">
            <div class="progress-bar-fill" id="q-progress" style="width:0%"></div>
          </div>
        </div>

        <div class="card" style="margin-bottom:1.5rem">
          <div class="card-body">
            <div class="q-text" id="q-text">Loading...</div>
          </div>
        </div>

        <div id="options-container"></div>

        <button id="btn-next" class="btn btn-gold hidden" onclick="nextQuestion()" style="width:100%;justify-content:center;margin-top:0.5rem">
          Next Question →
        </button>
      </div>

      <!-- RESULT AREA -->
      <div id="result-area" class="hidden">
        <div class="card">
          <div class="card-body">
            <div class="result-icon" id="result-emoji">🎉</div>
            <div class="score-circle" id="score-circle">
              <div class="score-num" id="result-score">0/0</div>
            </div>
            <h2 style="font-family:'Playfair Display',serif;font-size:1.8rem;margin-bottom:0.3rem" id="result-title">Quiz Complete!</h2>
            <p style="color:var(--muted)" id="result-pct">0%</p>
            <div class="alert alert-success mt-2" id="streak-update" style="margin-top:1rem;justify-content:center"></div>
            <div style="display:flex;gap:0.75rem;justify-content:center;margin-top:1.5rem;flex-wrap:wrap">
              <a href="take.php?lesson=<?= $lessonId ?>" class="btn btn-primary">🔄 Retake Quiz</a>
              <a href="result.php" class="btn btn-jade">📊 Detailed Result</a>
              <a href="history.php" class="btn btn-sky">📋 My History</a>
              <a href="index.php" class="btn btn-dark">← All Lessons</a>
              <a href="../leaderboard/index.php" class="btn btn-gold">🏆 Leaderboard</a>
            </div>
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

<script src="../js/main.js"></script>
<script>
const quizData = <?= json_encode($questions) ?>;
const lessonId = <?= $lessonId ?>;
let currentQ = 0;
let score = 0;
let answered = false;
let lang = document.getElementById('quiz-lang-sel').value;
let answerLog = []; // tracks {question_id, selected_answer, is_correct} per question

document.getElementById('quiz-lang-sel').addEventListener('change', function() {
  lang = this.value;
  if (!answered) renderQuestion();
});

function shuffle(arr) {
  for (let i=arr.length-1;i>0;i--) {
    const j=Math.floor(Math.random()*(i+1));
    [arr[i],arr[j]]=[arr[j],arr[i]];
  }
  return arr;
}

function renderQuestion() {
  answered = false;
  const q = quizData[currentQ];
  document.getElementById('q-num').textContent = currentQ+1;
  document.getElementById('q-progress').style.width = ((currentQ/quizData.length)*100)+'%';
  document.getElementById('btn-next').classList.add('hidden');

  const qText = lang==='bangla' && q.question_text_bangla ? q.question_text_bangla : q.question_text_english;
  document.getElementById('q-text').textContent = qText;

  const options = shuffle([
    { text: lang==='bangla'&&q.correct_answer_bangla ? q.correct_answer_bangla : q.correct_answer_english, correct: true },
    { text: lang==='bangla'&&q.option_b_bangla ? q.option_b_bangla : q.option_b_english, correct: false },
    { text: lang==='bangla'&&q.option_c_bangla ? q.option_c_bangla : q.option_c_english, correct: false },
    { text: lang==='bangla'&&q.option_d_bangla ? q.option_d_bangla : q.option_d_english, correct: false },
  ]);

  const letters = ['A','B','C','D'];
  const container = document.getElementById('options-container');
  container.innerHTML = '';
  options.forEach((opt, i) => {
    const div = document.createElement('div');
    div.className = 'quiz-option';
    div.innerHTML = `<span class="option-letter">${letters[i]}</span><span>${opt.text}</span>`;
    div.addEventListener('click', () => selectAnswer(div, opt.correct, options));
    container.appendChild(div);
  });
}

function selectAnswer(chosen, isCorrect, options) {
  if (answered) return;
  answered = true;
  if (isCorrect) score++;

  // Log this answer for per-question save
  const q = quizData[currentQ];
  const selectedText = chosen.querySelector('span:last-child').textContent;
  answerLog.push({
    question_id: q.question_id,
    selected_answer: selectedText,
    is_correct: isCorrect ? 1 : 0
  });

  document.querySelectorAll('.quiz-option').forEach((el, i) => {
    if (el === chosen) el.classList.add(isCorrect ? 'correct' : 'wrong');
    else if (options[i].correct) el.classList.add('correct');
  });

  document.getElementById('btn-next').classList.remove('hidden');
  document.getElementById('btn-next').textContent = currentQ+1 < quizData.length ? 'Next Question →' : 'See Results →';
}

function nextQuestion() {
  currentQ++;
  if (currentQ < quizData.length) {
    renderQuestion();
  } else {
    finishQuiz();
  }
}

function finishQuiz() {
  const pct = Math.round(score/quizData.length*100);
  document.getElementById('quiz-area').classList.add('hidden');
  document.getElementById('result-area').classList.remove('hidden');
  document.getElementById('result-score').textContent = score+'/'+quizData.length;
  document.getElementById('result-pct').textContent = pct+'%';
  document.getElementById('result-title').textContent = pct>=80?'Excellent! 🌟':pct>=60?'Good Job! 👍':'Keep Practising! 💪';
  document.getElementById('result-emoji').textContent = pct>=80?'🏆':pct>=60?'🎉':'📚';

  // Conic gradient
  const circle = document.getElementById('score-circle');
  circle.style.background = `conic-gradient(var(--gold) ${pct*3.6}deg, var(--border) 0deg)`;

  // Save to server — include full answer log
  fetch('submit.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({
      lesson_id: lessonId,
      score,
      total: quizData.length,
      answers: answerLog
    })
  })
  .then(r=>r.json())
  .then(data => {
    const streakEl = document.getElementById('streak-update');
    let msg = '';
    if (data.new_streak) msg += '🔥 Streak: '+data.new_streak+' day'+( data.new_streak>1?'s':'')+'!  ';
    if (data.points_earned) msg += '+'+data.points_earned+' pts earned';
    streakEl.textContent = msg;
    streakEl.style.display = msg ? 'flex' : 'none';
    // Update the "Detailed Result" link with the real attempt_id
    if (data.attempt_id) {
      document.querySelectorAll('a[href="result.php"]').forEach(a => {
        a.href = 'result.php?attempt=' + data.attempt_id;
      });
    }
  })
  .catch(()=>{});
}

// Init
renderQuestion();
</script>
</body>
</html>
