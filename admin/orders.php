<?php
require __DIR__ . '/auth.php'; requireAdmin(true); require __DIR__ . '/layout.php'; require __DIR__ . '/../includes/orders.php';
$userId=isset($_GET['user'])?(int)$_GET['user']:null;
$orders=orderHistory(getDB(),$userId);adminHeader($userId?'Order history for user #'.$userId:'Order history'); ?>
<p>Saved orders include the products, quantities, prices and delivery details recorded at checkout.</p>
<div class="admin-panel"><?php renderOrderHistory($orders,true); ?></div><?php adminFooter(); ?>
