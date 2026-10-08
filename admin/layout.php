<?php
function adminHeader(string $title): void { ?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= e($title) ?> | Dharma Darshan</title><link rel="stylesheet" href="../assets/css/style.css"><link rel="stylesheet" href="admin.css"></head><body class="management"><header class="management-header"><a href="dashboard.php" class="management-brand"><img src="../assets/images/logo.png" alt="">Dharma Darshan <small>Administration</small></a><nav><a href="dashboard.php">Dashboard</a><a href="products.php">Products</a><a href="blogs.php">Blogs</a><a href="users.php">Users</a><a href="orders.php">Orders</a><a href="bookings.php">Kundli bookings</a><a href="../index.php" target="_blank">View website</a><a href="logout.php">Logout</a></nav></header><main class="management-main"><h1><?= e($title) ?></h1>
<?php }
function adminFooter(): void { ?></main></body></html><?php }
