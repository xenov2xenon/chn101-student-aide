<?php
$base = '../';
require_once '../includes/db.php';
requireLogin();

$lessonId = (int)($_GET['id'] ?? 0);
if (!$lessonId) { header('Location: index.php'); exit; }

$lesson = $pdo->prepare("SELECT * FROM lessons WHERE lesson_id=?");
$lesson->execute([$lessonId]);
$lesson = $lesson->fetch();
if (!$lesson) { header('Location: index.php'); exit; }

$lang = $_SESSION['preferred_lang'] ?? 'english';
$studentId = $_SESSION['student_id'];

// Handle new personal flashcard submission
$msg = '';
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['add_personal'])) {
    $ch  = trim($_POST['chinese']??'');
    $py  = trim($_POST['pinyin']??'');
    $en  = trim($_POST['meaning_en']??'');
    $bn  = trim($_POST['meaning_bn']??'');
    if ($ch && ($en || $bn)) {
        $pdo->prepare("INSERT INTO personal_flashcards (student_id,lesson_id,chinese_text,pinyin,meaning_english,meaning_bangla) VALUES (?,?,?,?,?,?)")
            ->execute([$studentId,$lessonId,$ch,$py,$en,$bn]);
        $msg = 'Personal flashcard added!';
    }
}

// Delete personal card
if (isset($_GET['delete_personal'])) {
    $pdo->prepare("DELETE FROM personal_flashcards WHERE personal_fc_id=? AND student_id=?")->execute([(int)$_GET['delete_personal'],$studentId]);
    header("Location: lesson.php?id=$lessonId&deleted=1"); exit;
}

// Fetch official flashcards
$official = $pdo->prepare("SELECT * FROM flashcards WHERE lesson_id=? ORDER BY flashcard_id");
$official->execute([$lessonId]);
$official = $official->fetchAll();

// Fetch personal flashcards
$personal = $pdo->prepare("SELECT * FROM personal_flashcards WHERE student_id=? AND lesson_id=? ORDER BY created_at DESC");
$personal->execute([$studentId,$lessonId]);
$personal = $personal->fetchAll();

$allCards = array_merge(
    array_map(fn($c) => array_merge($c, ['type'=>'official']), $official),
    array_map(fn($c) => array_merge($c, ['type'=>'personal', 'flashcard_id'=>'p'.$c['personal_fc_id']]), $personal)
);

$pageTitle = "Lesson {$lesson['lesson_number']}: {$lesson['lesson_title']} — Flashcards";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title><?= $pageTitle ?></title>
  <link rel="stylesheet" href="../css/style.css"/>
  <style>
    .fc-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:1.5rem; }
    .fc-wrap { position:relative; }
    .fc-type-badge { position:absolute; top:10px; right:10px; z-index:10; font-size:0.72rem; }
    .fc-delete { position:absolute; bottom:10px; right:10px; z-index:10; }
    .nav-fc { display:flex; align-items:center; gap:0.75rem; justify-content:center; margin-top:2rem; flex-wrap:wrap; }
    .single-card-area { max-width:420px; margin:0 auto; }
    .mode-btns button { padding:0.5rem 1.2rem; border:2px solid var(--border); border-radius:8px; background:white; cursor:pointer; font-weight:600; font-size:0.88rem; transition:var(--transition); }
    .mode-btns button.active { border-color:var(--gold); background:var(--gold); color:var(--ink); }
  </style>
</head>
<body>

<nav class="navbar">
  <a href="../index.php" class="navbar-brand">
    <span class="logo-char">中</span>
    <span class="logo-text">CHN<span>101</span> <em style="font-style:italic;font-weight:300;font-size:0.9em;">Student Aide</em></span>
  </a>
  <ul class="nav-links">
    <li><a href="../index.php">🏠 Home</a></li>
    <li><a href="index.php" class="active">📚 Flashcards</a></li>
    <li><a href="my_cards.php">✨ My Cards</a></li>
    <li><a href="../quiz/index.php">✏️ Quiz</a></li>
    <li><a href="../leaderboard/index.php">🏆 Leaderboard</a></li>
  </ul>
  <div class="nav-user">
    <span class="nav-streak">🔥 <?= (int)($_SESSION['student_streak']??0) ?></span>
    <span style="color:rgba(255,255,255,0.6);font-size:0.88rem">Hi, <strong style="color:white"><?= h($_SESSION['student_name']) ?></strong></span>
    <a href="../logout.php" class="btn-logout">Logout</a>
  </div>
</nav>

