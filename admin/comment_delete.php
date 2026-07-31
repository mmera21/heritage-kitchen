<?php
require 'auth.php';
require '../connect.php';
require '../functions.php';

$comment_id = valid_id($_GET['id'] ?? $_POST['id'] ?? null);

if ($comment_id === null) {
    exit('Invalid comment ID.');
}

$statement = $db->prepare(
    'SELECT comments.comment_id, comments.commenter_name, comments.comment_text, recipes.title
     FROM comments
     INNER JOIN recipes ON comments.recipe_id = recipes.recipe_id
     WHERE comments.comment_id = :comment_id'
);
$statement->execute(['comment_id' => $comment_id]);
$comment = $statement->fetch();

if (!$comment) {
    exit('Comment not found.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $statement = $db->prepare('DELETE FROM comments WHERE comment_id = :comment_id');
    $statement->execute(['comment_id' => $comment_id]);
    header('Location: comments.php');
    exit;
}

$admin_page = true;
require '../header.php';
?>

<main class="container admin-main">
    <div class="panel">
        <p class="eyebrow">Confirm deletion</p>
        <h1>Delete this comment?</h1>
        <p><strong><?php echo e($comment['commenter_name']); ?></strong> wrote on <em><?php echo e($comment['title']); ?></em>:</p>
        <p><?php echo e($comment['comment_text']); ?></p>
        <form method="post" class="admin-actions">
            <input type="hidden" name="id" value="<?php echo $comment['comment_id']; ?>">
            <button class="button danger" type="submit">Delete comment</button>
            <a class="button secondary" href="comments.php">Cancel</a>
        </form>
    </div>
</main>

<?php require '../footer.php'; ?>
