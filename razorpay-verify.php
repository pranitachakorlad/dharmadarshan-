<?php
require __DIR__ . '/includes/razorpay.php';$user=paymentPostUser();
if(!razorpayReady()){paymentJson(['error'=>'Payment service is not available.'],503);}
$paymentId=(string)($_POST['razorpay_payment_id']??'');$orderId=(string)($_POST['razorpay_order_id']??'');$signature=(string)($_POST['razorpay_signature']??'');
try{
 $db=getDB();$s=$db->prepare('SELECT * FROM kundli_bookings WHERE razorpay_order_id=? AND user_id=? AND razorpay_key_id=?');$s->execute([$orderId,$user['id'],RAZORPAY_KEY_ID]);$booking=$s->fetch();
 if(!$booking || !preg_match('/^pay_[a-zA-Z0-9]+$/',$paymentId) || !razorpaySignatureValid($booking['razorpay_order_id'],$paymentId,$signature,RAZORPAY_KEY_SECRET)){paymentJson(['error'=>'Payment verification failed. Contact us if your account was debited.'],400);}
 $payment=razorpayRequest('payments/'.rawurlencode($paymentId));
 if(markKundliPaid($db,$booking,$payment)){paymentJson(['paid'=>true,'booking_id'=>$booking['id']]);}
 paymentJson(['paid'=>false,'message'=>'Your payment is awaiting confirmation. Check My Account for updates.']);
}catch(Throwable $ex){error_log('Razorpay verification failed ('.get_class($ex).').');paymentJson(['error'=>'Unable to confirm payment yet. Check My Account before trying another payment.'],502);}
