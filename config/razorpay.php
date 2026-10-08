<?php
// Enter your Razorpay keys here on Hostinger, or configure these environment variables.
// Start with TEST keys. Replace with LIVE keys only after activation and testing.
define('RAZORPAY_KEY_ID', getenv('RAZORPAY_KEY_ID') ?: '');
define('RAZORPAY_KEY_SECRET', getenv('RAZORPAY_KEY_SECRET') ?: '');
// Separate secret chosen in Razorpay Dashboard when adding the webhook.
define('RAZORPAY_WEBHOOK_SECRET', getenv('RAZORPAY_WEBHOOK_SECRET') ?: '');
