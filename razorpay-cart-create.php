<?php
require __DIR__ . '/includes/product-payments.php';$user=paymentPostUser('cart_payment_csrf');
if(!razorpayReady()){paymentJson(['error'=>'Online payment is not configured yet.'],503);}
if(empty($_SESSION['checkout_token']) || !hash_equals($_SESSION['checkout_token'],(string)($_POST['checkout_token']??''))){paymentJson(['error'=>'Reload your cart and try again.'],403);}
$name=trim($_POST['customer_name']??'');$contact=trim($_POST['contact_no']??'');$alt=trim($_POST['alt_contact_no']??'');$address=trim($_POST['address']??'');
if($name===''||mb_strlen($name)>100||!preg_match('/^[0-9+\-\s]{8,15}$/',$contact)||($alt!==''&&!preg_match('/^[0-9+\-\s]{8,15}$/',$alt))||$address===''||mb_strlen($address)>5000){paymentJson(['error'=>'Enter valid name, contact number and delivery address.'],422);}
try{
 $db=getDB();$cart=getCartItems($db);if(!$cart['items']){paymentJson(['error'=>'Your cart is empty.'],422);}
 $snapshot=[];$cents=0;foreach($cart['items'] as $item){$snapshot[$item['product']['id']]=$item['qty'];$cents+=(int)round((float)$item['product']['price']*100)*$item['qty'];}
 if($cents<=0 || $cents>999999999999){paymentJson(['error'=>'This cart cannot be paid online.'],422);}
 $requestToken=hash('sha256',json_encode([$_SESSION['checkout_token'],$user['id'],$snapshot,$cents,$name,$contact,$alt,$address,RAZORPAY_KEY_ID]));
 $s=$db->prepare('SELECT pp.*,o.user_id FROM product_payments pp JOIN orders o ON o.id=pp.order_id WHERE o.checkout_token=? AND o.user_id=?');$s->execute([$requestToken,$user['id']]);$saved=$s->fetch();
 if(!$saved){
  $db->beginTransaction();$s=$db->prepare("INSERT INTO orders(user_id,checkout_token,customer_name,contact_no,alt_contact_no,address,payment_method,status,total) VALUES(?,?,?,?,?,?,'Razorpay','Awaiting payment',?)");$s->execute([$user['id'],$requestToken,$name,$contact,$alt,$address,number_format($cents/100,2,'.','')]);$id=(int)$db->lastInsertId();
  $s=$db->prepare('INSERT INTO order_items(order_id,product_id,product_name,product_image,unit_price,quantity,line_total) VALUES(?,?,?,?,?,?,?)');
  foreach($cart['items'] as $item){$pr=$item['product'];$unit=(int)round((float)$pr['price']*100);$s->execute([$id,$pr['id'],$pr['name'],$pr['image'],number_format($unit/100,2,'.',''),$item['qty'],number_format($unit*$item['qty']/100,2,'.','')]);}
  $s=$db->prepare('INSERT INTO product_payments(order_id,razorpay_key_id,amount_paise,cart_snapshot) VALUES(?,?,?,?)');$s->execute([$id,RAZORPAY_KEY_ID,$cents,json_encode($snapshot)]);$pid=(int)$db->lastInsertId();$db->commit();
  $saved=['id'=>$pid,'order_id'=>$id,'amount_paise'=>$cents,'razorpay_order_id'=>null,'cart_snapshot'=>json_encode($snapshot),'status'=>'pending'];
 }
 if($saved['status']==='paid'){clearPurchasedCart($saved);paymentJson(['paid'=>true,'order_id'=>$saved['order_id']]);}
 if(!$saved['razorpay_order_id']){
  $gateway=razorpayRequest('orders',['amount'=>(int)$saved['amount_paise'],'currency'=>'INR','receipt'=>'product_'.$saved['order_id'],'notes'=>['site_order_id'=>(string)$saved['order_id'],'service'=>'products']]);
  if(empty($gateway['id'])||(int)($gateway['amount']??0)!==(int)$saved['amount_paise']||($gateway['currency']??'')!=='INR'){throw new RuntimeException('Invalid gateway order.');}
  $s=$db->prepare('UPDATE product_payments SET razorpay_order_id=? WHERE id=?');$s->execute([$gateway['id'],$saved['id']]);$saved['razorpay_order_id']=$gateway['id'];
 }
 paymentJson(['key'=>RAZORPAY_KEY_ID,'order_id'=>$saved['razorpay_order_id'],'amount'=>(int)$saved['amount_paise'],'currency'=>'INR','prefill'=>['name'=>$name,'contact'=>$contact,'email'=>$user['email']]]);
}catch(Throwable $ex){if(isset($db)&&$db->inTransaction()){$db->rollBack();}error_log('Product payment start failed ('.get_class($ex).').');paymentJson(['error'=>'Unable to start payment. Your cart is still available. Please try again.'],502);}
