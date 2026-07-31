<?php
require 'auth.php';
require '../connect.php';
require '../functions.php';

$admin_page = true;
$user_id = valid_id($_GET['id'] ?? $_POST['id'] ?? null);

if ($user_id === null || $user_id === (int) $_SESSION['user_id']) {
    exit('You cannot delete this user.');
}

$statement = $db->prepare('SELECT user_id, username, role FROM users WHERE user_id = :user_id');
$statement->execute(['user_id' => $user_id]);
$user = $statement->fetch();

if (!$user) {
    exit('User not found.');
}

if ($user['role'] === 'admin') {
    $admin_count = (int) $db->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
    if ($admin_count <= 1) {
        exit('The last admin account cannot be deleted.');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $statement = $db->prepare('DELETE FROM users WHERE user_id = :user_id');
    $statement->execute(['user_id' => $user_id]);
    header('Location: users.php');
    exit;
}

require '../header.php';
?>

<main class="container admin-main"><div class="panel"><p class="eyebrow">Confirm deletion</p><h1>Delete <?php echo e($user['username']); ?>?</h1><form method="post" class="admin-actions"><input type="hidden" name="id" value="<?php echo $user['user_id']; ?>"><button class="button danger" type="submit">Delete user</button><a class="button secondary" href="users.php">Cancel</a></form></div></main>

<?php require '../footer.php'; ?>
