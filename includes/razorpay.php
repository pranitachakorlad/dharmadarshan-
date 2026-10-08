<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/../config/razorpay.php';
function razorpayReady(): bool { return RAZORPAY_KEY_ID !== '' && RAZORPAY_KEY_SECRET !== ''; }
function razorpaySignatureValid(string $orderId,string $paymentId,string $signature,string $secret): bool {
    return $signature !== '' && $secret !== '' && hash_equals(hash_hmac('sha256',$orderId.'|'.$paymentId,$secret),$signature);
}
function razorpayPaymentMatches(array $payment,array $booking): bool {
    return ($payment['order_id']??'') === $booking['razorpay_order_id'] && ($payment['currency']??'') === 'INR' && (int)($payment['amount']??0) === 9900 && (int)$booking['amount_paise'] === 9900 && ($payment['status']??'') === 'captured' && !empty($payment['id']);
}
function razorpayRequest(string $path,?array $data=null): array {
    if(!razorpayReady() || !function_exists('curl_init')) { throw new RuntimeException('Payment service is not configured.'); }
    $ch=curl_init('https://api.razorpay.com/v1/'.$path);
    curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_USERPWD=>RAZORPAY_KEY_ID.':'.RAZORPAY_KEY_SECRET,CURLOPT_CONNECTTIMEOUT=>10,CURLOPT_TIMEOUT=>30,CURLOPT_HTTPHEADER=>['Content-Type: application/json']]);
    if($data!==null){curl_setopt($ch,CURLOPT_POST,true);curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($data,JSON_THROW_ON_ERROR));}
    $body=curl_exec($ch);$status=curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);
    if($body===false || $status<200 || $status>=300){throw new RuntimeException('Unable to contact payment service.');}
    $result=json_decode($body,true,512,JSON_THROW_ON_ERROR);return $result;
}
function markKundliPaid(PDO $db,array $booking,array $payment): bool {
    if(!razorpayPaymentMatches($payment,$booking)){return false;}
    $stmt=$db->prepare("UPDATE kundli_bookings SET status='paid',razorpay_payment_id=?,paid_at=COALESCE(paid_at,CURRENT_TIMESTAMP) WHERE id=? AND (razorpay_payment_id IS NULL OR razorpay_payment_id=?)");
    $stmt->execute([$payment['id'],$booking['id'],$payment['id']]);
    return $stmt->rowCount()>0 || (($booking['status']??'')==='paid' && ($booking['razorpay_payment_id']??'')===$payment['id']);
}
function paymentJson(array $data,int $status=200): never { http_response_code($status);header('Content-Type: application/json');echo json_encode($data);exit; }
function paymentPostUser(string $tokenName = 'kundli_csrf'): array {
    if($_SERVER['REQUEST_METHOD']!=='POST'){paymentJson(['error'=>'Method not allowed.'],405);}
    if(!currentUser()){paymentJson(['error'=>'Please sign in before booking.'],401);}
    if(!hash_equals($_SESSION[$tokenName]??'',(string)($_POST['csrf_token']??'')) || empty($_SESSION[$tokenName])){paymentJson(['error'=>'Reload the booking page and try again.'],403);}
    return currentUser();
}
function kundliHistory(PDO $db,?int $userId): array {
    $s=$db->prepare('SELECT * FROM kundli_bookings'.($userId!==null?' WHERE user_id=?':'').' ORDER BY id DESC');$s->execute($userId!==null?[$userId]:[]);return $s->fetchAll();
}
function renderKundliHistory(array $bookings,bool $admin=false): void { ?>
<?php if(!$bookings): ?><p>No Darshan Kundli bookings yet.</p><?php endif; ?>
<?php foreach($bookings as $booking): ?><article class="order-history-card"><h3>Darshan Kundli #<?= (int)$booking['id'] ?> &middot; Rs. 99.00</h3><p><strong><?= $booking['status']==='paid'?'Paid':'Payment pending' ?></strong> &middot; <?= e($booking['created_at']) ?><?= str_starts_with($booking['razorpay_key_id'],'rzp_test_')?' &middot; Test payment':'' ?></p>
<?php if($admin): ?><p><?= e($booking['customer_name']) ?> &middot; <?= e($booking['contact_no']) ?> &middot; <?= e($booking['email']) ?></p><p>Birth details: <?= e($booking['birth_date']) ?> <?= e($booking['birth_time']) ?> &middot; <?= e($booking['birth_place']) ?></p><?php endif; ?>
<?php if($booking['razorpay_payment_id']): ?><p>Payment reference: <?= e($booking['razorpay_payment_id']) ?></p><?php endif; ?></article><?php endforeach; ?>
<?php }
