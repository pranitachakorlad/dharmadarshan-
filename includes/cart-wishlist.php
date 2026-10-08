<?php

require_once __DIR__ . '/session.php';

startAppSession();

function initCartWishlist(): void
{
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    if (!isset($_SESSION['wishlist'])) {
        $_SESSION['wishlist'] = [];
    }
}

function cartCount(): int
{
    initCartWishlist();
    return (int) array_sum($_SESSION['cart']);
}

function wishlistCount(): int
{
    initCartWishlist();
    return count($_SESSION['wishlist']);
}

function addToCart(int $productId, int $qty = 1): void
{
    initCartWishlist();
    $qty = max(1, min(99, $qty));
    $current = $_SESSION['cart'][$productId] ?? 0;
    $_SESSION['cart'][$productId] = min(99, $current + $qty);
}

function updateCartQty(int $productId, int $qty): void
{
    initCartWishlist();
    if ($qty <= 0) {
        unset($_SESSION['cart'][$productId]);
        return;
    }
    $_SESSION['cart'][$productId] = min(99, $qty);
}

function removeFromCart(int $productId): void
{
    initCartWishlist();
    unset($_SESSION['cart'][$productId]);
}

function clearCart(): void
{
    initCartWishlist();
    $_SESSION['cart'] = [];
}

function addToWishlist(int $productId): void
{
    initCartWishlist();
    if (!in_array($productId, $_SESSION['wishlist'], true)) {
        $_SESSION['wishlist'][] = $productId;
    }
}

function removeFromWishlist(int $productId): void
{
    initCartWishlist();
    $_SESSION['wishlist'] = array_values(array_filter(
        $_SESSION['wishlist'],
        fn($id) => (int) $id !== $productId
    ));
}

function isInWishlist(int $productId): bool
{
    initCartWishlist();
    return in_array($productId, $_SESSION['wishlist'], true);
}

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    if (empty($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function getProductById(PDO $db, int $id): ?array
{
    $stmt = $db->prepare("
        SELECT p.*, c.name AS category_name, c.slug AS category_slug
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.id = ? AND p.is_active = 1
        LIMIT 1
    ");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function getCartItems(PDO $db): array
{
    initCartWishlist();
    $items = [];
    $subtotal = 0.0;

    foreach ($_SESSION['cart'] as $productId => $qty) {
        $product = getProductById($db, (int) $productId);
        if (!$product) {
            unset($_SESSION['cart'][$productId]);
            continue;
        }
        $lineTotal = (float) $product['price'] * (int) $qty;
        $subtotal += $lineTotal;
        $items[] = [
            'product' => $product,
            'qty' => (int) $qty,
            'line_total' => $lineTotal,
        ];
    }

    return ['items' => $items, 'subtotal' => $subtotal, 'count' => cartCount()];
}

function getWishlistItems(PDO $db): array
{
    initCartWishlist();
    $items = [];

    foreach ($_SESSION['wishlist'] as $productId) {
        $product = getProductById($db, (int) $productId);
        if (!$product) {
            removeFromWishlist((int) $productId);
            continue;
        }
        $items[] = $product;
    }

    return $items;
}
