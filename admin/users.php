<?php
require 'auth.php';
require '../connect.php';
require '../functions.php';

$admin_page = true;
$users = $db->query('SELECT user_id, username, email, role, created_at FROM users ORDER BY username ASC')->fetchAll();

require '../header.php';
?>

<main class="container admin-main">
    <p class="eyebrow">User management</p>
    <h1>Manage users</h1>
    <div class="admin-actions">
        <a class="button" href="user_new.php">Add a user</a>
        <a class="button secondary" href="index.php">Manage recipes</a>
    </div>

    <div class="panel">
        <table>
            <thead><tr><th>Username</th><th>Email</th><th>Role</th><th>Created</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo e($user['username']); ?></td>
                        <td><?php echo e($user['email']); ?></td>
                        <td><?php echo e($user['role']); ?></td>
                        <td><?php echo e($user['created_at']); ?></td>
                        <td>
                            <a href="user_edit.php?id=<?php echo $user['user_id']; ?>">Edit</a>
                            <?php if ((int) $user['user_id'] !== (int) $_SESSION['user_id']): ?>
                                <a href="user_delete.php?id=<?php echo $user['user_id']; ?>">Delete</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<?php require '../footer.php'; ?>
