<?php
require 'connect.php';
require 'functions.php';

$lineage_id = valid_id($_GET['id'] ?? null);

if ($lineage_id === null) {
    http_response_code(404);
    exit('Family lineage not found.');
}

$statement = $db->prepare('SELECT * FROM lineages WHERE lineage_id = :lineage_id');
$statement->execute(['lineage_id' => $lineage_id]);
$lineage = $statement->fetch();

if (!$lineage) {
    http_response_code(404);
    exit('Family lineage not found.');
}

$statement = $db->prepare(
    'SELECT recipes.recipe_id, recipes.title, recipes.slug, recipes.introduced_by,
            (SELECT image_path FROM images WHERE images.recipe_id = recipes.recipe_id ORDER BY image_id ASC LIMIT 1) AS image_path,
            (SELECT image_caption FROM images WHERE images.recipe_id = recipes.recipe_id ORDER BY image_id ASC LIMIT 1) AS image_caption
     FROM recipes
     WHERE lineage_id = :lineage_id
     ORDER BY title ASC'
);
$statement->execute(['lineage_id' => $lineage_id]);
$recipes = $statement->fetchAll();

require 'header.php';
?>

<main class="container recipe-page">
    <p class="eyebrow">Family lineage</p>
    <h1><?php echo e($lineage['lineage_name']); ?></h1>
    <?php if ($lineage['region']): ?>
        <p><strong>Region:</strong> <?php echo e($lineage['region']); ?></p>
    <?php endif; ?>
    <?php if ($lineage['description']): ?>
        <p><?php echo nl2br(e($lineage['description'])); ?></p>
    <?php endif; ?>

    <h2>Recipes from this family</h2>
    <?php if ($recipes): ?>
        <div class="recipe-grid row g-4">
            <?php foreach ($recipes as $recipe): ?>
                <article class="recipe-card col-md-6 col-lg-4">
                    <?php if ($recipe['image_path']): ?>
                        <img class="recipe-card-image" src="<?php echo e(application_url($recipe['image_path'])); ?>" alt="<?php echo e($recipe['image_caption'] ?: 'Photo of ' . $recipe['title']); ?>">
                    <?php endif; ?>
                    <h3><?php echo e($recipe['title']); ?></h3>
                    <p>Introduced by <?php echo e($recipe['introduced_by']); ?></p>
                    <a href="<?php echo e(recipe_permalink((int) $recipe['recipe_id'], $recipe['slug'])); ?>">Read recipe</a>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="notice">No recipes have been added to this family yet.</p>
    <?php endif; ?>
</main>

<?php require 'footer.php'; ?>
