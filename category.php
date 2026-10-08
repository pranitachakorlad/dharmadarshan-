<?php
require_once __DIR__ . '/includes/razorpay.php';
$_SESSION['kundli_csrf'] ??= bin2hex(random_bytes(32));

$slug = $_GET['cat'] ?? '';
$validSlugs = array_column(shopNavItems(), 'slug');

if (!in_array($slug, $validSlugs, true)) {
    header('Location: index.php');
    exit;
}

$db = getDB();
$products = getProductsByCategory($db, $slug);
$pageTitle = categoryPageTitle($slug);
$activeCategory = $slug;
$subtitle = categorySubtitle($slug);
$shopType = $slug === 'shop-collection' ? ($_GET['type'] ?? '') : '';
$selectedShopCard = $shopType ? shopCollectionCardBySlug($shopType) : null;
if ($shopType && !$selectedShopCard) { header('Location: category.php?cat=shop-collection'); exit; }
if ($selectedShopCard) { $products = getProductsByCategory($db, $shopType); }

require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <h1><?= e($pageTitle) ?></h1>
        <?php if ($subtitle): ?>
        <p class="topic-subtitle"><?= e($subtitle) ?></p>
        <?php elseif ($slug === 'shop-collection'): ?>
        <p>Browse other Dharma Darshan products and spiritual accessories.</p>
        <?php else: ?>
        <p>Explore our curated <?= e(strtolower($pageTitle)) ?> offerings.</p>
        <?php endif; ?>
    </div>
</section>

<?php if ($slug !== 'shop-collection'): ?>
<section class="section category-intro-section">
    <div class="container container-narrow">
        <div class="category-intro">
            <?php $featureImage = categoryFeatureImage($slug); ?>
            <?php if ($featureImage): ?>
            <div class="category-feature-image">
                <img src="<?= e($featureImage) ?>" alt="<?= e($pageTitle) ?>">
            </div>
            <p id="payment-message" role="status" aria-live="polite" style="margin-top:16px"></p>
            <?php if(currentUser() && razorpayReady()): ?>
            <?php if(str_starts_with(RAZORPAY_KEY_ID,'rzp_test_')): ?><p>Test mode — no real payment collected.</p><?php endif; ?>
            <script src="https://checkout.razorpay.com/v1/checkout.js"></script><script src="assets/js/kundli-payment.js"></script>
            <?php endif; ?>
            <?php endif; ?>
            <?= categoryDescriptionHtml($slug) ?>
            <?php if ($slug === 'darshankundli'): ?>
            <div class="kundli-booking">
                <?php if(currentUser() && razorpayReady()): ?><button id="kundli-pay" data-csrf="<?= e($_SESSION['kundli_csrf']) ?>" class="btn btn-primary btn-large">BOOK NOW<br><span>Only in Rs. 99/-</span></button><?php else: ?><a href="payment.php?service=darshan-kundli" class="btn btn-primary btn-large">BOOK NOW<br><span>Only in Rs. 99/-</span></a><?php endif; ?>
                <a href="https://wa.me/918108579007?text=<?= rawurlencode('Namaste, I want Darshan Kundli guidance from Dharma Darshan.') ?>" class="btn btn-outline btn-large">WhatsApp Now</a>
            </div>
            <p id="payment-message" role="status" aria-live="polite" style="margin-top:16px"></p>
            <?php if(currentUser() && razorpayReady()): ?>
            <?php if(str_starts_with(RAZORPAY_KEY_ID,'rzp_test_')): ?><p>Test mode — no real payment collected.</p><?php endif; ?>
            <script src="https://checkout.razorpay.com/v1/checkout.js"></script><script src="assets/js/kundli-payment.js"></script>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($slug === 'ratna'): ?>
<section class="section ratna-section">
    <div class="container">
        <div class="ratna-grid">
            <?php foreach (ratnaCards() as $ratna): ?>
            <article class="ratna-card">
                <div class="ratna-image-wrap">
                    <img src="<?= e(productImageUrl($ratna['image'])) ?>" alt="<?= e($ratna['name']) ?>">
                    <span class="stock-badge">In-stock</span>
                </div>
                <div class="ratna-body">
                    <h2><?= e($ratna['name']) ?></h2>
                    <p class="ratna-description"><?= e($ratna['description']) ?></p>
                    <button type="button" class="read-more-btn">Read more</button>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($slug === 'shop-collection'): ?>
<section class="section">
    <div class="container">
        <?php if ($selectedShopCard): ?>
        <div class="shop-selected-header">
            <a href="category.php?cat=shop-collection" class="back-link">&larr; Back to Shop Collection</a>
            <h2><?= e($selectedShopCard['name']) ?></h2>
            <p>Explore our <?= e($selectedShopCard['name']) ?> collection.</p>
        </div>
        <?php if (!$products): ?><div class="empty-state"><p>No products added in this section yet.</p></div>
        <?php else: ?><div class="product-grid"><?php foreach ($products as $product) { require __DIR__ . '/includes/product-card.php'; } ?></div><?php endif; ?>
        <?php else: ?>
        <div class="shop-category-grid">
            <?php foreach (shopCollectionCards() as $card): ?>
            <a href="category.php?cat=shop-collection&type=<?= e($card['slug']) ?>" class="shop-category-card">
                <img src="<?= e($card['image']) ?>" alt="<?= e($card['name']) ?>">
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
