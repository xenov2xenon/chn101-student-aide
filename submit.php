<?php
// quiz/submit.php
// Receives JSON POST: { lesson_id, score, total, answers:[{question_id, selected_answer, is_correct}] }
require_once '../includes/db.php';
header('Content-Type: application/json');

if (!isLoggedIn()) { echo json_encode(['error'=>'Not logged in']); exit; }

$input    = json_decode(file_get_contents('php://input'), true);
$lessonId = (int)($input['lesson_id'] ?? 0);
$score    = (int)($input['score']     ?? 0);
$total    = (int)($input['total']     ?? 0);
$answers  = $input['answers']         ?? [];
$studentId = $_SESSION['student_id'];

if (!$lessonId || $total <= 0) { echo json_encode(['error'=>'Invalid data']); exit; }

// 1. Save quiz attempt
$pdo->prepare("INSERT INTO quiz_attempts (student_id,lesson_id,score,total_questions) VALUES (?,?,?,?)")
    ->execute([$studentId,$lessonId,$score,$total]);
$attemptId = (int)$pdo->lastInsertId();

// 2. Save per-question answers
if (!empty($answers) && $attemptId) {
    $ansStmt = $pdo->prepare("INSERT INTO quiz_answers (attempt_id,question_id,selected_answer,is_correct) VALUES (?,?,?,?)");
    foreach ($answers as $a) {
        $qid = (int)($a['question_id'] ?? 0);
        if ($qid) $ansStmt->execute([$attemptId, $qid, trim($a['selected_answer']??''), $a['is_correct']?1:0]);
    }
}

// 3. Update student_progress best score
$pdo->prepare("INSERT INTO student_progress (student_id,lesson_id,best_score,lessons_completed,last_attempt)
  VALUES (?,?,?,1,NOW())
  ON DUPLICATE KEY UPDATE best_score=GREATEST(best_score,VALUES(best_score)),lessons_completed=lessons_completed+1,last_attempt=NOW()")
  ->execute([$studentId,$lessonId,$score]);

// 4. Points
$pointsEarned = $score * 10 + ($score===$total ? 20 : 0);

// 5. Streak logic
$st = $pdo->prepare("SELECT current_streak,longest_streak,last_quiz_date,total_points FROM students WHERE student_id=?");
$st->execute([$studentId]);
$st = $st->fetch();

$today    = date('Y-m-d');
$lastDate = $st['last_quiz_date'];
$streak   = (int)$st['current_streak'];
$longest  = (int)$st['longest_streak'];

if ($lastDate === $today) {
    $newStreak = $streak;
} elseif ($lastDate === date('Y-m-d', strtotime('-1 day'))) {
    $newStreak = $streak + 1;
} else {
    $newStreak = 1;
}
$newLongest = max($longest, $newStreak);
$newPoints  = $st['total_points'] + $pointsEarned;

$pdo->prepare("UPDATE students SET current_streak=?,longest_streak=?,last_quiz_date=?,total_points=? WHERE student_id=?")
    ->execute([$newStreak,$newLongest,$today,$newPoints,$studentId]);

$_SESSION['student_streak'] = $newStreak;

echo json_encode([
    'success'        => true,
    'attempt_id'     => $attemptId,
    'new_streak'     => $newStreak,
    'longest_streak' => $newLongest,
    'points_earned'  => $pointsEarned,
    'total_points'   => $newPoints,
]);
