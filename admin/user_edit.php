<?php
require 'auth.php';
require '../connect.php';
require '../functions.php';

$admin_page = true;
$setup_mode = false;
$password_optional = true;
$submit_label = 'Update user';
$user_id = valid_id($_GET['id'] ?? null);

if ($user_id === null) {
    exit('Invalid user ID.');
}

$statement = $db->prepare('SELECT user_id, username, email, role FROM users WHERE user_id = :user_id');
$statement->execute(['user_id' => $user_id]);
$user = $statement->fetch();

if (!$user) {
    exit('User not found.');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = user_input($_POST);
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $errors = user_errors($user, $password, $password_confirm, false);

    if (!$errors) {
        try {
            if ($password !== '') {
                $statement = $db->prepare(
                    'UPDATE users SET username = :username, email = :email, role = :role, password_hash = :password_hash WHERE user_id = :user_id'
                );
                $user['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
            } else {
                $statement = $db->prepare('UPDATE users SET username = :username, email = :email, role = :role WHERE user_id = :user_id');
            }

            $user['user_id'] = $user_id;
            $statement->execute($user);
            header('Location: users.php');
            exit;
        } catch (PDOException $error) {
            $errors[] = 'Username or email address is already in use.';
        }
    }
}

require '../header.php';
?>

<main class="container admin-main"><div class="panel"><p class="eyebrow">User management</p><h1>Edit user</h1><?php require 'user_form.php'; ?></div></main>

<?php require '../footer.php'; ?>
