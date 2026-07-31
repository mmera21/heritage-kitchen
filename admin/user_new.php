<?php
require 'auth.php';
require '../connect.php';
require '../functions.php';

$admin_page = true;
$setup_mode = false;
$password_optional = false;
$submit_label = 'Create user';
$user = ['username' => '', 'email' => '', 'role' => 'member'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = user_input($_POST);
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
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
                'role' => $user['role'],
            ]);
            header('Location: users.php');
            exit;
        } catch (PDOException $error) {
            $errors[] = 'Username or email address is already in use.';
        }
    }
}

require '../header.php';
?>

<main class="container admin-main"><div class="panel"><p class="eyebrow">User management</p><h1>Add a user</h1><?php require 'user_form.php'; ?></div></main>

<?php require '../footer.php'; ?>
