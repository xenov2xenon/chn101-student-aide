// js/main.js - CHN101 Student Aide

// ── MODAL HELPERS ──
function openModal(id) {
  const m = document.getElementById(id);
  if (m) m.classList.add('show');
}
function closeModal(id) {
  const m = document.getElementById(id);
  if (m) m.classList.remove('show');
}
document.addEventListener('click', (e) => {
  if (e.target.classList.contains('modal-overlay')) {
    e.target.classList.remove('show');
  }
  if (e.target.classList.contains('modal-close')) {
    e.target.closest('.modal-overlay').classList.remove('show');
  }
});

// ── FLASHCARD FLIP ──
document.addEventListener('click', (e) => {
  const card = e.target.closest('.flashcard');
  if (card) card.classList.toggle('flipped');
});

// ── QUIZ LOGIC ──
let currentQuestion = 0;
let answers = [];
let quizData = [];
let selectedOption = null;

function loadQuestion() {
  if (!quizData.length) return;
  const q = quizData[currentQuestion];
  const lang = document.getElementById('quiz-lang')?.value || 'english';

  document.getElementById('q-counter').textContent = `${currentQuestion + 1} / ${quizData.length}`;
  document.getElementById('q-progress').style.width = `${((currentQuestion) / quizData.length) * 100}%`;
  document.getElementById('q-text').textContent = lang === 'bangla' && q.question_text_bangla ? q.question_text_bangla : q.question_text_english;

  const opts = [
    { letter: 'A', eng: q.correct_answer_english, ban: q.correct_answer_bangla, idx: 0 },
    { letter: 'B', eng: q.option_b_english, ban: q.option_b_bangla, idx: 1 },
    { letter: 'C', eng: q.option_c_english, ban: q.option_c_bangla, idx: 2 },
    { letter: 'D', eng: q.option_d_english, ban: q.option_d_bangla, idx: 3 },
  ];
  // shuffle
  for (let i = opts.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [opts[i], opts[j]] = [opts[j], opts[i]];
  }
  q._shuffled = opts;

  const container = document.getElementById('options-container');
  container.innerHTML = '';
  opts.forEach((opt) => {
    const label = lang === 'bangla' && opt.ban ? opt.ban : opt.eng;
    const btn = document.createElement('div');
    btn.className = 'quiz-option';
    btn.dataset.idx = opt.idx;
    btn.innerHTML = `<span class="option-letter">${opt.letter}</span><span>${label}</span>`;
    btn.addEventListener('click', () => selectOption(btn, opt.idx === 0));
    container.appendChild(btn);
  });

  selectedOption = null;
  document.getElementById('btn-next')?.classList.add('hidden');
}

function selectOption(btn, isCorrect) {
  if (selectedOption !== null) return;
  selectedOption = isCorrect;
  answers.push(isCorrect ? 1 : 0);

  document.querySelectorAll('.quiz-option').forEach(o => {
    if (o === btn) {
      o.classList.add(isCorrect ? 'correct' : 'wrong');
    } else if (Number(o.dataset.idx) === 0) {
      o.classList.add('correct');
    }
  });

  document.getElementById('btn-next')?.classList.remove('hidden');
}

function nextQuestion() {
  currentQuestion++;
  if (currentQuestion >= quizData.length) {
    submitQuiz();
  } else {
    loadQuestion();
  }
}

function submitQuiz() {
  const score = answers.reduce((a, b) => a + b, 0);
  const lessonId = document.getElementById('lesson-id')?.value;
  const lang = document.getElementById('quiz-lang')?.value || 'english';

  fetch('submit_quiz.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ lesson_id: lessonId, score, total: quizData.length, answers })
  })
  .then(r => r.json())
  .then(data => {
    showResult(score, quizData.length, data);
  })
  .catch(() => showResult(score, quizData.length, {}));
}

function showResult(score, total, data) {
  const pct = Math.round((score / total) * 100);
  document.getElementById('quiz-area')?.classList.add('hidden');
  const result = document.getElementById('result-area');
  if (result) {
    result.classList.remove('hidden');
    result.querySelector('.score-num').textContent = `${score}/${total}`;
    const circle = result.querySelector('.score-circle');
    if (circle) circle.style.setProperty('--pct', `${pct * 3.6}deg`);
    result.querySelector('.score-pct').textContent = `${pct}%`;
    if (data.new_streak) result.querySelector('.streak-update').textContent = `🔥 Streak: ${data.new_streak} days!`;
  }
}

// ── LANG TOGGLE ──
document.addEventListener('change', (e) => {
  if (e.target.id === 'lang-toggle') {
    const lang = e.target.value;
    document.querySelectorAll('.meaning-en').forEach(el => el.classList.toggle('hidden', lang === 'bangla'));
    document.querySelectorAll('.meaning-bn').forEach(el => el.classList.toggle('hidden', lang === 'english'));
  }
});

// ── ANIMATE ON LOAD ──
document.querySelectorAll('.animate-fadeup').forEach((el, i) => {
  el.style.opacity = '0';
  el.style.animationDelay = `${i * 0.08}s`;
  el.style.animationFillMode = 'forwards';
});

// ── ALERT AUTO HIDE ──
document.querySelectorAll('.alert').forEach(a => {
  if (!a.dataset.persist) setTimeout(() => a.style.opacity = '0', 4000);
});

// ── SEARCH / FILTER ──
const searchInput = document.getElementById('table-search');
if (searchInput) {
  searchInput.addEventListener('input', () => {
    const q = searchInput.value.toLowerCase();
    document.querySelectorAll('tbody tr').forEach(row => {
      row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
  });
}
