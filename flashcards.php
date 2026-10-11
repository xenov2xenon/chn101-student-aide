<?php
$pageTitle = 'Manage Flashcards — Admin';
require_once '../includes/db.php';
requireAdmin();

// Delete
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM flashcards WHERE flashcard_id=?")->execute([(int)$_GET['delete']]);
    header('Location: flashcards.php?deleted=1'); exit;
}

$lessonFilter = (int)($_GET['lesson'] ?? 0);
$lessons = $pdo->query("SELECT * FROM lessons ORDER BY lesson_number")->fetchAll();

if ($lessonFilter) {
    $stmt = $pdo->prepare("SELECT f.*, l.lesson_title, l.lesson_number FROM flashcards f JOIN lessons l ON l.lesson_id=f.lesson_id WHERE f.lesson_id=? ORDER BY f.flashcard_id");
    $stmt->execute([$lessonFilter]);
} else {
    $stmt = $pdo->query("SELECT f.*, l.lesson_title, l.lesson_number FROM flashcards f JOIN lessons l ON l.lesson_id=f.lesson_id ORDER BY l.lesson_number, f.flashcard_id");
}
$cards = $stmt->fetchAll();
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
  <div class="sidebar-brand">
    <h2>🛠️ Admin Panel</h2>
    <p>Logged in as <strong><?= h($_SESSION['admin_name']) ?></strong></p>
  </div>
  <ul class="sidebar-nav">
    <li><a href="index.php"><span class="nav-icon">📊</span> Dashboard</a></li>
    <li><a href="students.php"><span class="nav-icon">👥</span> All Students</a></li>
    <li><a href="flashcards.php" class="active"><span class="nav-icon">📇</span> Flashcards</a></li>
    <li><a href="add_flashcard.php"><span class="nav-icon">➕</span> Add Flashcard</a></li>
    <li><a href="questions.php"><span class="nav-icon">❓</span> Quiz Questions</a></li>
    <li><a href="add_question.php"><span class="nav-icon">➕</span> Add Question</a></li>
    <li><a href="lessons.php"><span class="nav-icon">📖</span> Lessons</a></li>
    <li><a href="reports.php"><span class="nav-icon">📈</span> Reports</a></li>
    <li><a href="../index.php"><span class="nav-icon">🌐</span> View Site</a></li>
    <li><a href="logout.php"><span class="nav-icon">🚪</span> Logout</a></li>
  </ul>
</aside>

<main class="admin-main">
  <div class="admin-topbar">
    <h1>📇 Official Flashcards <span style="font-size:1rem;font-weight:400;color:var(--muted)">(<?= count($cards) ?> shown)</span></h1>
    <a href="add_flashcard.php" class="btn btn-primary">➕ Add Flashcard</a>
  </div>

  <?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">✅ Flashcard deleted.</div><?php endif; ?>
  <?php if (isset($_GET['saved'])): ?><div class="alert alert-success">✅ Flashcard saved successfully.</div><?php endif; ?>

  <!-- Filter -->
  <div class="card" style="margin-bottom:1.5rem">
    <div class="card-body" style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap">
      <label class="form-label" style="margin:0;white-space:nowrap">Filter by Lesson:</label>
      <select onchange="location='flashcards.php?lesson='+this.value" class="form-control" style="width:auto;min-width:240px">
        <option value="0">All Lessons</option>
        <?php foreach ($lessons as $l): ?>
        <option value="<?= $l['lesson_id'] ?>" <?= $lessonFilter===$l['lesson_id']?'selected':'' ?>>
          Lesson <?= $l['lesson_number'] ?>: <?= h($l['lesson_title']) ?>
        </option>
        <?php endforeach; ?>
      </select>
      <input id="table-search" type="text" class="form-control" placeholder="Search characters, pinyin, meaning..." style="width:240px"/>
    </div>
  </div>

  <div class="card">
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Lesson</th>
            <th>Chinese</th>
            <th>Pinyin</th>
            <th>Meaning (EN)</th>
            <th>Meaning (BN)</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!$cards): ?>
          <tr><td colspan="7" style="text-align:center;padding:2.5rem;color:var(--muted)">
            No flashcards found. <a href="add_flashcard.php">Add one now →</a>
          </td></tr>
          <?php endif; ?>
          <?php foreach ($cards as $i => $c): ?>
          <tr>
            <td><?= $i+1 ?></td>
            <td><span class="badge badge-blue">L<?= $c['lesson_number'] ?></span></td>
            <td style="font-family:'Noto Sans SC',sans-serif;font-size:1.4rem;color:var(--ink);font-weight:700"><?= h($c['chinese_text']) ?></td>
            <td style="font-style:italic;color:var(--muted)"><?= h($c['pinyin']) ?></td>
            <td><?= h($c['meaning_english']) ?></td>
            <td style="color:var(--muted)"><?= h($c['meaning_bangla'] ?: '—') ?></td>
            <td style="display:flex;gap:0.4rem;flex-wrap:wrap">
              <a href="edit_flashcard.php?id=<?= $c['flashcard_id'] ?>" class="btn btn-gold btn-sm">✏️ Edit</a>
              <a href="flashcards.php?delete=<?= $c['flashcard_id'] ?><?= $lessonFilter?"&lesson=$lessonFilter":'' ?>"
                 class="btn btn-primary btn-sm"
                 onclick="return confirm('Delete this flashcard?')">🗑️</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>
</div>
<script src="../js/main.js"></script>
</body>
</html>
