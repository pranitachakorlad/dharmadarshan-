<?php
require_once __DIR__ . '/includes/functions.php';

if (currentUser()) {
    header('Location: account.php');
    exit;
}

$db = getDB();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $contactNo = trim($_POST['contact_no'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $birthDate = trim($_POST['birth_date'] ?? '');
    $birthTime = trim($_POST['birth_time'] ?? '');
    $birthPlace = trim($_POST['birth_place'] ?? '');

    if ($name === '' || $contactNo === '' || $birthDate === '' || $birthTime === '' || $birthPlace === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
        $error = 'Enter full name, contact number, birth details, valid email, and password with at least 6 characters.';
    } else {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("
                INSERT INTO users (name, contact_no, email, password_hash, birth_date, birth_time, birth_place)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $contactNo, $email, $hash, $birthDate, $birthTime, $birthPlace]);

            $_SESSION['user_id'] = (int) $db->lastInsertId();
            $_SESSION['user_name'] = $name;
            $_SESSION['user_contact_no'] = $contactNo;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_birth_date'] = $birthDate;
            $_SESSION['user_birth_time'] = $birthTime;
            $_SESSION['user_birth_place'] = $birthPlace;
            setFlash('success', 'Account created successfully.');
            header('Location: account.php');
            exit;
        } catch (PDOException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                $error = 'An account already exists with this email.';
            } else {
                throw $e;
            }
        }
    }
}

$activePage = 'login';
$pageTitle = 'Create Account';
require_once __DIR__ . '/includes/header.php';
?>

<section class="section auth-section">
    <div class="container container-narrow">
        <div class="auth-panel">
            <header class="section-header">
                <h1>Create Account</h1>
                <p>Save your wishlist and keep your DharmaDarshan shopping ready.</p>
            </header>

            <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="post" class="admin-form">
                <label>
                    Enter Your Full Name: *
                    <input type="text" name="name" required autocomplete="name" value="<?= e($_POST['name'] ?? '') ?>">
                </label>
                <label>
                    Contact No. *
                    <input type="tel" name="contact_no" required value="<?= e($_POST['contact_no'] ?? '') ?>">
                </label>
                <label>
                    Enter Your Birth Date: *
                    <input type="date" name="birth_date" required value="<?= e($_POST['birth_date'] ?? '') ?>">
                </label>
                <label>
                    Enter Your Birth Time: *
                    <input type="time" name="birth_time" required value="<?= e($_POST['birth_time'] ?? '') ?>">
                </label>
                <label>
                    Enter Your Birth Place: *
                    <input type="text" name="birth_place" required value="<?= e($_POST['birth_place'] ?? '') ?>">
                </label>
                <label>
                    Email *
                    <input type="email" name="email" required autocomplete="email" value="<?= e($_POST['email'] ?? '') ?>">
                </label>
                <label>
                    Password *
                    <input type="password" name="password" required autocomplete="new-password" minlength="6">
                </label>
                <button type="submit" class="btn btn-primary btn-block">Create Account</button>
            </form>

            <p class="auth-switch">Already registered? <a href="login.php">Login</a></p>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
