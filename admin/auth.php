<?php
require_once __DIR__ . '/../includes/session.php';

startAppSession();
require_once __DIR__ . '/../includes/functions.php';

function requireAdmin(bool $usersSection = false): void
{
    if (empty($_SESSION['admin_id'])) { header('Location: login.php'); exit; }
    $stmt = getDB()->prepare('SELECT username, role FROM admin_users WHERE id = ?');
    $stmt->execute([$_SESSION['admin_id']]);
    $account = $stmt->fetch();
    if (!$account || $account['username'] !== 'Lalitmahajan' || $account['role'] !== 'admin') { unset($_SESSION['admin_id']); header('Location: login.php'); exit; }
    $_SESSION['admin_role'] = $account['role'];
    if (!$usersSection && $account['role'] === 'users') { header('Location: users.php'); exit; }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !hash_equals(adminToken(), (string) ($_POST['csrf_token'] ?? ''))) { http_response_code(403); exit('Please reload this page and try again.'); }
}
function adminToken(): string { return $_SESSION['admin_csrf'] ??= bin2hex(random_bytes(32)); }
function adminTokenField(): string { return '<input type="hidden" name="csrf_token" value="' . e(adminToken()) . '">'; }

function getAllCategories(PDO $db, bool $includeOther = true): array
{
    $sql = "SELECT * FROM categories";
    if (!$includeOther) {
        $sql .= " WHERE slug != 'other'";
    }
    $sql .= " ORDER BY id ASC";
    return $db->query($sql)->fetchAll();
}

function getOtherCategoryId(PDO $db): ?int
{
    $stmt = $db->prepare("SELECT id FROM categories WHERE slug = 'other' LIMIT 1");
    $stmt->execute();
    $row = $stmt->fetch();
    return $row ? (int) $row['id'] : null;
}

function adminUpload(string $field, string $folder): ?string
{
    if (empty($_FILES[$field]['name'])) { return null; }
    if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) { throw new RuntimeException('Image upload failed. Choose a smaller image and try again.'); }
    if ($_FILES[$field]['size'] > 5 * 1024 * 1024) { throw new RuntimeException('Image must be under 5 MB.'); }
    $types = ['image/jpeg'=>'jpg', 'image/png'=>'png', 'image/webp'=>'webp', 'image/gif'=>'gif'];
    $mime = mime_content_type($_FILES[$field]['tmp_name']);
    if (!isset($types[$mime]) || !getimagesize($_FILES[$field]['tmp_name'])) { throw new RuntimeException('Choose a valid JPG, PNG, WebP or GIF image.'); }
    $directory = __DIR__ . '/../assets/uploads/' . $folder;
    if (!is_dir($directory) && !mkdir($directory, 0755, true)) { throw new RuntimeException('Upload directory unavailable.'); }
    $filename = bin2hex(random_bytes(16)) . '.' . $types[$mime];
    if (!move_uploaded_file($_FILES[$field]['tmp_name'], $directory . '/' . $filename)) { throw new RuntimeException('Could not save image.'); }
    return 'assets/uploads/' . $folder . '/' . $filename;
}
function handleProductUpload(string $fieldName): ?string { return adminUpload($fieldName, 'products'); }

function slugify(string $value): string
{
    $slug = strtolower(trim($value));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim($slug ?? '', '-');
    return $slug !== '' ? $slug : 'post-' . time();
}

function uniqueBlogSlug(PDO $db, string $title): string
{
    $base = slugify($title);
    $slug = $base;
    $i = 2;

    while (true) {
        $stmt = $db->prepare("SELECT id FROM blog_posts WHERE slug = ? LIMIT 1");
        $stmt->execute([$slug]);
        if (!$stmt->fetch()) {
            return $slug;
        }
        $slug = $base . '-' . $i;
        $i++;
    }
}

function handleBlogUpload(string $fieldName): ?string { return adminUpload($fieldName, 'blogs'); }
