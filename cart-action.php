<?php
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$db = getDB();
$action = $_POST['action'] ?? '';
$productId = (int) ($_POST['product_id'] ?? 0);
$qty = (int) ($_POST['qty'] ?? 1);
$redirect = $_POST['redirect'] ?? 'index.php';

// Only allow relative redirects within the site
if (!preg_match('#^[a-zA-Z0-9_./?=&%-]+$#', $redirect) || str_contains($redirect, '..')) {
    $redirect = 'index.php';
}

$product = $productId > 0 ? getProductById($db, $productId) : null;

switch ($action) {
    case 'add_cart':
        if ($product) {
            addToCart($productId, $qty);
            setFlash('success', e($product['name']) . ' added to cart.');
        } else {
            setFlash('error', 'Product not found.');
        }
        break;

    case 'buy_now':
        if ($product) {
            addToCart($productId, $qty);
            setFlash('success', e($product['name']) . ' is ready in your cart.');
            $redirect = 'cart.php';
        } else {
            setFlash('error', 'Product not found.');
        }
        break;

    case 'update_cart':
        if ($product) {
            updateCartQty($productId, $qty);
            setFlash('success', 'Cart updated.');
        }
        break;

    case 'remove_cart':
        removeFromCart($productId);
        setFlash('success', 'Item removed from cart.');
        $redirect = 'cart.php';
        break;

    case 'clear_cart':
        clearCart();
        setFlash('success', 'Cart cleared.');
        $redirect = 'cart.php';
        break;

    case 'place_order':
        requireUser();
        $checkoutToken = (string) ($_POST['checkout_token'] ?? '');
        if (!$checkoutToken || !hash_equals($_SESSION['checkout_token'] ?? '', $checkoutToken)) {
            setFlash('error', 'Please reload your cart before submitting an order.'); $redirect = 'cart.php'; break;
        }
        $cart = getCartItems($db);
        $customerName = trim($_POST['customer_name'] ?? '');
        $contactNo = trim($_POST['contact_no'] ?? '');
        $altContactNo = trim($_POST['alt_contact_no'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $paymentMethod = $_POST['payment_method'] ?? '';
        $allowedPayments = ['Cash on Delivery'];

        if (empty($cart['items'])) {
            setFlash('error', 'Your cart is empty.');
            $redirect = 'cart.php';
            break;
        }

        if ($customerName === '' || mb_strlen($customerName) > 100 || !preg_match('/^[0-9+\-\s]{8,15}$/', $contactNo) || ($altContactNo !== '' && !preg_match('/^[0-9+\-\s]{8,15}$/', $altContactNo)) || $address === '' || mb_strlen($address) > 5000 || !in_array($paymentMethod, $allowedPayments, true)) {
            setFlash('error', 'Please fill all required order details.');
            $redirect = 'cart.php#checkout-form';
            break;
        }

        try {
            $db->beginTransaction();
            $userId = (int) currentUser()['id'];
            $totalCents = 0;
            foreach ($cart['items'] as $item) { $totalCents += (int) round((float) $item['product']['price'] * 100) * $item['qty']; }
            $stmt = $db->prepare('INSERT INTO orders(user_id,checkout_token,customer_name,contact_no,alt_contact_no,address,payment_method,total) VALUES(?,?,?,?,?,?,?,?)');
            $stmt->execute([$userId,$checkoutToken,$customerName,$contactNo,$altContactNo,$address,$paymentMethod,number_format($totalCents/100,2,'.','')]);
            $orderId = (int) $db->lastInsertId();
            $stmt = $db->prepare('INSERT INTO order_items(order_id,product_id,product_name,product_image,unit_price,quantity,line_total) VALUES(?,?,?,?,?,?,?)');
            foreach ($cart['items'] as $item) {
                $product = $item['product'];
                $cents = (int) round((float) $product['price'] * 100);
                $stmt->execute([$orderId,$product['id'],$product['name'],$product['image'],number_format($cents/100,2,'.',''),$item['qty'],number_format($cents*$item['qty']/100,2,'.','')]);
            }
            $db->commit();
            clearCart();
            unset($_SESSION['checkout_token']);
            setFlash('success', 'Order #' . $orderId . ' received. We will contact you soon for confirmation.');
            $redirect = 'account.php#orders';
        } catch (PDOException $ex) {
            if ($db->inTransaction()) { $db->rollBack(); }
            if ((int) ($ex->errorInfo[1] ?? 0) === 1062) {
                $stmt=$db->prepare('SELECT id FROM orders WHERE checkout_token=? AND user_id=?'); $stmt->execute([$checkoutToken,$userId]);
                if ($stmt->fetchColumn()) { clearCart(); unset($_SESSION['checkout_token']); setFlash('success','This order has already been saved.'); $redirect='account.php#orders'; break; }
            }
            error_log('Order save failed: '.$ex->getMessage());
            setFlash('error', 'Your order could not be saved. Your cart is still available; please try again.');
            $redirect = 'cart.php#checkout-form';
        }
        break;

    case 'add_wishlist':
        if ($product) {
            addToWishlist($productId);
            setFlash('success', e($product['name']) . ' added to wishlist.');
        } else {
            setFlash('error', 'Product not found.');
        }
        break;

    case 'remove_wishlist':
        removeFromWishlist($productId);
        setFlash('success', 'Item removed from wishlist.');
        $redirect = 'wishlist.php';
        break;

    case 'move_to_cart':
        if ($product) {
            addToCart($productId, 1);
            removeFromWishlist($productId);
            setFlash('success', e($product['name']) . ' moved to cart.');
            $redirect = 'cart.php';
        }
        break;

    default:
        setFlash('error', 'Invalid action.');
}

header('Location: ' . $redirect);
exit;
