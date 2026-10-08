# Dharma Darshan

PHP and MySQL website with nine product categories, admin product/blog/user management, saved orders, and Razorpay checkout for the Rs.99 Kundli booking and product purchases.

## Setup

1. Upload these files into public_html or run PHP locally.
2. Import your database backup privately using phpMyAdmin. Customer data and the database backup are intentionally excluded from this repository.
3. Set the database credentials in config/database.php.
4. Configure matching Razorpay API keys and webhook secret in config/razorpay.php or the environment. Never commit actual credentials.
5. Follow RAZORPAY-SETUP.txt and HOSTINGER-STEPS.txt for payment activation and hosting setup.

Razorpay keys are unconfigured. Complete a Razorpay test payment before enabling live payments. The application creates order and payment-history tables automatically after the base database is imported.
