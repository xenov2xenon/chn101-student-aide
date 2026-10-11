<?php
$pageTitle = 'Lessons — Admin';
require_once '../includes/db.php';
requireAdmin();

$msg = '';
// Add lesson
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['add_lesson'])) {
    $title = trim($_POST['title']??'');
    $desc  = trim($_POST['desc']??'');
    $num   = (int)$_POST['lesson_number'];
    if ($title && $num) {
        $pdo->prepare("INSERT INTO lessons (lesson_title,lesson_number,description) VALUES (?,?,?)")->execute([$title,$num,$desc]);
        $msg = 'Lesson added!';
    }
}
// Delete
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM lessons WHERE lesson_id=?")->execute([(int)$_GET['delete']]);
    header('Location: lessons.php?deleted=1'); exit;
}

$lessons = $pdo->query("
  SELECT l.*, COUNT(DISTINCT f.flashcard_id) AS card_count, COUNT(DISTINCT q.question_id) AS q_count
  FROM lessons l
  LEFT JOIN flashcards f ON f.lesson_id=l.lesson_id
  LEFT JOIN quiz_questions q ON q.lesson_id=l.lesson_id
  GROUP BY l.lesson_id ORDER BY l.lesson_number
")->fetchAll();
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
    <li><a href="add_question.php"><span class="nav-icon">➕</span> Add Question</a></li>
    <li><a href="lessons.php" class="active"><span class="nav-icon">📖</span> Lessons</a></li>
    <li><a href="../index.php"><span class="nav-icon">🌐</span> View Site</a></li>
    <li><a href="logout.php"><span class="nav-icon">🚪</span> Logout</a></li>
  </ul>
</aside>
<main class="admin-main">
  <div class="admin-topbar">
    <h1>📖 Lessons Management</h1>
    <button onclick="openModal('add-lesson-modal')" class="btn btn-primary">➕ Add Lesson</button>
  </div>
  <?php if ($msg||isset($_GET['deleted'])): ?><div class="alert alert-success">✅ <?= $msg ?: 'Lesson deleted.' ?></div><?php endif; ?>

  <div class="card">
    <div class="table-wrap">
      <table>
        <thead><tr><th>#</th><th>Lesson No.</th><th>Title</th><th>Description</th><th>Flashcards</th><th>Questions</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($lessons as $l): ?>
          <tr>
            <td><?= $l['lesson_id'] ?></td>
            <td><strong><?= $l['lesson_number'] ?></strong></td>
            <td><?= h($l['lesson_title']) ?></td>
            <td style="font-size:0.85rem;color:var(--muted)"><?= h(substr($l['description']??'',0,60)) ?>...</td>
            <td><span class="badge badge-green"><?= $l['card_count'] ?></span></td>
            <td><span class="badge badge-blue"><?= $l['q_count'] ?></span></td>
            <td>
              <a href="questions.php?lesson=<?= $l['lesson_id'] ?>" class="btn btn-sky btn-sm">Questions</a>
              <a href="lessons.php?delete=<?= $l['lesson_id'] ?>" class="btn btn-primary btn-sm" onclick="return confirm('Delete lesson and all its data?')">🗑️</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>
</div>

<!-- ADD LESSON MODAL -->
<div class="modal-overlay" id="add-lesson-modal">
  <div class="modal">
    <div class="modal-header">
      <h3>➕ Add New Lesson</h3>
      <button class="modal-close">×</button>
    </div>
    <form method="POST">
      <div class="form-group">
        <label class="form-label">Lesson Number *</label>
        <input type="number" name="lesson_number" class="form-control" required min="1"/>
      </div>
      <div class="form-group">
        <label class="form-label">Lesson Title *</label>
        <input type="text" name="title" class="form-control" required placeholder="e.g. Greetings & Introductions"/>
      </div>
      <div class="form-group">
        <label class="form-label">Description</label>
        <textarea name="desc" class="form-control" placeholder="Brief description of this lesson"></textarea>
      </div>
      <button type="submit" name="add_lesson" class="btn btn-primary w-full" style="width:100%;justify-content:center">Add Lesson</button>
    </form>
  </div>
</div>

<script src="../js/main.js"></script>
</body>
</html>
