<?php
require_once 'includes/db.php';
$pageTitle = 'Register — CHN101';
$error = ''; $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $lang     = in_array($_POST['lang']??'', ['english','bangla']) ? $_POST['lang'] : 'english';

    if (strlen($username) < 3) { $error = 'Username must be at least 3 characters.'; }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $error = 'Invalid email address.'; }
    elseif (strlen($password) < 6) { $error = 'Password must be at least 6 characters.'; }
    else {
        $check = $pdo->prepare("SELECT student_id FROM students WHERE username=? OR email=?");
        $check->execute([$username, $email]);
        if ($check->fetch()) { $error = 'Username or email already exists.'; }
        else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO students (username,email,password_hash,preferred_language) VALUES (?,?,?,?)");
            $stmt->execute([$username, $email, $hash, $lang]);
            $success = 'Account created! <a href="login.php">Login now</a>';
        }
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
      <span class="big-char">学习</span>
      <h2>Create Account</h2>
      <p>Join CHN101 and start your Mandarin journey today</p>
    </div>
    <?php if ($error): ?><div class="alert alert-error">⚠️ <?= $error ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success">✅ <?= $success ?></div><?php endif; ?>
    <form method="POST">
      <div class="form-group">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-control" placeholder="Choose a username" required value="<?= h($_POST['username']??'') ?>"/>
      </div>
      <div class="form-group">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-control" placeholder="your@email.com" required value="<?= h($_POST['email']??'') ?>"/>
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" placeholder="At least 6 characters" required/>
      </div>
      <div class="form-group">
        <label class="form-label">Preferred Study Language</label>
        <select name="lang" class="form-control">
          <option value="english" <?= ($_POST['lang']??'')!=='bangla'?'selected':'' ?>>🇬🇧 English</option>
          <option value="bangla"  <?= ($_POST['lang']??'')==='bangla'?'selected':'' ?>>🇧🇩 Bangla</option>
        </select>
      </div>
      <button type="submit" class="btn btn-gold w-full" style="width:100%;justify-content:center">Register →</button>
    </form>
    <div class="auth-footer">Already have an account? <a href="login.php">Login here</a></div>
  </div>
</div>
<script src="js/main.js"></script>
</body>
</html>
