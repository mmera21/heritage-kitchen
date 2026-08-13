<?php
require 'connect.php';
require 'functions.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$user = ['username' => '', 'email' => '', 'role' => 'member'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = posted_trim('username');
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW);
    $password_confirm = filter_input(INPUT_POST, 'password_confirm', FILTER_UNSAFE_RAW);

    $user = user_input([
        'username' => $username,
        'email' => is_string($email) ? $email : '',
        'role' => 'member',
    ]);
    $password = is_string($password) ? $password : '';
    $password_confirm = is_string($password_confirm) ? $password_confirm : '';
    $errors = user_errors($user, $password, $password_confirm, true);

    if (!$errors) {
        try {
            $statement = $db->prepare(
                'INSERT INTO users (username, email, password_hash, role)
                VALUES (:username, :email, :password_hash, :role)'
            );
            $statement->execute([
                'username' => $user['username'],
                'email' => $user['email'],
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'role' => 'member',
            ]);
            header('Location: admin/login.php?registered=1');
            exit;
        } catch (PDOException $error) {
            $errors[] = 'Username or email address is already in use.';
        }
    }
}

require 'header.php';
?>

<main class="container admin-main">
    <div class="panel">
        <p class="eyebrow">Family account</p>
        <h1>Create an account</h1>
        <p>Register to keep your place in The Heritage Kitchen. New accounts have member access.</p>

        <?php if ($errors): ?>
            <div class="error-list">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post">
            <label for="username">Username</label>
            <input id="username" name="username" type="text" value="<?php echo e($user['username']); ?>" required>

            <label for="email">Email address</label>
            <input id="email" name="email" type="email" value="<?php echo e($user['email']); ?>" required>

            <label for="password">Password</label>
            <input id="password" name="password" type="password" required>

            <label for="password_confirm">Repeat password</label>
            <input id="password_confirm" name="password_confirm" type="password" required>

            <div class="admin-actions">
                <button class="button" type="submit">Create account</button>
            </div>
        </form>
    </div>
</main>

<?php require 'footer.php'; ?>
