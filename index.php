<?php
require 'connect.php';

$statement = $db->query(
    'SELECT recipes.recipe_id, recipes.title, recipes.slug, recipes.introduced_by,
            recipes.created_at, lineages.lineage_name,
            (SELECT image_path FROM images WHERE images.recipe_id = recipes.recipe_id ORDER BY image_id ASC LIMIT 1) AS image_path,
            (SELECT image_caption FROM images WHERE images.recipe_id = recipes.recipe_id ORDER BY image_id ASC LIMIT 1) AS image_caption
     FROM recipes
     LEFT JOIN lineages ON recipes.lineage_id = lineages.lineage_id
     ORDER BY recipes.created_at DESC'
);
$recipes = $statement->fetchAll();
$lineages = $db->query('SELECT lineage_id, lineage_name FROM lineages ORDER BY lineage_name')->fetchAll();

require 'header.php';
?>

<section class="hero">
    <p class="eyebrow">A digital family cookbook</p>
    <h1>The Heritage Kitchen</h1>
    <p>Recipes, stories, and traditions from the Mera, Rodriguez, and Galarce families.</p>
</section>

<main class="container">
    <?php if (!empty($_SESSION['login_message'])): ?>
        <p class="notice"><?php echo e($_SESSION['login_message']); unset($_SESSION['login_message']); ?></p>
    <?php endif; ?>
    <section class="lineage-navigation" aria-labelledby="lineage-heading">
        <p class="eyebrow">Explore our roots</p>
        <h2 id="lineage-heading">Browse by family</h2>
        <div class="lineage-links">
            <?php foreach ($lineages as $lineage): ?>
                <a href="lineage.php?id=<?php echo $lineage['lineage_id']; ?>"><?php echo e($lineage['lineage_name']); ?></a>
            <?php endforeach; ?>
        </div>
    </section>

    <div class="section-heading">
        <h2>Family recipes</h2>
        <a class="button" href="admin/index.php">Family chef area</a>
    </div>

    <?php if ($recipes): ?>
        <div class="recipe-grid row g-4">
            <?php foreach ($recipes as $recipe): ?>
                <article class="recipe-card col-md-6 col-lg-4">
                    <?php if ($recipe['image_path']): ?>
                        <img class="recipe-card-image" src="<?php echo e(application_url($recipe['image_path'])); ?>" alt="<?php echo e($recipe['image_caption'] ?: 'Photo of ' . $recipe['title']); ?>">
                        <p class="gemini-image-credit">Image created with Google Gemini.</p>
                    <?php endif; ?>
                    <p class="eyebrow"><?php echo e($recipe['lineage_name'] ?? 'Uncategorized'); ?></p>
                    <h3><?php echo e($recipe['title']); ?></h3>
                    <p>Introduced by <?php echo e($recipe['introduced_by']); ?></p>
                    <a href="<?php echo e(recipe_permalink((int) $recipe['recipe_id'], $recipe['slug'])); ?>">Read recipe</a>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="notice">No recipes have been added yet. Use the Family Chef Area to add the first one.</p>
    <?php endif; ?>
</main>

<?php require 'footer.php'; ?>
