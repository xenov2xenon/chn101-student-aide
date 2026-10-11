<?php
$pageTitle = 'Add Flashcard — Admin';
require_once '../includes/db.php';
requireAdmin();

$lessons = $pdo->query("SELECT * FROM lessons ORDER BY lesson_number")->fetchAll();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lessonId = (int)($_POST['lesson_id'] ?? 0);
    $chinese  = trim($_POST['chinese']  ?? '');
    $pinyin   = trim($_POST['pinyin']   ?? '');
    $en       = trim($_POST['meaning_en'] ?? '');
    $bn       = trim($_POST['meaning_bn'] ?? '');

    if (!$lessonId || !$chinese || !$en) {
        $error = 'Please fill in the required fields: Lesson, Chinese text, and English meaning.';
    } else {
        $pdo->prepare("INSERT INTO flashcards (lesson_id,chinese_text,pinyin,meaning_english,meaning_bangla,is_official) VALUES (?,?,?,?,?,1)")
            ->execute([$lessonId, $chinese, $pinyin, $en, $bn]);
        header('Location: flashcards.php?saved=1'); exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title><?= $pageTitle ?></title>
  <link rel="stylesheet" href="../css/style.css"/>
  <style>
    .preview-card {
      background: var(--ink); color: white; border-radius: 16px;
      padding: 2.5rem; text-align: center; min-height: 180px;
      display:flex;flex-direction:column;align-items:center;justify-content:center;
    }
    .preview-char { font-family:'Noto Sans SC',sans-serif;font-size:3.5rem;line-height:1.1; }
    .preview-pinyin { color:rgba(255,255,255,0.6);font-style:italic;margin-top:0.4rem; }
    .preview-back {
      background: var(--gold); color: var(--ink); border-radius: 16px;
      padding: 2rem; text-align: center; min-height: 120px;
      display:flex;align-items:center;justify-content:center;
      font-family:'Playfair Display',serif;font-size:1.6rem;font-weight:700;
    }
  </style>
</head>
<body>
<div class="admin-layout">
<aside class="admin-sidebar">
  <div class="sidebar-brand">
    <h2>🛠️ Admin Panel</h2>
    <p><?= h($_SESSION['admin_name']) ?></p>
  </div>
  <ul class="sidebar-nav">
    <li><a href="index.php"><span class="nav-icon">📊</span> Dashboard</a></li>
    <li><a href="students.php"><span class="nav-icon">👥</span> All Students</a></li>
    <li><a href="flashcards.php"><span class="nav-icon">📇</span> Flashcards</a></li>
    <li><a href="add_flashcard.php" class="active"><span class="nav-icon">➕</span> Add Flashcard</a></li>
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
    <h1>➕ Add Official Flashcard</h1>
    <a href="flashcards.php" class="btn btn-dark btn-sm">← Back to Flashcards</a>
  </div>

  <?php if ($error): ?><div class="alert alert-error">⚠️ <?= h($error) ?></div><?php endif; ?>

  <div style="display:grid;grid-template-columns:1fr 320px;gap:1.5rem;align-items:start">

    <!-- FORM -->
    <form method="POST">
      <div class="card">
        <div class="card-header"><h3>📇 Flashcard Details</h3></div>
        <div class="card-body">
          <div class="form-group">
            <label class="form-label">Lesson *</label>
            <select name="lesson_id" id="f-lesson" class="form-control" required>
              <option value="">Select a lesson</option>
              <?php foreach ($lessons as $l): ?>
              <option value="<?= $l['lesson_id'] ?>" <?= ($_POST['lesson_id']??'')==$l['lesson_id']?'selected':'' ?>>
                Lesson <?= $l['lesson_number'] ?>: <?= h($l['lesson_title']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label">Chinese Text (汉字) *</label>
            <input type="text" name="chinese" id="f-chinese" class="form-control" required
                   placeholder="e.g. 你好" oninput="updatePreview()"
                   value="<?= h($_POST['chinese']??'') ?>"
                   style="font-family:'Noto Sans SC',sans-serif;font-size:1.5rem"/>
          </div>

          <div class="form-group">
            <label class="form-label">Pinyin (Romanisation)</label>
            <input type="text" name="pinyin" id="f-pinyin" class="form-control"
                   placeholder="e.g. Nǐ hǎo" oninput="updatePreview()"
                   value="<?= h($_POST['pinyin']??'') ?>"/>
          </div>

          <div class="form-group">
            <label class="form-label">Meaning in English *</label>
            <input type="text" name="meaning_en" id="f-en" class="form-control" required
                   placeholder="e.g. Hello" oninput="updatePreview()"
                   value="<?= h($_POST['meaning_en']??'') ?>"/>
          </div>

          <div class="form-group">
            <label class="form-label">Meaning in Bangla</label>
            <input type="text" name="meaning_bn" id="f-bn" class="form-control"
                   placeholder="e.g. হ্যালো" oninput="updatePreview()"
                   value="<?= h($_POST['meaning_bn']??'') ?>"/>
          </div>

          <div style="display:flex;gap:0.75rem;margin-top:0.5rem">
            <button type="submit" class="btn btn-primary">💾 Save Flashcard</button>
            <button type="reset" onclick="clearPreview()" class="btn btn-dark">🔄 Clear</button>
          </div>
        </div>
      </div>
    </form>

    <!-- LIVE PREVIEW -->
    <div>
      <div class="card">
        <div class="card-header"><h3>👁️ Live Preview</h3></div>
        <div class="card-body" style="display:flex;flex-direction:column;gap:1rem">
          <div>
            <div style="font-size:0.75rem;color:var(--muted);margin-bottom:0.5rem;text-transform:uppercase;letter-spacing:0.08em">Front (Chinese)</div>
            <div class="preview-card">
              <div class="preview-char" id="prev-char">你好</div>
              <div class="preview-pinyin" id="prev-pinyin">Nǐ hǎo</div>
            </div>
          </div>
          <div>
            <div style="font-size:0.75rem;color:var(--muted);margin-bottom:0.5rem;text-transform:uppercase;letter-spacing:0.08em">Back (Meaning)</div>
            <div class="preview-back" id="prev-meaning">Hello / হ্যালো</div>
          </div>
        </div>
      </div>

      <div class="card" style="margin-top:1rem">
        <div class="card-body">
          <p style="font-size:0.85rem;color:var(--muted);line-height:1.6">
            <strong>Tips:</strong><br>
            • Use proper tone marks in Pinyin (ā á ǎ à)<br>
            • Bangla meaning is optional but helps Bangla-language students<br>
            • Official cards appear for all students in that lesson
          </p>
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
  document.getElementById('prev-meaning').textContent = bn ? en + ' / ' + bn : en;
}
function clearPreview() {
  document.getElementById('prev-char').textContent    = '—';
  document.getElementById('prev-pinyin').textContent  = '';
  document.getElementById('prev-meaning').textContent = '—';
}
// Init preview with POST values if any
updatePreview();
</script>
</body>
</html>
