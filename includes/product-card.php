<?php
/** @var array $product */
$cardBase = !empty($isAdminPage) ? '../' : '';
$img = productImageUrl($product['image'] ?? '', $cardBase . 'assets/images/placeholder-product.svg');
$redirectUrl = $_SERVER['REQUEST_URI'] ?? 'index.php';
$productId = (int) ($product['id'] ?? 0);
$inWishlist = $productId > 0 && isInWishlist($productId);
?>
<article class="product-card">
    <div class="product-image-wrap">
        <img src="<?= e($img) ?>" alt="<?= e($product['name']) ?>" loading="lazy">
        <?php if (!empty($product['category_name'])): ?>
        <span class="product-badge"><?= e($product['category_name']) ?></span>
        <?php endif; ?>
        <form method="post" action="<?= e($cardBase) ?>cart-action.php" class="wishlist-toggle-form">
            <input type="hidden" name="action" value="<?= $inWishlist ? 'remove_wishlist' : 'add_wishlist' ?>">
            <input type="hidden" name="product_id" value="<?= $productId ?>">
            <input type="hidden" name="redirect" value="<?= e($redirectUrl) ?>">
            <button type="submit" class="wishlist-btn <?= $inWishlist ? 'is-active' : '' ?>"
                    title="<?= $inWishlist ? 'Remove from wishlist' : 'Add to wishlist' ?>"
                    aria-label="<?= $inWishlist ? 'Remove from wishlist' : 'Add to wishlist' ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="<?= $inWishlist ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2">
                    <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8z"/>
                </svg>
            </button>
        </form>
    </div>
    <div class="product-body">
        <h3 class="product-title"><?= e($product['name']) ?></h3>
        <p class="product-desc"><?= e(mb_strimwidth($product['description'] ?? '', 0, 100, '...')) ?></p>
        <div class="product-footer">
            <span class="product-price"><?= formatPrice((float) $product['price']) ?></span>
            <div class="product-actions">
            <form method="post" action="<?= e($cardBase) ?>cart-action.php" class="add-cart-form">
                <input type="hidden" name="action" value="add_cart">
                <input type="hidden" name="product_id" value="<?= $productId ?>">
                <input type="hidden" name="redirect" value="<?= e($redirectUrl) ?>">
                <button type="submit" class="btn btn-sm btn-primary">Add to Cart</button>
            </form>
            <form method="post" action="<?= e($cardBase) ?>cart-action.php" class="add-cart-form">
                <input type="hidden" name="action" value="buy_now">
                <input type="hidden" name="product_id" value="<?= $productId ?>">
                <input type="hidden" name="redirect" value="cart.php">
                <button type="submit" class="btn btn-sm btn-outline">Buy Now</button>
            </form>
            </div>
        </div>
    </div>
</article>
