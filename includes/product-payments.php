<?php
require_once __DIR__ . '/razorpay.php';
function productPaymentMatches(array $payment,array $saved): bool {
    return ($payment['order_id']??'')===$saved['razorpay_order_id'] && ($payment['currency']??'')==='INR' && (int)($payment['amount']??0)===(int)$saved['amount_paise'] && (int)$saved['amount_paise']>0 && ($payment['status']??'')==='captured' && !empty($payment['id']);
}
function markProductPaid(PDO $db,array $saved,array $payment): bool {
    if(!productPaymentMatches($payment,$saved)){return false;}
    $db->beginTransaction();
    try {
        $s=$db->prepare('SELECT * FROM product_payments WHERE id=? FOR UPDATE');$s->execute([$saved['id']]);$current=$s->fetch();
        if(!$current || ($current['razorpay_payment_id'] && $current['razorpay_payment_id']!==$payment['id'])){$db->rollBack();return false;}
        $s=$db->prepare("UPDATE product_payments SET status='paid',razorpay_payment_id=?,paid_at=COALESCE(paid_at,CURRENT_TIMESTAMP) WHERE id=?");$s->execute([$payment['id'],$current['id']]);
        $s=$db->prepare("UPDATE orders SET status='Paid - pending confirmation' WHERE id=? AND status='Awaiting payment'");$s->execute([$current['order_id']]);$db->commit();return true;
    }catch(Throwable $ex){if($db->inTransaction()){$db->rollBack();}throw $ex;}
}
function clearPurchasedCart(array $saved): void {
    $id=(int)$saved['order_id'];if(isset($_SESSION['cleared_paid_orders'][$id])){return;}
    foreach(json_decode($saved['cart_snapshot'],true)??[] as $productId=>$quantity){
        $remaining=(int)($_SESSION['cart'][$productId]??0)-(int)$quantity;
        if($remaining>0){$_SESSION['cart'][$productId]=$remaining;}else{unset($_SESSION['cart'][$productId]);}
    }
    $_SESSION['cleared_paid_orders'][$id]=true;unset($_SESSION['checkout_token']);
}
