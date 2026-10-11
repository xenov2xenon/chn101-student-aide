<?php
// ajax/get_questions.php
require_once '../includes/db.php';
header('Content-Type: application/json');

if (!isLoggedIn()) { echo json_encode(['error'=>'Not logged in']); exit; }

$lessonId = (int)($_GET['lesson'] ?? 0);
if (!$lessonId) { echo json_encode(['error'=>'Invalid lesson']); exit; }

$stmt = $pdo->prepare("SELECT * FROM quiz_questions WHERE lesson_id=? ORDER BY RAND()");
$stmt->execute([$lessonId]);
$questions = $stmt->fetchAll();

echo json_encode(['questions' => $questions, 'total' => count($questions)]);
