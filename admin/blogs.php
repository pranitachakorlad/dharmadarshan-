<?php
require_once __DIR__ . '/auth.php';
requireAdmin();

$db = getDB();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $title = trim($_POST['title'] ?? '');
        $excerpt = trim($_POST['excerpt'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $isPublished = isset($_POST['is_published']) ? 1 : 0;

        if ($title === '' || mb_strlen($title) > 255 || $content === '') {
            $error = 'Blog title (up to 255 characters) and content are required.';
        } else {
            $slug = uniqueBlogSlug($db, $title);
            try { $image = handleBlogUpload('image') ?? 'assets/images/placeholder-blog.svg'; } catch (RuntimeException $ex) { $error = $ex->getMessage(); }
            if (!$error) {

            $stmt = $db->prepare("
                INSERT INTO blog_posts (title, slug, excerpt, content, image, is_published)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$title, $slug, $excerpt, nl2br(e($content)), $image, $isPublished]);
            $message = 'Blog post added successfully.';
            }
        }
    }

    if ($action === 'toggle') {
        $id = (int) ($_POST['post_id'] ?? 0);
        $isPublished = (int) ($_POST['is_published'] ?? 0);
        if ($id > 0) {
            $stmt = $db->prepare("UPDATE blog_posts SET is_published = ? WHERE id = ?");
            $stmt->execute([$isPublished, $id]);
            $message = $isPublished ? 'Blog post published.' : 'Blog post hidden.';
        }
    }

    if ($action === 'delete') {
        $id = (int) ($_POST['post_id'] ?? 0);
        if ($id > 0) {
            $stmt = $db->prepare("DELETE FROM blog_posts WHERE id = ?");
            $stmt->execute([$id]);
            $message = 'Blog post deleted.';
        }
    }
}

$posts = $db->query("SELECT * FROM blog_posts ORDER BY created_at DESC")->fetchAll();

$isAdminPage = true;
$activeAdmin = 'blogs';
$pageTitle = 'Manage Blogs';
require_once __DIR__ . '/layout.php'; adminHeader($pageTitle);
?>

<section class="section admin-section">
    <div class="container">
        <header class="section-header">
            
            <p>Add guidance articles, remedies, product education, and spiritual updates.</p>
        </header>

        <?php if ($message): ?>
        <div class="alert alert-success"><?= e($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <div class="admin-panel">
            <h2>Add Blog Post</h2>
            <form method="post" enctype="multipart/form-data" class="admin-form"><?= adminTokenField() ?>
                <input type="hidden" name="action" value="add">
                <label>
                    Title *
                    <input type="text" name="title" maxlength="255" required value="<?= e($_POST['title'] ?? '') ?>">
                </label>
                <label>
                    Short Description
                    <textarea name="excerpt" rows="3"><?= e($_POST['excerpt'] ?? '') ?></textarea>
                </label>
                <label>
                    Blog Content *
                    <textarea name="content" rows="8" required><?= e($_POST['content'] ?? '') ?></textarea>
                </label>
                <label>
                    Blog Image
                    <input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif">
                </label>
                <label class="checkbox-label">
                    <input type="checkbox" name="is_published" value="1" checked>
                    Publish on website
                </label>
                <button type="submit" class="btn btn-primary">Save Blog</button>
            </form>
        </div>

        <div class="admin-panel">
            <h2>Current Blog Posts (<?= count($posts) ?>)</h2>
            <?php if (empty($posts)): ?>
            <p class="empty-state">No blog posts yet.</p>
            <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as $post): ?>
                    <tr>
                        <td>
                            <strong><?= e($post['title']) ?></strong><br>
                            <small><?= e($post['slug']) ?></small>
                        </td>
                        <td><?= !empty($post['is_published']) ? 'Published' : 'Hidden' ?></td>
                        <td><?= e(date('d M Y', strtotime($post['created_at']))) ?></td>
                        <td>
                            <a class="btn btn-sm btn-outline" href="edit-blog.php?id=<?= (int) $post['id'] ?>">Edit</a>
                            <form method="post" class="inline-form"><?= adminTokenField() ?>
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">
                                <input type="hidden" name="is_published" value="<?= !empty($post['is_published']) ? 0 : 1 ?>">
                                <button type="submit" class="btn btn-sm btn-outline">
                                    <?= !empty($post['is_published']) ? 'Hide' : 'Publish' ?>
                                </button>
                            </form>
                            <form method="post" class="inline-form" onsubmit="return confirm('Delete this blog post?');"><?= adminTokenField() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php adminFooter(); ?>
