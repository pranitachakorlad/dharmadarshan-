<?php
require __DIR__ . '/includes/razorpay.php';$user=paymentPostUser();
if(!razorpayReady()){paymentJson(['error'=>'Online booking is not available yet. Please contact us on WhatsApp.'],503);}
try{
 $db=getDB();$s=$db->prepare('SELECT * FROM users WHERE id=?');$s->execute([$user['id']]);$profile=$s->fetch();
 if(!$profile){paymentJson(['error'=>'Please sign in again.'],401);}
 $s=$db->prepare("SELECT * FROM kundli_bookings WHERE user_id=? AND status='pending' AND razorpay_key_id=? AND razorpay_order_id IS NOT NULL AND created_at > DATE_SUB(NOW(),INTERVAL 1 HOUR) ORDER BY id DESC LIMIT 1");$s->execute([$user['id'],RAZORPAY_KEY_ID]);$booking=$s->fetch();
 if(!$booking){
  $s=$db->prepare('INSERT INTO kundli_bookings(user_id,razorpay_key_id,customer_name,contact_no,email,birth_date,birth_time,birth_place) VALUES(?,?,?,?,?,?,?,?)');$s->execute([$profile['id'],RAZORPAY_KEY_ID,$profile['name'],$profile['contact_no']??'',$profile['email'],$profile['birth_date'],$profile['birth_time'],$profile['birth_place']]);$id=(int)$db->lastInsertId();
  $order=razorpayRequest('orders',['amount'=>9900,'currency'=>'INR','receipt'=>'kundli_'.$id,'notes'=>['booking_id'=>(string)$id,'service'=>'darshan-kundli']]);
  if(empty($order['id']) || (int)($order['amount']??0)!==9900 || ($order['currency']??'')!=='INR'){throw new RuntimeException('Invalid payment order.');}
  $s=$db->prepare('UPDATE kundli_bookings SET razorpay_order_id=? WHERE id=?');$s->execute([$order['id'],$id]);$booking=['id'=>$id,'razorpay_order_id'=>$order['id']];
 }
 paymentJson(['key'=>RAZORPAY_KEY_ID,'order_id'=>$booking['razorpay_order_id'],'booking_id'=>$booking['id'],'amount'=>9900,'currency'=>'INR','prefill'=>['name'=>$profile['name'],'email'=>$profile['email'],'contact'=>$profile['contact_no']??'']]);
}catch(Throwable $ex){error_log('Razorpay order creation failed ('.get_class($ex).').');paymentJson(['error'=>'Unable to start payment. Please try again shortly.'],502);}
