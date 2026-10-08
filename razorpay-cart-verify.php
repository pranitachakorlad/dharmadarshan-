<?php
require __DIR__ . '/includes/product-payments.php';$user=paymentPostUser('cart_payment_csrf');
if(!razorpayReady()){paymentJson(['error'=>'Payment service is unavailable.'],503);}
$paymentId=(string)($_POST['razorpay_payment_id']??'');$orderId=(string)($_POST['razorpay_order_id']??'');$signature=(string)($_POST['razorpay_signature']??'');
try{
 $db=getDB();$s=$db->prepare('SELECT pp.*,o.user_id FROM product_payments pp JOIN orders o ON o.id=pp.order_id WHERE pp.razorpay_order_id=? AND o.user_id=? AND pp.razorpay_key_id=?');$s->execute([$orderId,$user['id'],RAZORPAY_KEY_ID]);$saved=$s->fetch();
 if(!$saved||!preg_match('/^pay_[a-zA-Z0-9]+$/',$paymentId)||!razorpaySignatureValid($saved['razorpay_order_id'],$paymentId,$signature,RAZORPAY_KEY_SECRET)){paymentJson(['error'=>'Payment verification failed. Contact us if your account was debited.'],400);}
 $payment=razorpayRequest('payments/'.rawurlencode($paymentId));
 if(($payment['id']??'')===$paymentId && markProductPaid($db,$saved,$payment)){clearPurchasedCart($saved);paymentJson(['paid'=>true,'order_id'=>$saved['order_id']]);}
 paymentJson(['paid'=>false,'message'=>'Payment is awaiting confirmation. Check My Account before paying again.']);
}catch(Throwable $ex){error_log('Product payment verification failed ('.get_class($ex).').');paymentJson(['error'=>'Unable to confirm payment yet. Check My Account before paying again.'],502);}
