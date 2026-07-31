<?php
require 'auth.php';
require '../connect.php';
require '../functions.php';

$admin_page = true;
$recipe_id = valid_id($_GET['id'] ?? null);

if ($recipe_id === null) {
    exit('Invalid recipe ID.');
}

$statement = $db->prepare('SELECT * FROM recipes WHERE recipe_id = :recipe_id');
$statement->execute(['recipe_id' => $recipe_id]);
$recipe = $statement->fetch();

if (!$recipe) {
    exit('Recipe not found.');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipe = recipe_input($_POST);

    if ($recipe['slug'] === '' && $recipe['title'] !== '') {
        $recipe['slug'] = recipe_slug($recipe['title']);
    }

    $errors = recipe_errors($recipe, $_POST);

    if (!$errors) {
        try {
            $recipe['recipe_id'] = $recipe_id;
            $statement = $db->prepare(
                'UPDATE recipes
                 SET lineage_id = :lineage_id, title = :title, slug = :slug,
                     introduced_by = :introduced_by, ingredients = :ingredients,
                     instructions = :instructions, prep_time_minutes = :prep_time_minutes,
                     servings = :servings
                 WHERE recipe_id = :recipe_id'
            );
            $statement->execute($recipe);
            header('Location: index.php?message=updated');
            exit;
        } catch (PDOException $error) {
            $errors[] = 'The URL slug must be unique. Choose a different slug.';
        }
    }
}

$lineages = $db->query('SELECT lineage_id, lineage_name FROM lineages ORDER BY lineage_name')->fetchAll();
$submit_label = 'Update recipe';

require '../header.php';
?>

<main class="container admin-main">
    <p class="eyebrow">Recipe CRUD</p>
    <h1>Edit recipe</h1>
    <?php require 'recipe_form.php'; ?>
</main>

<?php require '../footer.php'; ?>
