<?php
require_once 'includes/db.php';
$pageTitle = 'Admin Login — CHN101';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    if ($admin && password_verify($password, $admin['password_hash'])) {
        $_SESSION['admin_id']   = $admin['admin_id'];
        $_SESSION['admin_name'] = $admin['username'];
        header('Location: admin/index.php');
        exit;
    } else {
        $error = 'Invalid admin credentials.';
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
      <span class="big-char">管理</span>
      <h2>Admin Portal</h2>
      <p>Restricted access — administrators only</p>
    </div>
    <?php if ($error): ?><div class="alert alert-error">⚠️ <?= h($error) ?></div><?php endif; ?>
    <form method="POST">
      <div class="form-group">
        <label class="form-label">Admin Username</label>
        <input type="text" name="username" class="form-control" placeholder="admin" required/>
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" placeholder="Admin password" required/>
      </div>
      <button type="submit" class="btn btn-primary w-full" style="width:100%;justify-content:center">Enter Admin Panel →</button>
    </form>
    <div class="auth-footer">
      <a href="index.php">← Back to Student Site</a><br/>
      <small style="color:var(--muted)">Default: admin / password</small>
    </div>
  </div>
</div>
<script src="js/main.js"></script>
</body>
</html>
