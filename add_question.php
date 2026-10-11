<?php
$pageTitle = 'Add Question — Admin';
require_once '../includes/db.php';
requireAdmin();

$lessons = $pdo->query("SELECT * FROM lessons ORDER BY lesson_number")->fetchAll();
$error = ''; $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lessonId = (int)$_POST['lesson_id'];
    $qEn  = trim($_POST['question_en']  ?? '');
    $qBn  = trim($_POST['question_bn']  ?? '');
    $aEn  = trim($_POST['answer_en']    ?? '');
    $aBn  = trim($_POST['answer_bn']    ?? '');
    $b_en = trim($_POST['b_en'] ?? '');
    $b_bn = trim($_POST['b_bn'] ?? '');
    $c_en = trim($_POST['c_en'] ?? '');
    $c_bn = trim($_POST['c_bn'] ?? '');
    $d_en = trim($_POST['d_en'] ?? '');
    $d_bn = trim($_POST['d_bn'] ?? '');

    if (!$lessonId || !$qEn || !$aEn || !$b_en || !$c_en || !$d_en) {
        $error = 'Please fill in all required English fields.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO quiz_questions
          (lesson_id, question_text_english, question_text_bangla,
           correct_answer_english, correct_answer_bangla,
           option_b_english, option_b_bangla,
           option_c_english, option_c_bangla,
           option_d_english, option_d_bangla)
          VALUES (?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([$lessonId,$qEn,$qBn,$aEn,$aBn,$b_en,$b_bn,$c_en,$c_bn,$d_en,$d_bn]);
        header('Location: questions.php?saved=1');
        exit;
    }
}
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
    <li><a href="students.php"><span class="nav-icon">👥</span> All Students</a></li>
    <li><a href="questions.php"><span class="nav-icon">❓</span> Quiz Questions</a></li>
    <li><a href="add_question.php" class="active"><span class="nav-icon">➕</span> Add Question</a></li>
    <li><a href="lessons.php"><span class="nav-icon">📖</span> Lessons</a></li>
    <li><a href="flashcards.php"><span class="nav-icon">📇</span> Flashcards</a></li>
    <li><a href="reports.php"><span class="nav-icon">📈</span> Reports</a></li>
    <li><a href="../index.php"><span class="nav-icon">🌐</span> View Site</a></li>
    <li><a href="logout.php"><span class="nav-icon">🚪</span> Logout</a></li>
  </ul>
</aside>
<main class="admin-main">
  <div class="admin-topbar">
    <h1>➕ Add Quiz Question</h1>
    <a href="questions.php" class="btn btn-dark btn-sm">← Back to Questions</a>
  </div>

  <?php if ($error): ?><div class="alert alert-error">⚠️ <?= h($error) ?></div><?php endif; ?>

  <form method="POST">
    <div class="grid-2">
      <div class="card">
        <div class="card-header"><h3>📝 Question Details</h3></div>
        <div class="card-body">
          <div class="form-group">
            <label class="form-label">Lesson *</label>
            <select name="lesson_id" class="form-control" required>
              <option value="">Select a lesson</option>
              <?php foreach ($lessons as $l): ?>
              <option value="<?= $l['lesson_id'] ?>">Lesson <?= $l['lesson_number'] ?>: <?= h($l['lesson_title']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Question (English) *</label>
            <textarea name="question_en" class="form-control" required placeholder="e.g. What does 你好 mean?"><?= h($_POST['question_en']??'') ?></textarea>
          </div>
          <div class="form-group">
            <label class="form-label">Question (Bangla)</label>
            <textarea name="question_bn" class="form-control" placeholder="Bangla version of the question"><?= h($_POST['question_bn']??'') ?></textarea>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header"><h3>✅ Correct Answer (Option A)</h3></div>
        <div class="card-body">
          <div class="form-group">
            <label class="form-label">Correct Answer (English) *</label>
            <input type="text" name="answer_en" class="form-control" required placeholder="The correct answer" value="<?= h($_POST['answer_en']??'') ?>"/>
          </div>
          <div class="form-group">
            <label class="form-label">Correct Answer (Bangla)</label>
            <input type="text" name="answer_bn" class="form-control" placeholder="Bangla version" value="<?= h($_POST['answer_bn']??'') ?>"/>
          </div>
        </div>
      </div>
    </div>

    <div class="card mt-2" style="margin-top:1.5rem">
      <div class="card-header"><h3>🔀 Wrong Options (B, C, D)</h3></div>
      <div class="card-body">
        <div class="grid-3">
          <div>
            <div class="form-group">
              <label class="form-label">Option B (English) *</label>
              <input type="text" name="b_en" class="form-control" required value="<?= h($_POST['b_en']??'') ?>"/>
            </div>
            <div class="form-group">
              <label class="form-label">Option B (Bangla)</label>
              <input type="text" name="b_bn" class="form-control" value="<?= h($_POST['b_bn']??'') ?>"/>
            </div>
          </div>
          <div>
            <div class="form-group">
              <label class="form-label">Option C (English) *</label>
              <input type="text" name="c_en" class="form-control" required value="<?= h($_POST['c_en']??'') ?>"/>
            </div>
            <div class="form-group">
              <label class="form-label">Option C (Bangla)</label>
              <input type="text" name="c_bn" class="form-control" value="<?= h($_POST['c_bn']??'') ?>"/>
            </div>
          </div>
          <div>
            <div class="form-group">
              <label class="form-label">Option D (English) *</label>
              <input type="text" name="d_en" class="form-control" required value="<?= h($_POST['d_en']??'') ?>"/>
            </div>
            <div class="form-group">
              <label class="form-label">Option D (Bangla)</label>
              <input type="text" name="d_bn" class="form-control" value="<?= h($_POST['d_bn']??'') ?>"/>
            </div>
          </div>
        </div>
        <button type="submit" class="btn btn-primary" style="margin-top:1rem">💾 Save Question</button>
      </div>
    </div>
  </form>
</main>
</div>
<script src="../js/main.js"></script>
</body>
</html>
