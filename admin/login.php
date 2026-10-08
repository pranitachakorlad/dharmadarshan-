<?php
require_once __DIR__ . '/auth.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM admin_users WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && $user['username'] === 'Lalitmahajan' && $user['role'] === 'admin' && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_role'] = $user['role'];
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_username'] = $user['username'];
        header('Location: dashboard.php');
        exit;
    }
    $error = 'Invalid username or password.';
}

$pageTitle = 'Admin Login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | <?= e(SITE_NAME) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600&family=Source+Sans+3:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-login-page">
    <div class="login-card">
        <img src="../assets/images/logo.png" alt="Logo" class="login-logo">
        <h1>Admin Login</h1>
        <p class="login-sub"><?= e(SITE_NAME) ?></p>
        <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="post" class="login-form">
            <label>
                Username
                <input type="text" name="username" required autocomplete="username">
            </label>
            <label>
                Password
                <input type="password" name="password" required autocomplete="current-password">
            </label>
            <button type="submit" class="btn btn-primary btn-block">Sign In</button>
        </form>

        <a href="../index.php" class="back-home">Back to website</a>
    </div>
</body>
</html>
