<?php
require_once __DIR__ . '/includes/functions.php';

$slug = $_GET['slug'] ?? '';
$db = getDB();

$stmt = $db->prepare("SELECT * FROM blog_posts WHERE slug = ? AND is_published = 1 LIMIT 1");
$stmt->execute([$slug]);
$post = $stmt->fetch();

if (!$post) {
    header('Location: index.php#blog');
    exit;
}

$pageTitle = $post['title'];
require_once __DIR__ . '/includes/header.php';
?>

<article class="section blog-article">
    <div class="container container-narrow">
        <header class="article-header">
            <a href="index.php#blog" class="back-link">&larr; Back to Blog</a>
            <time datetime="<?= e(date('Y-m-d', strtotime($post['created_at']))) ?>">
                <?= e(date('F j, Y', strtotime($post['created_at']))) ?>
            </time>
            <h1><?= e($post['title']) ?></h1>
        </header>
        <?php if (!empty($post['image'])): ?>
        <figure class="article-image">
            <img src="<?= e(productImageUrl($post['image'], 'assets/images/placeholder-blog.svg')) ?>" alt="<?= e($post['title']) ?>">
        </figure>
        <?php endif; ?>
        <div class="article-content">
            <?= $post['content'] ?>
        </div>
    </div>
</article>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
