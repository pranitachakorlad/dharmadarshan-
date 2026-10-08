<?php
require_once __DIR__ . '/auth.php';
requireAdmin(true);

$db = getDB();
$users = $db->query("
    SELECT id, name, contact_no, email, birth_date, birth_time, birth_place, created_at
    FROM users
    ORDER BY created_at DESC
")->fetchAll();

$isAdminPage = true;
$activeAdmin = 'users';
$pageTitle = 'Users';
require_once __DIR__ . '/layout.php'; adminHeader($pageTitle);
?>

<section class="section admin-section">
    <div class="container">
        <header class="section-header">
            
            <p>View registered users and their birth details.</p>
        </header>

        <div class="admin-panel">
            <h2>Registered Users (<?= count($users) ?>)</h2>
            <?php if (empty($users)): ?>
            <p class="empty-state">No users have registered yet.</p>
            <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>User ID</th><th>Full Name</th>
                        <th>Contact No.</th>
                        <th>Email</th>
                        <th>Birth Date</th>
                        <th>Birth Time</th>
                        <th>Birth Place</th>
                        <th>Registered</th><th>Orders</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= (int) $user['id'] ?></td><td><?= e($user['name']) ?></td>
                        <td><?= e($user['contact_no'] ?? '') ?></td>
                        <td><?= e($user['email']) ?></td>
                        <td><?= e($user['birth_date'] ?? '') ?></td>
                        <td><?= e(!empty($user['birth_time']) ? substr($user['birth_time'], 0, 5) : '') ?></td>
                        <td><?= e($user['birth_place'] ?? '') ?></td>
                        <td><?= e(date('d M Y', strtotime($user['created_at']))) ?></td><td><a href="orders.php?user=<?= (int) $user['id'] ?>">View orders</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php adminFooter(); ?>
