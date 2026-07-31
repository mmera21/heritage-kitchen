<?php
require 'auth.php';
require '../connect.php';
require '../functions.php';

$admin_page = true;
$statement = $db->query(
    'SELECT comments.comment_id, comments.commenter_name, comments.comment_text,
            comments.is_visible, comments.created_at, recipes.recipe_id, recipes.title
     FROM comments
     INNER JOIN recipes ON comments.recipe_id = recipes.recipe_id
     ORDER BY comments.created_at DESC'
);
$comments = $statement->fetchAll();

require '../header.php';
?>

<main class="container admin-main">
    <p class="eyebrow">Comment moderation</p>
    <h1>Moderate comments</h1>
    <p>Hide inappropriate comments from the public site, restore them when appropriate, or remove them permanently.</p>

    <div class="admin-actions">
        <a class="button secondary" href="index.php">Manage recipes</a>
    </div>

    <?php if ($comments): ?>
        <div class="panel">
            <table>
                <thead>
                    <tr>
                        <th>Recipe</th>
                        <th>Comment</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($comments as $comment): ?>
                        <tr>
                            <td><?php echo e($comment['title']); ?></td>
                            <td><strong><?php echo e($comment['commenter_name']); ?></strong><br><?php echo e($comment['comment_text']); ?></td>
                            <td><?php echo $comment['is_visible'] ? 'Visible' : 'Hidden'; ?></td>
                            <td>
                                <form class="inline-form" method="post" action="comment_moderate.php">
                                    <input type="hidden" name="comment_id" value="<?php echo $comment['comment_id']; ?>">
                                    <input type="hidden" name="action" value="<?php echo $comment['is_visible'] ? 'hide' : 'show'; ?>">
                                    <button class="text-button" type="submit"><?php echo $comment['is_visible'] ? 'Hide' : 'Show'; ?></button>
                                </form>
                                <a class="text-button danger-link" href="comment_delete.php?id=<?php echo $comment['comment_id']; ?>">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="notice">There are no comments to moderate yet.</p>
    <?php endif; ?>
</main>

<?php require '../footer.php'; ?>
