<?php
$pageTitle = 'Edit Question — Admin';
require_once '../includes/db.php';
requireAdmin();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: questions.php'); exit; }

$lessons = $pdo->query("SELECT * FROM lessons ORDER BY lesson_number")->fetchAll();
$error = '';

$stmt = $pdo->prepare("SELECT * FROM quiz_questions WHERE question_id=?");
$stmt->execute([$id]);
$q = $stmt->fetch();
if (!$q) { header('Location: questions.php'); exit; }

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
        $upd = $pdo->prepare("UPDATE quiz_questions SET
          lesson_id=?, question_text_english=?, question_text_bangla=?,
          correct_answer_english=?, correct_answer_bangla=?,
          option_b_english=?, option_b_bangla=?,
          option_c_english=?, option_c_bangla=?,
          option_d_english=?, option_d_bangla=?
          WHERE question_id=?");
        $upd->execute([$lessonId,$qEn,$qBn,$aEn,$aBn,$b_en,$b_bn,$c_en,$c_bn,$d_en,$d_bn,$id]);
        header('Location: questions.php?saved=1');
        exit;
    }
    // Re-populate $q with POST data
    $q = array_merge($q, ['lesson_id'=>$lessonId,'question_text_english'=>$qEn,'question_text_bangla'=>$qBn,
      'correct_answer_english'=>$aEn,'correct_answer_bangla'=>$aBn,
      'option_b_english'=>$b_en,'option_b_bangla'=>$b_bn,
      'option_c_english'=>$c_en,'option_c_bangla'=>$c_bn,
      'option_d_english'=>$d_en,'option_d_bangla'=>$d_bn]);
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
    <li><a href="questions.php" class="active"><span class="nav-icon">❓</span> Quiz Questions</a></li>
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
    <h1>✏️ Edit Question #<?= $id ?></h1>
    <a href="questions.php" class="btn btn-dark btn-sm">← Back</a>
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
              <?php foreach ($lessons as $l): ?>
              <option value="<?= $l['lesson_id'] ?>" <?= $q['lesson_id']==$l['lesson_id']?'selected':'' ?>>
                Lesson <?= $l['lesson_number'] ?>: <?= h($l['lesson_title']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Question (English) *</label>
            <textarea name="question_en" class="form-control" required><?= h($q['question_text_english']) ?></textarea>
          </div>
          <div class="form-group">
            <label class="form-label">Question (Bangla)</label>
            <textarea name="question_bn" class="form-control"><?= h($q['question_text_bangla']) ?></textarea>
          </div>
        </div>
      </div>
      <div class="card">
        <div class="card-header"><h3>✅ Correct Answer (A)</h3></div>
        <div class="card-body">
          <div class="form-group">
            <label class="form-label">English *</label>
            <input type="text" name="answer_en" class="form-control" required value="<?= h($q['correct_answer_english']) ?>"/>
          </div>
          <div class="form-group">
            <label class="form-label">Bangla</label>
            <input type="text" name="answer_bn" class="form-control" value="<?= h($q['correct_answer_bangla']) ?>"/>
          </div>
        </div>
      </div>
    </div>
    <div class="card" style="margin-top:1.5rem">
      <div class="card-header"><h3>🔀 Wrong Options (B, C, D)</h3></div>
      <div class="card-body">
        <div class="grid-3">
          <?php foreach ([['b','option_b'],['c','option_c'],['d','option_d']] as [$letter,$col]): ?>
          <div>
            <div class="form-group">
              <label class="form-label">Option <?= strtoupper($letter) ?> (English) *</label>
              <input type="text" name="<?= $letter ?>_en" class="form-control" required value="<?= h($q["{$col}_english"]) ?>"/>
            </div>
            <div class="form-group">
              <label class="form-label">Option <?= strtoupper($letter) ?> (Bangla)</label>
              <input type="text" name="<?= $letter ?>_bn" class="form-control" value="<?= h($q["{$col}_bangla"]) ?>"/>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <button type="submit" class="btn btn-gold" style="margin-top:1rem">💾 Update Question</button>
      </div>
    </div>
  </form>
</main>
</div>
<script src="../js/main.js"></script>
</body>
</html>