<div class="page-header" data-char="学">
  <div class="container">
    <div class="breadcrumb">
      <a href="../index.php">Home</a> /
      <a href="index.php">Flashcards</a> /
      Lesson <?= $lesson['lesson_number'] ?>
    </div>
    <h1>Lesson <?= $lesson['lesson_number'] ?>: <?= h($lesson['lesson_title']) ?></h1>
    <p><?= h($lesson['description']) ?> &mdash; <?= count($allCards) ?> cards total</p>
  </div>
</div>

<section class="section">
  <div class="container">

    <?php if ($msg || isset($_GET['deleted'])): ?>
    <div class="alert alert-success">✅ <?= $msg ?: 'Card deleted.' ?></div>
    <?php endif; ?>

    <!-- Controls bar -->
    <div class="d-flex justify-between align-center mb-3" style="flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem">
      <div class="mode-btns d-flex" style="gap:0.5rem">
        <button id="btn-browse" class="active" onclick="setMode('browse')">🗂️ Browse All</button>
        <button id="btn-study"  onclick="setMode('study')">🎴 Study Mode</button>
      </div>
      <div class="d-flex align-center" style="gap:0.75rem;flex-wrap:wrap">
        <span style="font-size:0.88rem;color:var(--muted)">Show meanings in:</span>
        <select id="lang-select" class="form-control" style="width:auto" onchange="setLang(this.value)">
          <option value="english" <?= $lang!=='bangla'?'selected':'' ?>>🇬🇧 English</option>
          <option value="bangla"  <?= $lang==='bangla'?'selected':'' ?>>🇧🇩 Bangla</option>
        </select>
        <button onclick="openModal('add-card-modal')" class="btn btn-jade btn-sm">➕ Add Personal Card</button>
        <a href="../quiz/take.php?lesson=<?= $lessonId ?>" class="btn btn-primary btn-sm">✏️ Take Quiz</a>
      </div>
    </div>

    <!-- BROWSE MODE -->
    <div id="browse-area">
      <?php if (!$allCards): ?>
      <div class="card" style="text-align:center;padding:3rem;color:var(--muted)">
        No flashcards yet for this lesson. Be the first to add one!
      </div>
      <?php else: ?>
      <div class="fc-grid">
        <?php foreach ($allCards as $card): ?>
        <div class="fc-wrap">
          <?php if ($card['type']==='personal'): ?>
            <span class="fc-type-badge badge badge-green">✨ Personal</span>
          <?php else: ?>
            <span class="fc-type-badge badge badge-blue">📖 Official</span>
          <?php endif; ?>

          <div class="flashcard-scene">
            <div class="flashcard">
              <div class="flashcard-front">
                <div class="fc-char"><?= h($card['chinese_text']) ?></div>
                <div class="fc-pinyin"><?= h($card['pinyin']??'') ?></div>
              </div>
              <div class="flashcard-back">
                <div class="fc-meaning meaning-en <?= $lang==='bangla'?'hidden':'' ?>"><?= h($card['meaning_english']??'') ?></div>
                <div class="fc-meaning meaning-bn <?= $lang!=='bangla'?'hidden':'' ?>"><?= h($card['meaning_bangla'] ?: $card['meaning_english']) ?></div>
                <div style="font-size:0.78rem;margin-top:0.75rem;opacity:0.7"><?= h($card['pinyin']??'') ?></div>
              </div>
            </div>
          </div>
          <div class="flashcard-hint">Click card to flip</div>

          <?php if ($card['type']==='personal'): ?>
          <div class="fc-delete">
            <a href="lesson.php?id=<?= $lessonId ?>&delete_personal=<?= $card['personal_fc_id'] ?>"
               class="btn btn-primary btn-sm" style="padding:0.25rem 0.6rem;font-size:0.72rem"
               onclick="return confirm('Delete this card?')">🗑️</a>
          </div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <!-- STUDY MODE (one card at a time) -->
    <div id="study-area" class="hidden">
      <div class="single-card-area">
        <div style="text-align:center;margin-bottom:1rem">
          <span id="study-counter" style="color:var(--muted);font-size:0.9rem">Card 1 / <?= count($allCards) ?></span>
          <div class="progress-bar-wrap" style="margin-top:0.5rem">
            <div class="progress-bar-fill" id="study-progress" style="width:0%"></div>
          </div>
        </div>

        <div class="flashcard-scene" id="study-card-scene">
          <div class="flashcard" id="study-card">
            <div class="flashcard-front">
              <div class="fc-char" id="s-char">—</div>
              <div class="fc-pinyin" id="s-pinyin">—</div>
            </div>
            <div class="flashcard-back">
              <div class="fc-meaning" id="s-meaning">—</div>
              <div style="font-size:0.78rem;margin-top:0.75rem;opacity:0.7" id="s-pinyin-back">—</div>
            </div>
          </div>
        </div>
        <div class="flashcard-hint">Click card to flip</div>

        <div class="nav-fc">
          <button onclick="prevCard()" class="btn btn-dark">← Prev</button>
          <button onclick="nextCard()" class="btn btn-gold">Next →</button>
          <button onclick="shuffleCards()" class="btn btn-jade">🔀 Shuffle</button>
        </div>
      </div>
    </div>

  </div>
