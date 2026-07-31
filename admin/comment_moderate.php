<?php
require 'auth.php';
require '../connect.php';
require '../functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: comments.php');
    exit;
}

$comment_id = valid_id($_POST['comment_id'] ?? null);
$action = $_POST['action'] ?? '';

if ($comment_id === null || !in_array($action, ['hide', 'show'], true)) {
    exit('Invalid moderation request.');
}

$is_visible = $action === 'show' ? 1 : 0;
$statement = $db->prepare('UPDATE comments SET is_visible = :is_visible WHERE comment_id = :comment_id');
$statement->execute(['is_visible' => $is_visible, 'comment_id' => $comment_id]);

header('Location: comments.php');
exit;
