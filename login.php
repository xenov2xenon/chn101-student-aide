<?php
require_once 'includes/db.php';
$pageTitle = 'Login — CHN101';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM students WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $student = $stmt->fetch();

    if ($student && password_verify($password, $student['password_hash'])) {
        $_SESSION['student_id']     = $student['student_id'];
        $_SESSION['student_name']   = $student['username'];
        $_SESSION['student_streak'] = $student['current_streak'];
        $_SESSION['preferred_lang'] = $student['preferred_language'];
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title><?= $pageTitle ?></title>
  <link rel="stylesheet" href="css/style.css"/>
</head>
<body>
<div class="auth-wrap">
  <div class="auth-card animate-fadeup">
    <div class="auth-logo">
      <span class="big-char">你好</span>
      <h2>Welcome Back</h2>
      <p>Sign in to continue your Mandarin journey</p>
    </div>
    <?php if ($error): ?>
      <div class="alert alert-error">⚠️ <?= h($error) ?></div>
    <?php endif; ?>
    <form method="POST">
      <div class="form-group">
        <label class="form-label">Username or Email</label>
        <input type="text" name="username" class="form-control" placeholder="Enter your username" required value="<?= h($_POST['username']??'') ?>"/>
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" placeholder="Enter your password" required/>
      </div>
      <button type="submit" class="btn btn-primary w-full" style="width:100%;justify-content:center;margin-top:0.5rem">Login →</button>
    </form>
    <div class="auth-footer">
      Don't have an account? <a href="register.php">Register here</a><br/>
      <a href="admin_login.php" style="color:var(--muted);margin-top:0.4rem;display:inline-block">Admin Login</a>
    </div>
  </div>
</div>
<script src="js/main.js"></script>
</body>
</html>
