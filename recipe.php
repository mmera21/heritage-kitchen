<!-- A este punto ya me canse de dejar comentarios, quien vea esto, deje lagrimas y muchas, asi que si les sirve de algo adelante -->

<?php
session_start();
require 'connect.php';
require 'functions.php';

$recipe_id = valid_id($_GET['id'] ?? null);
$slug = trim(strip_tags($_GET['slug'] ?? ''));

if ($recipe_id === null || $slug === '') {
    http_response_code(404);
    exit('Recipe not found.');
}

$statement = $db->prepare(
    'SELECT recipes.*, lineages.lineage_name
    FROM recipes
    LEFT JOIN lineages ON recipes.lineage_id = lineages.lineage_id
    WHERE recipes.recipe_id = :recipe_id AND recipes.slug = :slug'
);
$statement->execute(['recipe_id' => $recipe_id, 'slug' => $slug]);
$recipe = $statement->fetch();

if (!$recipe) {
    http_response_code(404);
    exit('Recipe not found.');
}

$comment = ['commenter_name' => '', 'comment_text' => ''];
$comment_errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment = comment_input($_POST);
    $comment_errors = comment_errors($comment);
    $captcha_answer = strtoupper(trim($_POST['captcha_answer'] ?? ''));
    $captcha_code = $_SESSION['captcha_code'] ?? '';

    if ($captcha_code === '' || !hash_equals($captcha_code, $captcha_answer)) {
        $comment_errors[] = 'The CAPTCHA code was incorrect. Please try again.';
    }

    if (!$comment_errors) {
        $statement = $db->prepare(
            'INSERT INTO comments (recipe_id, commenter_name, comment_text)
            VALUES (:recipe_id, :commenter_name, :comment_text)'
        );
        $statement->execute([
            'recipe_id' => $recipe_id,
            'commenter_name' => $comment['commenter_name'],
            'comment_text' => $comment['comment_text'],
        ]);

        unset($_SESSION['captcha_code']);
        $_SESSION['comment_message'] = 'Thank you. Your comment has been added.';

        header('Location: ' . recipe_permalink($recipe_id, $recipe['slug']));
        exit;
    }
}

$statement = $db->prepare(
    'SELECT commenter_name, comment_text, created_at
    FROM comments
    WHERE recipe_id = :recipe_id AND is_visible = 1
    ORDER BY created_at DESC'
);
$statement->execute(['recipe_id' => $recipe_id]);
$comments = $statement->fetchAll();

$statement = $db->prepare(
    'SELECT image_id, image_path, image_caption
    FROM images
    WHERE recipe_id = :recipe_id
    ORDER BY image_id ASC'
);
$statement->execute(['recipe_id' => $recipe_id]);
$images = $statement->fetchAll();

require 'header.php';
?>

<main class="container recipe-page">
    <p class="eyebrow"><?php echo e($recipe['lineage_name'] ?? 'Uncategorized'); ?></p>
    <h1><?php echo e($recipe['title']); ?></h1>
    <p>Introduced by <?php echo e($recipe['introduced_by']); ?></p>

    <?php if ($images): ?>
        <section class="recipe-image-gallery" aria-label="Recipe images">
            <?php foreach ($images as $image): ?>
                <figure>
                    <img src="<?php echo e(application_url($image['image_path'])); ?>" alt="<?php echo e($image['image_caption'] ?: 'Photo of ' . $recipe['title']); ?>">
                    <?php if ($image['image_caption']): ?>
                        <figcaption><?php echo e($image['image_caption']); ?></figcaption>
                    <?php endif; ?>
                </figure>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>

    <div class="recipe-details">
        <section>
            <h2>Ingredients</h2>
            <p><?php echo nl2br(e($recipe['ingredients'])); ?></p>
        </section>
        <section>
            <h2>Instructions</h2>
            <div class="rich-text-content"><?php echo sanitize_rich_text($recipe['instructions']); ?></div>
        </section>
    </div>

    <section class="comments-section" aria-labelledby="comments-heading">
        <p class="eyebrow">Family memories</p>
        <h2 id="comments-heading">Comments</h2>

        <?php if (!empty($_SESSION['comment_message'])): ?>
            <p class="notice"><?php echo e($_SESSION['comment_message']); unset($_SESSION['comment_message']); ?></p>
        <?php endif; ?>

        <?php if ($comments): ?>
            <?php foreach ($comments as $posted_comment): ?>
                <article class="comment-card">
                    <h3><?php echo e($posted_comment['commenter_name']); ?></h3>
                    <p class="comment-date"><?php echo e($posted_comment['created_at']); ?></p>
                    <p><?php echo nl2br(e($posted_comment['comment_text'])); ?></p>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Be the first person to share a cooking tip or family memory.</p>
        <?php endif; ?>

        <div class="panel">
            <h3>Leave a comment</h3>
            <?php if ($comment_errors): ?>
                <div class="error-list">
                    <ul>
                        <?php foreach ($comment_errors as $error): ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post">
                <label for="commenter_name">Your name</label>
                <input id="commenter_name" name="commenter_name" type="text" value="<?php echo e($comment['commenter_name']); ?>" required>

                <label for="comment_text">Your cooking tip or memory</label>
                <textarea id="comment_text" name="comment_text" required><?php echo e($comment['comment_text']); ?></textarea>

                <label for="captcha_answer">Security check *</label>
                <div class="captcha-row">
                    <img src="<?php echo e(application_url('captcha.php')); ?>" alt="CAPTCHA security code" width="160" height="50">
                    <input id="captcha_answer" name="captcha_answer" type="text" maxlength="6" autocomplete="off" required>
                </div>
                <p class="captcha-help">Type the characters shown in the image. This helps prevent spam.</p>

                <div class="admin-actions">
                    <button class="button" type="submit">Post comment</button>
                </div>
            </form>
        </div>
    </section>
</main>

<?php require 'footer.php'; ?>