</section>

<!-- ADD PERSONAL CARD MODAL -->
<div class="modal-overlay" id="add-card-modal">
  <div class="modal">
    <div class="modal-header">
      <h3>✨ Add Personal Flashcard</h3>
      <button class="modal-close">×</button>
    </div>
    <form method="POST">
      <div class="form-group">
        <label class="form-label">Chinese Text *</label>
        <input type="text" name="chinese" class="form-control" required placeholder="e.g. 朋友" style="font-size:1.4rem"/>
      </div>
      <div class="form-group">
        <label class="form-label">Pinyin</label>
        <input type="text" name="pinyin" class="form-control" placeholder="e.g. péngyǒu"/>
      </div>
      <div class="form-group">
        <label class="form-label">Meaning (English)</label>
        <input type="text" name="meaning_en" class="form-control" placeholder="e.g. Friend"/>
      </div>
      <div class="form-group">
        <label class="form-label">Meaning (Bangla)</label>
        <input type="text" name="meaning_bn" class="form-control" placeholder="বাংলায় অর্থ"/>
      </div>
      <button type="submit" name="add_personal" class="btn btn-jade w-full" style="width:100%;justify-content:center">Add Card ✓</button>
    </form>
  </div>
</div>

<footer>
  <span class="footer-char">中</span>
  <p>CHN101 Student Aide &copy; <?= date('Y') ?></p>
</footer>

<script src="../js/main.js"></script>
<script>
// All cards data for study mode
const cards = <?= json_encode(array_map(fn($c) => [
  'char'    => $c['chinese_text'],
  'pinyin'  => $c['pinyin'] ?? '',
  'en'      => $c['meaning_english'] ?? '',
  'bn'      => $c['meaning_bangla'] ?? $c['meaning_english'] ?? '',
  'type'    => $c['type']
], $allCards)) ?>;

let currentIdx = 0;
let studyCards = [...cards];
let currentLang = '<?= $lang ?>';

function setMode(mode) {
  document.getElementById('browse-area').classList.toggle('hidden', mode==='study');
  document.getElementById('study-area').classList.toggle('hidden', mode==='browse');
  document.getElementById('btn-browse').classList.toggle('active', mode==='browse');
  document.getElementById('btn-study').classList.toggle('active', mode==='study');
  if (mode==='study') { currentIdx=0; renderStudyCard(); }
}

function renderStudyCard() {
  if (!studyCards.length) return;
  const card = studyCards[currentIdx];
  const sc = document.getElementById('study-card');
  sc.classList.remove('flipped');
  document.getElementById('s-char').textContent    = card.char;
  document.getElementById('s-pinyin').textContent  = card.pinyin || '—';
  document.getElementById('s-pinyin-back').textContent = card.pinyin || '';
  const meaning = currentLang==='bangla' && card.bn ? card.bn : card.en;
  document.getElementById('s-meaning').textContent = meaning || '—';
  document.getElementById('study-counter').textContent = `Card ${currentIdx+1} / ${studyCards.length}`;
  const pct = ((currentIdx+1)/studyCards.length*100).toFixed(0);
  document.getElementById('study-progress').style.width = pct+'%';
}

function nextCard() {
  if (currentIdx < studyCards.length-1) { currentIdx++; renderStudyCard(); }
  else { currentIdx=0; renderStudyCard(); }
}
function prevCard() {
  if (currentIdx > 0) { currentIdx--; renderStudyCard(); }
}
function shuffleCards() {
  for (let i=studyCards.length-1;i>0;i--) {
    const j=Math.floor(Math.random()*(i+1));
    [studyCards[i],studyCards[j]]=[studyCards[j],studyCards[i]];
  }
  currentIdx=0; renderStudyCard();
}
function setLang(lang) {
  currentLang = lang;
  // Update browse cards
  document.querySelectorAll('.meaning-en').forEach(e=>e.classList.toggle('hidden',lang==='bangla'));
  document.querySelectorAll('.meaning-bn').forEach(e=>e.classList.toggle('hidden',lang!=='bangla'));
  // Update study card
  renderStudyCard();
  // Save to server
  fetch('../ajax/set_lang.php?lang='+lang);
}

// Init study card
if (studyCards.length) renderStudyCard();
</script>
</body>
</html>
