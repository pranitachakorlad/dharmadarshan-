<?php
require_once __DIR__ . '/includes/razorpay.php';
if(($_GET['service']??'')!=='darshan-kundli'){header('Location: index.php');exit;}
$amount=99;$_SESSION['kundli_csrf']??=bin2hex(random_bytes(32));$user=currentUser();$pageTitle='Darshan Kundli Booking';require __DIR__ . '/includes/header.php'; ?>
<section class="page-hero"><div class="container"><h1>Darshan Kundli Booking</h1><p>Booking amount: <?= formatPrice($amount) ?></p></div></section>
<section class="section"><div class="container container-narrow"><div class="payment-panel"><h2>Book your Darshan Kundli</h2><p>Pay <strong>Rs. 99.00</strong> securely through Razorpay. Your booking and payment status are saved in My Account.</p>
<?php if(!$user): ?><p>Sign in or create your account to save your birth details and booking.</p><a href="login.php" class="btn btn-primary">Sign in to book</a>
<?php elseif(!razorpayReady()): ?><p>Online booking will be available soon. Contact us on WhatsApp for booking support.</p>
<?php else: ?><?php if(str_starts_with(RAZORPAY_KEY_ID,'rzp_test_')): ?><p class="alert alert-error">Test mode — no real payment will be collected.</p><?php endif; ?><?php endif; ?>
<p id="payment-message" role="status" aria-live="polite" style="margin-top:16px"></p><div class="hero-actions"><?php if($user && razorpayReady()): ?><button id="kundli-pay" data-csrf="<?= e($_SESSION['kundli_csrf']) ?>" class="btn btn-primary">Book Now — Rs. 99</button><?php endif; ?><a href="https://wa.me/918108579007?text=<?= rawurlencode('Namaste, I want to book Darshan Kundli for Rs. 99.') ?>" class="btn btn-outline">WhatsApp Now</a><a href="category.php?cat=darshankundli" class="btn btn-outline">Back to Darshan Kundli</a></div></div></div></section>
<?php if($user && razorpayReady()): ?>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script><script src="assets/js/kundli-payment.js"></script><?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
