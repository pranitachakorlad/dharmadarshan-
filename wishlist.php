<?php
require_once __DIR__ . '/includes/functions.php';

$db = getDB();
$items = getWishlistItems($db);
$pageTitle = 'Wishlist';

require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <h1>Wishlist</h1>
        <p><?= count($items) ?> saved item<?= count($items) === 1 ? '' : 's' ?></p>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php if (empty($items)): ?>
        <div class="empty-state cart-empty">
            <p>Your wishlist is empty. Save products you love for later.</p>
            <a href="category.php?cat=shop-collection" class="btn btn-primary">Browse Products</a>
        </div>
        <?php else: ?>
        <div class="wishlist-grid">
            <?php foreach ($items as $product): ?>
            <?php
                $img = productImageUrl($product['image'] ?? '');
                $inWishlist = true;
            ?>
            <article class="wishlist-card">
                <div class="wishlist-image">
                    <img src="<?= e($img) ?>" alt="<?= e($product['name']) ?>">
                </div>
                <div class="wishlist-body">
                    <h3><?= e($product['name']) ?></h3>
                    <?php if (!empty($product['category_name'])): ?>
                    <span class="product-badge-inline"><?= e($product['category_name']) ?></span>
                    <?php endif; ?>
                    <p class="product-desc"><?= e(mb_strimwidth($product['description'] ?? '', 0, 120, '...')) ?></p>
                    <span class="product-price"><?= formatPrice((float) $product['price']) ?></span>
                    <div class="wishlist-actions">
                        <form method="post" action="cart-action.php">
                            <input type="hidden" name="action" value="move_to_cart">
                            <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                            <input type="hidden" name="redirect" value="cart.php">
                            <button type="submit" class="btn btn-sm btn-primary">Move to Cart</button>
                        </form>
                        <form method="post" action="cart-action.php">
                            <input type="hidden" name="action" value="remove_wishlist">
                            <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                            <input type="hidden" name="redirect" value="wishlist.php">
                            <button type="submit" class="btn btn-sm btn-outline">Remove</button>
                        </form>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
