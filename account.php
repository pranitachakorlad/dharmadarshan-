<?php
require_once __DIR__ . '/includes/functions.php';
requireUser();

$db = getDB();
$cart = getCartItems($db);
$wishlist = getWishlistItems($db);
$user = currentUser();
require_once __DIR__ . '/includes/orders.php';
$orders = orderHistory($db, (int) $user['id']);
require_once __DIR__ . '/includes/razorpay.php';
$bookings = kundliHistory($db, (int) $user['id']);

$activePage = 'account';
$pageTitle = 'My Account';
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <h1>My Account</h1>
        <p>Welcome, <?= e($user['name']) ?>. Your saved shopping details are below.</p>
    </div>
</section>

<section class="section">
    <div class="container account-grid">
        <div class="admin-panel">
            <h2>Profile</h2>
            <p><strong>Name:</strong> <?= e($user['name']) ?></p>
            <p><strong>Contact No.:</strong> <?= e($user['contact_no']) ?></p>
            <p><strong>Email:</strong> <?= e($user['email']) ?></p>
            <?php if (!empty($user['birth_date'])): ?>
            <p><strong>Birth Date:</strong> <?= e($user['birth_date']) ?></p>
            <?php endif; ?>
            <?php if (!empty($user['birth_time'])): ?>
            <p><strong>Birth Time:</strong> <?= e(substr($user['birth_time'], 0, 5)) ?></p>
            <?php endif; ?>
            <?php if (!empty($user['birth_place'])): ?>
            <p><strong>Birth Place:</strong> <?= e($user['birth_place']) ?></p>
            <?php endif; ?>
            <a href="logout.php" class="btn btn-outline">Logout</a>
        </div>
        <div class="admin-panel">
            <h2>Shopping</h2>
            <p><?= (int) $cart['count'] ?> item<?= $cart['count'] === 1 ? '' : 's' ?> in cart.</p>
            <p><?= count($wishlist) ?> saved wishlist item<?= count($wishlist) === 1 ? '' : 's' ?>.</p>
            <div class="admin-actions">
                <a href="cart.php" class="btn btn-primary">Open Cart</a>
                <a href="wishlist.php" class="btn btn-outline">Open Wishlist</a>
            </div>
        </div>
    </div>
</section>

<section class="section" id="orders"><div class="container"><div class="admin-panel"><h2>Order history</h2><?php renderOrderHistory($orders); ?></div></div></section>
<section class="section" id="kundli-bookings"><div class="container"><div class="admin-panel"><h2>Darshan Kundli bookings</h2><?php renderKundliHistory($bookings); ?></div></div></section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
