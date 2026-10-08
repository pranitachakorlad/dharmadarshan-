<?php
require_once __DIR__ . '/includes/functions.php';

if (currentUser()) {
    header('Location: account.php');
    exit;
}

$db = getDB();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contactNo = trim($_POST['contact_no'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $db->prepare("SELECT * FROM users WHERE contact_no = ? LIMIT 1");
    $stmt->execute([$contactNo]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_contact_no'] = $user['contact_no'] ?? '';
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_birth_date'] = $user['birth_date'] ?? '';
        $_SESSION['user_birth_time'] = $user['birth_time'] ?? '';
        $_SESSION['user_birth_place'] = $user['birth_place'] ?? '';
        setFlash('success', 'Welcome back, ' . e($user['name']) . '.');
        header('Location: account.php');
        exit;
    }

    $error = 'Invalid contact number or password.';
}

$activePage = 'login';
$pageTitle = 'User Login';
require_once __DIR__ . '/includes/header.php';
?>

<section class="section auth-section">
    <div class="container container-narrow">
        <div class="auth-panel">
            <header class="section-header">
                <h1>User Login</h1>
                <p>Login to manage your cart, wishlist, and future orders.</p>
            </header>

            <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="post" class="admin-form">
                <label>
                    Contact No. *
                    <input type="tel" name="contact_no" required value="<?= e($_POST['contact_no'] ?? '') ?>">
                </label>
                <label>
                    Password *
                    <input type="password" name="password" required autocomplete="current-password">
                </label>
                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>

            <p class="auth-switch">New customer? <a href="register.php">Create an account</a></p>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
