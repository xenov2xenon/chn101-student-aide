<?php
$pageTitle = 'Quiz Questions — Admin';
require_once '../includes/db.php';
requireAdmin();

// Handle delete
if (isset($_GET['delete'])) {
    $del = $pdo->prepare("DELETE FROM quiz_questions WHERE question_id=?");
    $del->execute([(int)$_GET['delete']]);
    header('Location: questions.php?deleted=1');
    exit;
}

$lessonFilter = (int)($_GET['lesson'] ?? 0);
$lessons = $pdo->query("SELECT * FROM lessons ORDER BY lesson_number")->fetchAll();

if ($lessonFilter) {
    $stmt = $pdo->prepare("SELECT qq.*, l.lesson_title FROM quiz_questions qq JOIN lessons l ON l.lesson_id=qq.lesson_id WHERE qq.lesson_id=? ORDER BY qq.question_id DESC");
    $stmt->execute([$lessonFilter]);
} else {
    $stmt = $pdo->query("SELECT qq.*, l.lesson_title FROM quiz_questions qq JOIN lessons l ON l.lesson_id=qq.lesson_id ORDER BY l.lesson_number, qq.question_id");
}
$questions = $stmt->fetchAll();
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
    <h1>❓ Quiz Questions</h1>
    <a href="add_question.php" class="btn btn-primary">➕ Add New Question</a>
  </div>

  <?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">✅ Question deleted.</div><?php endif; ?>
  <?php if (isset($_GET['saved'])): ?><div class="alert alert-success">✅ Question saved successfully.</div><?php endif; ?>

  <!-- Filter by lesson -->
  <div class="card mb-3" style="margin-bottom:1.5rem">
    <div class="card-body" style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap">
      <label class="form-label" style="margin:0;white-space:nowrap">Filter by Lesson:</label>
      <select onchange="location='questions.php?lesson='+this.value" class="form-control" style="width:auto;min-width:220px">
        <option value="0">All Lessons</option>
        <?php foreach ($lessons as $l): ?>
        <option value="<?= $l['lesson_id'] ?>" <?= $lessonFilter===$l['lesson_id']?'selected':'' ?>>
          Lesson <?= $l['lesson_number'] ?>: <?= h($l['lesson_title']) ?>
        </option>
        <?php endforeach; ?>
      </select>
      <input id="table-search" type="text" class="form-control" placeholder="Search questions..." style="width:220px"/>
    </div>
  </div>

  <div class="card">
    <div class="table-wrap">
      <table>
        <thead>
          <tr><th>#</th><th>Lesson</th><th>Question (EN)</th><th>Correct Answer</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach ($questions as $i => $q): ?>
          <tr>
            <td><?= $i+1 ?></td>
            <td><span class="badge badge-blue"><?= h($q['lesson_title']) ?></span></td>
            <td style="max-width:280px"><?= h(substr($q['question_text_english'],0,80)) ?>...</td>
            <td><strong style="color:var(--jade)"><?= h($q['correct_answer_english']) ?></strong></td>
            <td style="display:flex;gap:0.5rem;flex-wrap:wrap">
              <a href="edit_question.php?id=<?= $q['question_id'] ?>" class="btn btn-gold btn-sm">✏️ Edit</a>
              <a href="questions.php?delete=<?= $q['question_id'] ?>" class="btn btn-primary btn-sm" onclick="return confirm('Delete this question?')">🗑️ Delete</a>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (!$questions): ?>
          <tr><td colspan="5" style="text-align:center;color:var(--muted);padding:2rem">No questions found. <a href="add_question.php">Add one now</a></td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>
</div>
<script src="../js/main.js"></script>
</body>
</html>
