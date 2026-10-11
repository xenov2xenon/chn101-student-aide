<?php
$pageTitle = 'Edit Flashcard — Admin';
require_once '../includes/db.php';
requireAdmin();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: flashcards.php'); exit; }

$lessons = $pdo->query("SELECT * FROM lessons ORDER BY lesson_number")->fetchAll();
$error = '';

$stmt = $pdo->prepare("SELECT * FROM flashcards WHERE flashcard_id=?");
$stmt->execute([$id]);
$card = $stmt->fetch();
if (!$card) { header('Location: flashcards.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lessonId = (int)($_POST['lesson_id'] ?? 0);
    $chinese  = trim($_POST['chinese']    ?? '');
    $pinyin   = trim($_POST['pinyin']     ?? '');
    $en       = trim($_POST['meaning_en'] ?? '');
    $bn       = trim($_POST['meaning_bn'] ?? '');

    if (!$lessonId || !$chinese || !$en) {
        $error = 'Please fill in all required fields.';
    } else {
        $pdo->prepare("UPDATE flashcards SET lesson_id=?,chinese_text=?,pinyin=?,meaning_english=?,meaning_bangla=? WHERE flashcard_id=?")
            ->execute([$lessonId,$chinese,$pinyin,$en,$bn,$id]);
        header('Location: flashcards.php?saved=1'); exit;
    }
    $card = array_merge($card,['lesson_id'=>$lessonId,'chinese_text'=>$chinese,'pinyin'=>$pinyin,'meaning_english'=>$en,'meaning_bangla'=>$bn]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title><?= $pageTitle ?></title>
  <link rel="stylesheet" href="../css/style.css"/>
  <style>
    .preview-card { background:var(--ink);color:white;border-radius:16px;padding:2.5rem;text-align:center;min-height:180px;display:flex;flex-direction:column;align-items:center;justify-content:center; }
    .preview-char { font-family:'Noto Sans SC',sans-serif;font-size:3.5rem;line-height:1.1; }
    .preview-pinyin { color:rgba(255,255,255,0.6);font-style:italic;margin-top:0.4rem; }
    .preview-back { background:var(--gold);color:var(--ink);border-radius:16px;padding:2rem;text-align:center;min-height:120px;display:flex;align-items:center;justify-content:center;font-family:'Playfair Display',serif;font-size:1.6rem;font-weight:700; }
  </style>
</head>
<body>
<div class="admin-layout">
<aside class="admin-sidebar">
  <div class="sidebar-brand"><h2>🛠️ Admin Panel</h2><p><?= h($_SESSION['admin_name']) ?></p></div>
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
    <h1>✏️ Edit Flashcard #<?= $id ?></h1>
    <a href="flashcards.php" class="btn btn-dark btn-sm">← Back</a>
  </div>
  <?php if ($error): ?><div class="alert alert-error">⚠️ <?= h($error) ?></div><?php endif; ?>

  <div style="display:grid;grid-template-columns:1fr 320px;gap:1.5rem;align-items:start">
    <form method="POST">
      <div class="card">
        <div class="card-header"><h3>📇 Edit Flashcard</h3></div>
        <div class="card-body">
          <div class="form-group">
            <label class="form-label">Lesson *</label>
            <select name="lesson_id" class="form-control" required>
              <?php foreach ($lessons as $l): ?>
              <option value="<?= $l['lesson_id'] ?>" <?= $card['lesson_id']==$l['lesson_id']?'selected':'' ?>>
                Lesson <?= $l['lesson_number'] ?>: <?= h($l['lesson_title']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Chinese Text *</label>
            <input type="text" name="chinese" id="f-chinese" class="form-control" required
                   value="<?= h($card['chinese_text']) ?>" oninput="updatePreview()"
                   style="font-family:'Noto Sans SC',sans-serif;font-size:1.5rem"/>
          </div>
          <div class="form-group">
            <label class="form-label">Pinyin</label>
            <input type="text" name="pinyin" id="f-pinyin" class="form-control"
                   value="<?= h($card['pinyin']) ?>" oninput="updatePreview()"/>
          </div>
          <div class="form-group">
            <label class="form-label">Meaning (English) *</label>
            <input type="text" name="meaning_en" id="f-en" class="form-control" required
                   value="<?= h($card['meaning_english']) ?>" oninput="updatePreview()"/>
          </div>
          <div class="form-group">
            <label class="form-label">Meaning (Bangla)</label>
            <input type="text" name="meaning_bn" id="f-bn" class="form-control"
                   value="<?= h($card['meaning_bangla']) ?>" oninput="updatePreview()"/>
          </div>
          <button type="submit" class="btn btn-gold">💾 Update Flashcard</button>
        </div>
      </div>
    </form>

    <div class="card">
      <div class="card-header"><h3>👁️ Preview</h3></div>
      <div class="card-body" style="display:flex;flex-direction:column;gap:1rem">
        <div>
          <div style="font-size:0.75rem;color:var(--muted);margin-bottom:0.5rem;text-transform:uppercase;letter-spacing:0.08em">Front</div>
          <div class="preview-card">
            <div class="preview-char" id="prev-char"><?= h($card['chinese_text']) ?></div>
            <div class="preview-pinyin" id="prev-pinyin"><?= h($card['pinyin']) ?></div>
          </div>
        </div>
        <div>
          <div style="font-size:0.75rem;color:var(--muted);margin-bottom:0.5rem;text-transform:uppercase;letter-spacing:0.08em">Back</div>
          <div class="preview-back" id="prev-meaning"><?= h($card['meaning_english']) ?><?= $card['meaning_bangla']?' / '.h($card['meaning_bangla']):'' ?></div>
        </div>
      </div>
    </div>
  </div>
</main>
</div>
<script src="../js/main.js"></script>
<script>
function updatePreview() {
  const ch = document.getElementById('f-chinese').value || '—';
  const py = document.getElementById('f-pinyin').value  || '';
  const en = document.getElementById('f-en').value      || '—';
  const bn = document.getElementById('f-bn').value      || '';
  document.getElementById('prev-char').textContent    = ch;
  document.getElementById('prev-pinyin').textContent  = py;
  document.getElementById('prev-meaning').textContent = bn ? en+' / '+bn : en;
}
</script>
</body>
</html>
