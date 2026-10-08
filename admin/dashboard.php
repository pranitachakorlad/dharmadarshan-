<?php
require __DIR__ . '/auth.php'; requireAdmin(); require __DIR__ . '/layout.php';
$db=getDB(); adminHeader('Dashboard'); ?>
<p>Welcome, <?= e($_SESSION['admin_username']) ?>. Add products to the matching website category, publish blogs, and view registered users.</p>
<div class="admin-stats"><?php foreach (['Products'=>"SELECT COUNT(*) FROM products WHERE is_active=1",'Blogs'=>"SELECT COUNT(*) FROM blog_posts",'Registered users'=>"SELECT COUNT(*) FROM users"] as $label=>$sql): ?><div class="stat-card"><span class="stat-value"><?= (int)$db->query($sql)->fetchColumn() ?></span><span class="stat-label"><?= e($label) ?></span></div><?php endforeach; ?></div>
<div class="admin-actions"><a class="btn btn-primary" href="add-product.php">Add product</a><a class="btn btn-outline" href="users.php">Manage users</a><a class="btn btn-outline" href="blogs.php">Manage blogs</a></div>
<h2 style="margin-top:32px">Product categories</h2><div class="category-dashboard"><?php foreach(shopCollectionCards() as $card): $count=count(getProductsByCategory($db,$card['slug'])); ?><a class="category-tile" href="products.php?cat=<?= e($card['slug']) ?>"><img src="../<?= e($card['image']) ?>" alt=""><h2><?= e($card['name']) ?></h2><p><?= $count ?> products</p><span>Manage products &rarr;</span></a><?php endforeach; ?></div><?php adminFooter(); ?>
