<?php
session_start();
require '../connect.php';
require '../functions.php';

$admin_page = true;
$setup_mode = true;
$password_optional = false;
$submit_label = 'Create admin account';
$user_count = (int) $db->query('SELECT COUNT(*) FROM users')->fetchColumn();

if ($user_count > 0) {
    header('Location: login.php');
    exit;
}

$user = ['username' => '', 'email' => '', 'role' => 'admin'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = user_input($_POST);
    $user['role'] = 'admin';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $errors = user_errors($user, $password, $password_confirm, true);

    if (!$errors) {
        $statement = $db->prepare(
            'INSERT INTO users (username, email, password_hash, role)
             VALUES (:username, :email, :password_hash, :role)'
        );
        $statement->execute([
            'username' => $user['username'],
            'email' => $user['email'],
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'admin',
        ]);
        header('Location: login.php');
        exit;
    }
}

require '../header.php';
?>

<main class="container admin-main">
    <div class="panel">
        <p class="eyebrow">First-time setup</p>
        <h1>Create the first admin account</h1>
        <?php require 'user_form.php'; ?>
    </div>
</main>

<?php require '../footer.php'; ?>
