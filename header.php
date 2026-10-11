<?php
// includes/header.php
require_once __DIR__ . '/db.php';

$studentName  = $_SESSION['student_name']  ?? 'Guest';
$studentStreak = $_SESSION['student_streak'] ?? 0;
$currentPage  = basename($_SERVER['PHP_SELF'], '.php');

// Resolve base path for assets depending on depth
$depth = substr_count($_SERVER['PHP_SELF'], '/') - 1;
$base  = str_repeat('../', max(0, $depth - 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $pageTitle ?? 'CHN101 Student Aide' ?></title>
  <link rel="stylesheet" href="<?= $base ?>css/style.css" />
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🀄</text></svg>" />
</head>
<body>

<nav class="navbar">
  <a href="<?= $base ?>index.php" class="navbar-brand">
    <span class="logo-char">中</span>
    <span class="logo-text">CHN<span>101</span> <em style="font-style:italic;font-weight:300;font-size:0.9em;">Student Aide</em></span>
  </a>

  <ul class="nav-links">
    <li><a href="<?= $base ?>index.php"          class="<?= $currentPage==='index'         ? 'active':'' ?>">🏠 Home</a></li>
    <li><a href="<?= $base ?>flashcards/index.php" class="<?= $currentPage==='index'&&strpos($_SERVER['PHP_SELF'],'flashcard')!==false ? 'active':'' ?>">📚 Flashcards</a></li>
    <li><a href="<?= $base ?>quiz/index.php"       class="<?= strpos($_SERVER['PHP_SELF'],'quiz')!==false ? 'active':'' ?>">✏️ Quiz</a></li>
    <li><a href="<?= $base ?>leaderboard/index.php" class="<?= strpos($_SERVER['PHP_SELF'],'leaderboard')!==false ? 'active':'' ?>">🏆 Leaderboard</a></li>
  </ul>

  <div class="nav-user">
    <?php if (isLoggedIn()): ?>
      <span class="nav-streak">🔥 <?= (int)$studentStreak ?> day streak</span>
      <span style="color:rgba(255,255,255,0.6);font-size:0.88rem;">Hi, <strong style="color:white"><?= h($studentName) ?></strong></span>
      <a href="<?= $base ?>logout.php" class="btn-logout">Logout</a>
    <?php else: ?>
      <a href="<?= $base ?>login.php" class="btn btn-gold btn-sm">Login</a>
      <a href="<?= $base ?>register.php" class="btn btn-outline btn-sm">Register</a>
    <?php endif; ?>
  </div>
</nav>
