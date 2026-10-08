<?php
if (!isset($pageTitle)) {
    $pageTitle = SITE_NAME;
}
$isAdmin = !empty($isAdminPage);
$headerBase = $isAdmin ? '../' : '';
$cartTotal = $isAdmin ? 0 : cartCount();
$wishTotal = $isAdmin ? 0 : wishlistCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle === SITE_NAME || $pageTitle === 'Home' ? e(SITE_NAME) : e($pageTitle) . ' | ' . e(SITE_NAME) ?></title>
    <link rel="icon" type="image/png" href="<?= $headerBase ?>assets/images/logo.png">
    <link rel="apple-touch-icon" href="<?= $headerBase ?>assets/images/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Marcellus&family=Manrope:wght@400;500;600;700;800&family=Cinzel:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $headerBase ?>assets/css/style.css">
</head>
<body>
<div class="site-video-bg" aria-hidden="true">
    <video autoplay muted loop playsinline preload="metadata">
        <source src="<?= $headerBase ?>assets/videos/hero-planets.mp4" type="video/mp4">
    </video>
</div>
<header class="site-header">
    <div class="container header-inner">
        <a href="<?= $headerBase ?>index.php" class="brand">
            <img src="<?= $headerBase ?>assets/images/logo.png" alt="<?= e(SITE_NAME) ?> logo" class="brand-logo">
            <div class="brand-text">
                <span class="brand-name"><?= e(SITE_NAME) ?></span>
                <span class="brand-slogan"><?= e(SITE_SLOGAN) ?></span>
            </div>
        </a>

        <?php if (!$isAdmin): ?>
        <div class="header-actions">
            <a href="<?= $headerBase ?>wishlist.php" class="header-icon-link" title="Wishlist" aria-label="Wishlist">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8z"/>
                </svg>
                <?php if ($wishTotal > 0): ?>
                <span class="header-badge"><?= $wishTotal ?></span>
                <?php endif; ?>
            </a>
            <a href="<?= $headerBase ?>cart.php" class="header-icon-link" title="Cart" aria-label="Shopping cart">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                    <path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"/>
                </svg>
                <?php if ($cartTotal > 0): ?>
                <span class="header-badge"><?= $cartTotal ?></span>
                <?php endif; ?>
            </a>
        </div>
        <?php endif; ?>

        <button class="nav-toggle" type="button" aria-label="Toggle menu" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>
        <nav class="main-nav" aria-label="Main navigation">
            <ul>
                <?php foreach (navItems() as $item): ?>
                <li>
                    <?php
                    $href = $item['slug'] === 'home'
                        ? ($isAdmin ? 'dashboard.php' : $headerBase . 'index.php')
                        : ($isAdmin ? 'products.php?cat=' . $item['slug'] : $headerBase . 'category.php?cat=' . $item['slug']);
                    ?>
                    <a href="<?= e($href) ?>"
                       class="<?= (!empty($activeCategory) && $activeCategory === $item['slug']) || (!empty($activePage) && $activePage === $item['slug']) ? 'active' : '' ?>">
                        <?= e($item['label']) ?>
                    </a>
                </li>
                <?php endforeach; ?>
                <?php if ($isAdmin): ?>
                <li><a href="other-products.php" class="<?= !empty($activeAdmin) && $activeAdmin === 'other' ? 'active' : '' ?>">Other Products</a></li>
                <li><a href="blogs.php" class="<?= !empty($activeAdmin) && $activeAdmin === 'blogs' ? 'active' : '' ?>">Blogs</a></li>
                <li><a href="users.php" class="<?= !empty($activeAdmin) && $activeAdmin === 'users' ? 'active' : '' ?>">Users</a></li>
                <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                <?php $user = currentUser(); ?>
                <?php if ($user): ?>
                <li><a href="<?= $headerBase ?>account.php" class="<?= !empty($activePage) && $activePage === 'account' ? 'active' : '' ?>">Hi, <?= e(explode(' ', $user['name'])[0]) ?></a></li>
                <li><a href="<?= $headerBase ?>logout.php">Logout</a></li>
                <?php else: ?>
                <li><a href="<?= $headerBase ?>login.php" class="<?= !empty($activePage) && $activePage === 'login' ? 'active' : '' ?>">Login</a></li>
                <?php endif; ?>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>
<main>
<?php
if (!$isAdmin) {
    $flash = getFlash();
    if ($flash): ?>
<div class="container flash-wrap">
    <div class="alert alert-<?= e($flash['type']) ?>"><?= $flash['message'] ?></div>
</div>
<?php endif;
} ?>
