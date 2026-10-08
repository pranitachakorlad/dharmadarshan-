<?php
function orderHistory(PDO $db, ?int $userId): array {
    $sql='SELECT o.*, u.email AS user_email, pp.status AS online_payment_status, pp.razorpay_payment_id, pp.razorpay_key_id FROM orders o JOIN users u ON u.id=o.user_id LEFT JOIN product_payments pp ON pp.order_id=o.id';
    $stmt=$db->prepare($sql.($userId!==null?' WHERE o.user_id=?':'').' ORDER BY o.id DESC');$stmt->execute($userId!==null?[$userId]:[]);$orders=$stmt->fetchAll();
    $items=$db->prepare('SELECT * FROM order_items WHERE order_id=? ORDER BY id');
    foreach($orders as &$order){$items->execute([$order['id']]);$order['items']=$items->fetchAll();}unset($order);return $orders;
}
function renderOrderHistory(array $orders, bool $admin=false): void { ?>
<?php if(!$orders): ?><p>No orders placed yet.</p><?php endif; ?>
<?php foreach($orders as $order): ?><article class="order-history-card">
<div class="order-history-heading"><h3>Order #<?= (int)$order['id'] ?></h3><span><?= e($order['status']) ?></span></div>
<p><?= e($order['created_at']) ?> &middot; <?= e($order['payment_method']) ?> &middot; <strong><?= formatPrice((float)$order['total']) ?></strong></p>
<?php if($admin): ?><p><strong>Customer:</strong> <?= e($order['customer_name']) ?> (User #<?= (int)$order['user_id'] ?>) &middot; <?= e($order['user_email']) ?></p><?php endif; ?>
<?php if(!empty($order['razorpay_payment_id'])): ?><p>Payment reference: <?= e($order['razorpay_payment_id']) ?></p><?php endif; ?><?php if(str_starts_with($order['razorpay_key_id']??'','rzp_test_')): ?><p>Test payment</p><?php endif; ?>
<div class="order-items-table"><table class="admin-table"><thead><tr><th>Product</th><th>Quantity</th><th>Unit price</th><th>Total</th></tr></thead><tbody><?php foreach($order['items'] as $item): ?><tr><td><?= e($item['product_name']) ?></td><td><?= (int)$item['quantity'] ?></td><td><?= formatPrice((float)$item['unit_price']) ?></td><td><?= formatPrice((float)$item['line_total']) ?></td></tr><?php endforeach; ?></tbody></table></div>
<details><summary>Delivery details</summary><p><strong><?= e($order['customer_name']) ?></strong><br><?= e($order['contact_no']) ?><?= $order['alt_contact_no']?' / '.e($order['alt_contact_no']):'' ?><br><?= nl2br(e($order['address'])) ?></p></details>
</article><?php endforeach; ?>
<?php }
