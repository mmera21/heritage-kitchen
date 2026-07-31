<?php
session_start();
require '../connect.php';
require '../functions.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: ' . (($_SESSION['role'] ?? '') === 'admin' ? 'index.php' : '../index.php'));
    exit;
}

$admin_page = true;
$error = '';
$username = '';
$user_count = (int) $db->query('SELECT COUNT(*) FROM users')->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $statement = $db->prepare('SELECT user_id, username, password_hash, role FROM users WHERE username = :username');
    $statement->execute(['username' => $username]);
    $user = $statement->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['is_admin'] = $user['role'] === 'admin';
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['login_message'] = 'Login successful. Welcome, ' . $user['username'] . '!';
        header('Location: ' . ($user['role'] === 'admin' ? 'index.php' : '../index.php'));
        exit;
    }

    $error = 'Incorrect username or password.';
}

require '../header.php';
?>

<main class="container admin-main">
    <div class="panel">
        <p class="eyebrow">Administrative access</p>
        <h1>Family Chef Login</h1>

        <?php if (isset($_GET['registered']) && $_GET['registered'] === '1'): ?>
            <p class="notice">Registration successful. You can now log in.</p>
        <?php endif; ?>

        <?php if ($user_count === 0): ?>
            <p class="notice">No admin account exists yet. <a href="setup.php">Create the first admin account</a>.</p>
        <?php endif; ?>

        <?php if ($error): ?>
            <p class="error-list"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>

        <form method="post">
            <label for="username">Username</label>
            <input id="username" name="username" type="text" value="<?php echo e($username); ?>" required>

            <label for="password">Password</label>
            <input id="password" name="password" type="password" required>

            <div class="admin-actions">
                <button class="button" type="submit">Log in</button>
            </div>
        </form>
    </div>
</main>

<?php require '../footer.php'; ?>
