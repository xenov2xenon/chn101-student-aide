<?php
$pageTitle = 'My Flashcards — CHN101';
$base = '../';
require_once '../includes/db.php';
requireLogin();

$studentId = $_SESSION['student_id'];
$lang = $_SESSION['preferred_lang'] ?? 'english';

// Delete
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM personal_flashcards WHERE personal_fc_id=? AND student_id=?")
        ->execute([(int)$_GET['delete'], $studentId]);
    header('Location: my_cards.php?deleted=1'); exit;
}

// Filter by lesson
$lessonFilter = (int)($_GET['lesson'] ?? 0);
$lessons = $pdo->query("
  SELECT DISTINCT l.lesson_id, l.lesson_title, l.lesson_number
  FROM lessons l
  JOIN personal_flashcards pf ON pf.lesson_id=l.lesson_id
  WHERE pf.student_id=$studentId
  ORDER BY l.lesson_number
")->fetchAll();

if ($lessonFilter) {
    $stmt = $pdo->prepare("SELECT pf.*, l.lesson_title, l.lesson_number FROM personal_flashcards pf JOIN lessons l ON l.lesson_id=pf.lesson_id WHERE pf.student_id=? AND pf.lesson_id=? ORDER BY pf.created_at DESC");
    $stmt->execute([$studentId,$lessonFilter]);
} else {
    $stmt = $pdo->prepare("SELECT pf.*, l.lesson_title, l.lesson_number FROM personal_flashcards pf JOIN lessons l ON l.lesson_id=pf.lesson_id WHERE pf.student_id=? ORDER BY pf.created_at DESC");
    $stmt->execute([$studentId]);
}
$cards = $stmt->fetchAll();

// All lessons for the add modal
$allLessons = $pdo->query("SELECT * FROM lessons ORDER BY lesson_number")->fetchAll();

$msg = '';
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['add_card'])) {
    $lid = (int)$_POST['lesson_id'];
    $ch  = trim($_POST['chinese']    ?? '');
    $py  = trim($_POST['pinyin']     ?? '');
    $en  = trim($_POST['meaning_en'] ?? '');
    $bn  = trim($_POST['meaning_bn'] ?? '');
    if ($lid && $ch && ($en || $bn)) {
        $pdo->prepare("INSERT INTO personal_flashcards (student_id,lesson_id,chinese_text,pinyin,meaning_english,meaning_bangla) VALUES (?,?,?,?,?,?)")
            ->execute([$studentId,$lid,$ch,$py,$en,$bn]);
        $msg = 'Flashcard added successfully!';
        header("Location: my_cards.php?added=1"); exit;
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
    .fc-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:1.25rem; }
    .fc-wrap { position:relative; }
    .fc-actions { display:flex;justify-content:space-between;align-items:center;margin-top:0.5rem;padding:0 0.25rem; }
    .fc-meta { font-size:0.75rem;color:var(--muted); }
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
    <li><a href="index.php" class="active">📚 Flashcards</a></li>
    <li><a href="../quiz/index.php">✏️ Quiz</a></li>
    <li><a href="../leaderboard/index.php">🏆 Leaderboard</a></li>
    <li><a href="../profile.php">👤 Profile</a></li>
  </ul>
  <div class="nav-user">
    <span class="nav-streak">🔥 <?= (int)($_SESSION['student_streak']??0) ?></span>
    <a href="../logout.php" class="btn-logout">Logout</a>
  </div>
</nav>

<div class="page-header" data-char="我">
  <div class="container">
    <div class="breadcrumb"><a href="../index.php">Home</a> / <a href="index.php">Flashcards</a> / My Cards</div>
    <h1>✨ My Personal Flashcards</h1>
    <p>Your custom study cards across all lessons — <?= count($cards) ?> card<?= count($cards)!=1?'s':'' ?> created</p>
  </div>
</div>

<section class="section">
  <div class="container">

    <?php if (isset($_GET['added'])): ?><div class="alert alert-success">✅ Flashcard added!</div><?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">✅ Flashcard deleted.</div><?php endif; ?>

    <!-- Toolbar -->
    <div class="d-flex justify-between align-center mb-3" style="flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem">
      <div style="display:flex;align-items:center;gap:0.75rem;flex-wrap:wrap">
        <label style="font-size:0.88rem;color:var(--muted)">Filter:</label>
        <select onchange="location='my_cards.php?lesson='+this.value" class="form-control" style="width:auto">
          <option value="0">All Lessons</option>
          <?php foreach ($lessons as $l): ?>
          <option value="<?= $l['lesson_id'] ?>" <?= $lessonFilter==$l['lesson_id']?'selected':'' ?>>
            L<?= $l['lesson_number'] ?>: <?= h($l['lesson_title']) ?>
          </option>
          <?php endforeach; ?>
        </select>
        <select id="lang-toggle-my" class="form-control" style="width:auto" onchange="toggleLang(this.value)">
          <option value="english" <?= $lang!=='bangla'?'selected':'' ?>>🇬🇧 English</option>
          <option value="bangla"  <?= $lang==='bangla'?'selected':'' ?>>🇧🇩 Bangla</option>
        </select>
      </div>
      <button onclick="openModal('add-card-modal')" class="btn btn-jade">➕ Add New Card</button>
    </div>

    <?php if (!$cards): ?>
    <div class="card" style="text-align:center;padding:4rem 2rem">
      <div style="font-size:3rem;margin-bottom:1rem">📇</div>
      <h3 style="font-family:'Playfair Display',serif;margin-bottom:0.5rem">No personal cards yet</h3>
      <p style="color:var(--muted);margin-bottom:1.5rem">Create your own flashcards to supplement the official lessons.</p>
      <button onclick="openModal('add-card-modal')" class="btn btn-jade">➕ Create Your First Card</button>
    </div>
    <?php else: ?>

    <!-- Group by lesson -->
    <?php
    $grouped = [];
    foreach ($cards as $c) {
        $key = $c['lesson_id'];
        $grouped[$key]['meta'] = ['title'=>$c['lesson_title'],'num'=>$c['lesson_number']];
        $grouped[$key]['cards'][] = $c;
    }
    ?>
    <?php foreach ($grouped as $lid => $group): ?>
    <div style="margin-bottom:2.5rem">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem">
        <div style="display:flex;align-items:center;gap:0.75rem">
          <span class="lesson-num" style="display:inline-block">L<?= $group['meta']['num'] ?></span>
          <h3 style="font-family:'Playfair Display',serif;font-size:1.1rem"><?= h($group['meta']['title']) ?></h3>
          <span class="badge badge-gold"><?= count($group['cards']) ?> card<?= count($group['cards'])!=1?'s':'' ?></span>
        </div>
        <a href="lesson.php?id=<?= $lid ?>" class="btn btn-dark btn-sm">Study Lesson →</a>
      </div>

      <div class="fc-grid">
        <?php foreach ($group['cards'] as $card): ?>
        <div class="fc-wrap">
          <div class="flashcard-scene">
            <div class="flashcard">
              <div class="flashcard-front">
                <div class="fc-char"><?= h($card['chinese_text']) ?></div>
                <div class="fc-pinyin"><?= h($card['pinyin'] ?? '') ?></div>
              </div>
              <div class="flashcard-back">
                <div class="fc-meaning meaning-en <?= $lang==='bangla'?'hidden':'' ?>"><?= h($card['meaning_english'] ?? '—') ?></div>
                <div class="fc-meaning meaning-bn <?= $lang!=='bangla'?'hidden':'' ?>"><?= h($card['meaning_bangla'] ?: $card['meaning_english'] ?: '—') ?></div>
                <div style="font-size:0.75rem;margin-top:0.5rem;opacity:0.7"><?= h($card['pinyin']??'') ?></div>
              </div>
            </div>
          </div>
          <div class="fc-actions">
            <span class="fc-meta">Added <?= date('M j',strtotime($card['created_at'])) ?></span>
            <a href="my_cards.php?delete=<?= $card['personal_fc_id'] ?><?= $lessonFilter?"&lesson=$lessonFilter":'' ?>"
               class="btn btn-primary btn-sm" style="padding:0.3rem 0.6rem;font-size:0.75rem"
               onclick="return confirm('Delete this flashcard?')">🗑️</a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

  </div>
</section>

<!-- ADD CARD MODAL -->
<div class="modal-overlay" id="add-card-modal">
  <div class="modal">
    <div class="modal-header">
      <h3>✨ Add Personal Flashcard</h3>
      <button class="modal-close">×</button>
    </div>
    <form method="POST">
      <div class="form-group">
        <label class="form-label">Lesson *</label>
        <select name="lesson_id" class="form-control" required>
          <option value="">Select a lesson</option>
          <?php foreach ($allLessons as $l): ?>
          <option value="<?= $l['lesson_id'] ?>" <?= $lessonFilter==$l['lesson_id']?'selected':'' ?>>
            Lesson <?= $l['lesson_number'] ?>: <?= h($l['lesson_title']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Chinese Text *</label>
        <input type="text" name="chinese" class="form-control" required placeholder="e.g. 朋友" style="font-size:1.4rem;font-family:'Noto Sans SC',sans-serif"/>
      </div>
      <div class="form-group">
        <label class="form-label">Pinyin</label>
        <input type="text" name="pinyin" class="form-control" placeholder="e.g. péngyǒu"/>
      </div>
      <div class="form-group">
        <label class="form-label">English Meaning</label>
        <input type="text" name="meaning_en" class="form-control" placeholder="e.g. Friend"/>
      </div>
      <div class="form-group">
        <label class="form-label">Bangla Meaning</label>
        <input type="text" name="meaning_bn" class="form-control" placeholder="e.g. বন্ধু"/>
      </div>
      <button type="submit" name="add_card" class="btn btn-jade w-full" style="width:100%;justify-content:center">Save Flashcard ✓</button>
    </form>
  </div>
</div>

<footer>
  <span class="footer-char">中</span>
  <p>CHN101 Student Aide &copy; <?= date('Y') ?></p>
</footer>
<script src="../js/main.js"></script>
<script>
function toggleLang(lang) {
  document.querySelectorAll('.meaning-en').forEach(e=>e.classList.toggle('hidden',lang==='bangla'));
  document.querySelectorAll('.meaning-bn').forEach(e=>e.classList.toggle('hidden',lang!=='bangla'));
  fetch('../ajax/set_lang.php?lang='+lang);
}
</script>
</body>
</html>
