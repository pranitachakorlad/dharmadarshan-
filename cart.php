<?php
require_once __DIR__ . '/includes/razorpay.php';
$_SESSION['cart_payment_csrf'] ??= bin2hex(random_bytes(32));

$db = getDB();
$cart = getCartItems($db);
$_SESSION['checkout_token'] ??= bin2hex(random_bytes(32));
$pageTitle = 'Shopping Cart';

require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <h1>Shopping Cart</h1>
        <p><?= $cart['count'] ?> item<?= $cart['count'] === 1 ? '' : 's' ?> in your cart</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php if (empty($cart['items'])): ?>
        <div class="empty-state cart-empty">
            <p>Your cart is empty.</p>
            <a href="category.php?cat=shop-collection" class="btn btn-primary">Browse Products</a>
        </div>
        <?php else: ?>
        <div class="cart-layout">
            <div class="cart-items">
                <?php foreach ($cart['items'] as $item): ?>
                <?php
                    $product = $item['product'];
                    $img = productImageUrl($product['image'] ?? '');
                ?>
                <article class="cart-row">
                    <a href="#" class="cart-row-image" onclick="return false;">
                        <img src="<?= e($img) ?>" alt="<?= e($product['name']) ?>">
                    </a>
                    <div class="cart-row-info">
                        <h3><?= e($product['name']) ?></h3>
                        <?php if (!empty($product['category_name'])): ?>
                        <span class="cart-row-cat"><?= e($product['category_name']) ?></span>
                        <?php endif; ?>
                        <span class="cart-row-price"><?= formatPrice((float) $product['price']) ?></span>
                    </div>
                    <form method="post" action="cart-action.php" class="cart-qty-form">
                        <input type="hidden" name="action" value="update_cart">
                        <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                        <input type="hidden" name="redirect" value="cart.php">
                        <label class="sr-only" for="qty-<?= (int) $product['id'] ?>">Quantity</label>
                        <input type="number" id="qty-<?= (int) $product['id'] ?>" name="qty"
                               value="<?= (int) $item['qty'] ?>" min="1" max="99"
                               onchange="this.form.submit()">
                    </form>
                    <div class="cart-row-total">
                        <?= formatPrice($item['line_total']) ?>
                    </div>
                    <form method="post" action="cart-action.php" class="cart-remove-form">
                        <input type="hidden" name="action" value="remove_cart">
                        <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                        <input type="hidden" name="redirect" value="cart.php">
                        <button type="submit" class="btn-icon" title="Remove" aria-label="Remove from cart">&times;</button>
                    </form>
                </article>
                <?php endforeach; ?>
            </div>

            <aside class="cart-summary">
                <h2>Order Summary</h2>
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span><?= formatPrice($cart['subtotal']) ?></span>
                </div>
                <div class="summary-row summary-total">
                    <span>Total</span>
                    <span><?= formatPrice($cart['subtotal']) ?></span>
                </div>
                <p class="summary-note">Click Buy Now, enter your delivery details, and choose online payment or Cash on Delivery.</p>
                <?php if (currentUser()): ?>
                <details class="checkout-panel" id="checkout-form">
                    <summary class="btn btn-primary btn-block">Buy Now</summary>
                    <form method="post" action="cart-action.php" class="checkout-form">
                        <input type="hidden" name="action" value="place_order">
                        <input type="hidden" name="checkout_token" value="<?= e($_SESSION['checkout_token']) ?>">
                        <input type="hidden" name="redirect" value="cart.php">

                        <label>
                            Name *
                            <input type="text" name="customer_name" required value="<?= e(currentUser()['name'] ?? '') ?>">
                        </label>
                        <label>
                            Contact No. *
                            <input type="tel" name="contact_no" required pattern="[0-9+\-\s]{8,15}" placeholder="Enter mobile number" value="<?= e(currentUser()['contact_no'] ?? '') ?>">
                        </label>
                        <label>
                            Alternative Contact No.
                            <input type="tel" name="alt_contact_no" pattern="[0-9+\-\s]{8,15}" placeholder="Optional">
                        </label>
                        <label>
                            Address *
                            <textarea name="address" rows="4" required placeholder="House no, street, area, city, pincode"></textarea>
                        </label>
                        <fieldset class="payment-methods">
                            <legend>Payment Method *</legend>
                            <label class="radio-label">
                                <input type="radio" name="payment_method" value="Cash on Delivery" required <?= razorpayReady() ? '' : 'checked' ?>>
                                Cash on Delivery
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="payment_method" value="Razorpay" <?= razorpayReady() ? 'checked' : 'disabled' ?>>
                                Pay online (Razorpay) — UPI, cards and more
                                <?php if (!razorpayReady()): ?><span>(available after payment setup)</span><?php endif; ?>
                            </label>
                        </fieldset>
                        <button type="submit" class="btn btn-primary btn-block">Continue to payment / Place COD order</button>
                        <p id="cart-payment-message" role="status" aria-live="polite"></p>
                    </form>
                </details>
                <?php else: ?>
                <a href="login.php" class="btn btn-primary btn-block">Login to Buy Now</a>
                <?php endif; ?>
                <a href="category.php?cat=shop-collection" class="btn btn-outline btn-block continue-shopping">Continue Shopping</a>
                <form method="post" action="cart-action.php" onsubmit="return confirm('Clear entire cart?');">
                    <input type="hidden" name="action" value="clear_cart">
                    <input type="hidden" name="redirect" value="cart.php">
                    <button type="submit" class="btn btn-danger btn-block" style="margin-top: 0.75rem;">Clear Cart</button>
                </form>
            </aside>
        </div>
        <?php endif; ?>
    </div>
