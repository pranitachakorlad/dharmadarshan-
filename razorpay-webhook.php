<?php
require __DIR__ . '/includes/product-payments.php';
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);exit;}
if(RAZORPAY_WEBHOOK_SECRET===''){http_response_code(503);exit;}
$body=file_get_contents('php://input');$signature=$_SERVER['HTTP_X_RAZORPAY_SIGNATURE']??'';
if(!$signature || !hash_equals(hash_hmac('sha256',$body,RAZORPAY_WEBHOOK_SECRET),$signature)){http_response_code(400);exit;}
try{
 $event=json_decode($body,true,512,JSON_THROW_ON_ERROR);
 if(!in_array($event['event']??'',['payment.captured','order.paid'],true)){http_response_code(200);exit;}
 $payment=$event['payload']['payment']['entity']??[];
 $db=getDB();$s=$db->prepare('SELECT * FROM kundli_bookings WHERE razorpay_order_id=? AND razorpay_key_id=?');$s->execute([$payment['order_id']??'',RAZORPAY_KEY_ID]);$booking=$s->fetch();
 if($booking && !markKundliPaid($db,$booking,$payment)){http_response_code(400);exit;}
 if(!$booking){$s=$db->prepare('SELECT * FROM product_payments WHERE razorpay_order_id=? AND razorpay_key_id=?');$s->execute([$payment['order_id']??'',RAZORPAY_KEY_ID]);$saved=$s->fetch();if($saved && !markProductPaid($db,$saved,$payment)){http_response_code(400);exit;}}
 http_response_code(200);
}catch(Throwable $ex){error_log('Razorpay webhook processing failed ('.get_class($ex).').');http_response_code(500);}
