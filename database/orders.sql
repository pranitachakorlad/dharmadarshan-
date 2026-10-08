
CREATE TABLE IF NOT EXISTS orders (
 id INT AUTO_INCREMENT PRIMARY KEY,
 user_id INT NOT NULL,
 checkout_token VARCHAR(64) NOT NULL UNIQUE,
 customer_name VARCHAR(100) NOT NULL,
 contact_no VARCHAR(20) NOT NULL,
 alt_contact_no VARCHAR(20) NOT NULL DEFAULT '',
 address TEXT NOT NULL,
 payment_method VARCHAR(40) NOT NULL,
 status VARCHAR(30) NOT NULL DEFAULT 'Pending confirmation',
 total DECIMAL(12,2) NOT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_orders_user (user_id,created_at),
 FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS order_items (
 id INT AUTO_INCREMENT PRIMARY KEY,
 order_id INT NOT NULL,
 product_id INT NULL,
 product_name VARCHAR(200) NOT NULL,
 product_image VARCHAR(255) NOT NULL,
 unit_price DECIMAL(10,2) NOT NULL,
 quantity INT NOT NULL,
 line_total DECIMAL(12,2) NOT NULL,
 FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS kundli_bookings (
 id INT AUTO_INCREMENT PRIMARY KEY,
 user_id INT NOT NULL,
 amount_paise INT NOT NULL DEFAULT 9900,
 currency VARCHAR(3) NOT NULL DEFAULT 'INR',
 razorpay_key_id VARCHAR(100) NOT NULL,
 razorpay_order_id VARCHAR(100) NULL UNIQUE,
 razorpay_payment_id VARCHAR(100) NULL UNIQUE,
 status VARCHAR(20) NOT NULL DEFAULT 'pending',
 customer_name VARCHAR(100) NOT NULL,
 contact_no VARCHAR(20) NOT NULL,
 email VARCHAR(150) NOT NULL,
 birth_date DATE NULL,
 birth_time TIME NULL,
 birth_place VARCHAR(200) NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 paid_at TIMESTAMP NULL,
 INDEX idx_kundli_user (user_id,created_at),
 FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS product_payments (
 id INT AUTO_INCREMENT PRIMARY KEY,
 order_id INT NOT NULL UNIQUE,
 razorpay_key_id VARCHAR(100) NOT NULL,
 razorpay_order_id VARCHAR(100) NULL UNIQUE,
 razorpay_payment_id VARCHAR(100) NULL UNIQUE,
 amount_paise BIGINT NOT NULL,
 currency VARCHAR(3) NOT NULL DEFAULT 'INR',
 cart_snapshot TEXT NOT NULL,
 status VARCHAR(20) NOT NULL DEFAULT 'pending',
 paid_at TIMESTAMP NULL,
 FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