</section>


<?php if(currentUser() && razorpayReady()): ?>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script><script>
const checkoutForm=document.querySelector('.checkout-form');
if(checkoutForm){const paymentMessage=document.getElementById('cart-payment-message'),submit=checkoutForm.querySelector('button[type=submit]');
async function cartPaymentPost(url,values){values.set('csrf_token',<?= json_encode($_SESSION['cart_payment_csrf']) ?>);const response=await fetch(url,{method:'POST',body:values,credentials:'same-origin'}),data=await response.json();if(!response.ok)throw new Error(data.error||'Payment service unavailable.');return data;}
checkoutForm.addEventListener('submit',async(event)=>{if(new FormData(checkoutForm).get('payment_method')!=='Razorpay')return;event.preventDefault();submit.disabled=true;paymentMessage.textContent='Opening secure payment…';
try{if(typeof Razorpay==='undefined')throw new Error('Payment checkout could not load. Please reload this page.');const order=await cartPaymentPost('razorpay-cart-create.php',new URLSearchParams(new FormData(checkoutForm)));
if(order.paid){location.href='account.php#orders';return;}
const checkout=new Razorpay({key:order.key,order_id:order.order_id,amount:order.amount,currency:order.currency,name:'Dharma Darshan',description:'Product order',prefill:order.prefill,
handler:async(result)=>{paymentMessage.textContent='Verifying payment…';try{const saved=await cartPaymentPost('razorpay-cart-verify.php',new URLSearchParams(result));if(saved.paid){location.href='account.php#orders';}else{paymentMessage.textContent=saved.message;submit.disabled=false;}}catch(error){paymentMessage.textContent=error.message;submit.disabled=false;}},
modal:{ondismiss:()=>{paymentMessage.textContent='Payment checkout closed. Your cart is still available.';submit.disabled=false;}}});
checkout.on('payment.failed',()=>{paymentMessage.textContent='Payment failed. Your cart is still available.';submit.disabled=false;});checkout.open();
}catch(error){paymentMessage.textContent=error.message;submit.disabled=false;}});}
</script><?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
