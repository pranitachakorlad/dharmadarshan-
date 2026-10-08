const payButton=document.getElementById('kundli-pay'),message=document.getElementById('payment-message');
const csrf=payButton.dataset.csrf;
async function paymentPost(url,values){const response=await fetch(url,{method:'POST',body:new URLSearchParams({...values,csrf_token:csrf}),credentials:'same-origin'});const data=await response.json();if(!response.ok)throw new Error(data.error||'Payment service unavailable.');return data;}
payButton.addEventListener('click',async()=>{payButton.disabled=true;message.textContent='Opening secure payment…';
try{if(typeof Razorpay==='undefined')throw new Error('Payment checkout could not load. Please reload the page.');const order=await paymentPost('razorpay-create-order.php',{});
const checkout=new Razorpay({key:order.key,amount:order.amount,currency:order.currency,order_id:order.order_id,name:'Dharma Darshan',description:'Darshan Kundli booking',prefill:order.prefill,
handler:async(result)=>{message.textContent='Verifying your payment…';try{const verification=await paymentPost('razorpay-verify.php',result);if(verification.paid){window.location.href='account.php#kundli-bookings';}else{message.textContent=verification.message;payButton.disabled=false;}}catch(error){message.textContent=error.message;payButton.disabled=false;}},
modal:{ondismiss:()=>{message.textContent='Payment checkout closed. You can try again.';payButton.disabled=false;}}});
checkout.on('payment.failed',()=>{message.textContent='Payment failed. Check your payment status before retrying.';payButton.disabled=false;});checkout.open();
}catch(error){message.textContent=error.message;payButton.disabled=false;}});
