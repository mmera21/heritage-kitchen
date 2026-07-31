<?php
require 'auth.php';
require '../connect.php';
require '../functions.php';

$admin_page = true;
$recipe_id = valid_id($_GET['id'] ?? $_POST['id'] ?? null);

if ($recipe_id === null) {
    exit('Invalid recipe ID.');
}

$statement = $db->prepare('SELECT recipe_id, title FROM recipes WHERE recipe_id = :recipe_id');
$statement->execute(['recipe_id' => $recipe_id]);
$recipe = $statement->fetch();

if (!$recipe) {
    exit('Recipe not found.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $statement = $db->prepare('DELETE FROM recipes WHERE recipe_id = :recipe_id');
    $statement->execute(['recipe_id' => $recipe_id]);
    header('Location: index.php?message=deleted');
    exit;
}

require '../header.php';
?>

<main class="container admin-main">
    <div class="panel">
        <p class="eyebrow">Confirm deletion</p>
        <h1>Delete <?php echo e($recipe['title']); ?>?</h1>
        <p>This action removes the recipe. Its linked images and comments will also be removed later when those features are added.</p>
        <form method="post" class="admin-actions">
            <input type="hidden" name="id" value="<?php echo $recipe['recipe_id']; ?>">
            <button class="button danger" type="submit">Delete recipe</button>
            <a class="button secondary" href="index.php">Cancel</a>
        </form>
    </div>
</main>

<?php require '../footer.php'; ?>
