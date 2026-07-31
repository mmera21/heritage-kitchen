<?php
require 'auth.php';
require '../connect.php';
require '../functions.php';

$admin_page = true;
$errors = [];
$recipe = [
    'lineage_id' => null,
    'title' => '',
    'slug' => '',
    'introduced_by' => 'N/A',
    'ingredients' => '',
    'instructions' => '',
    'prep_time_minutes' => '',
    'servings' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipe = recipe_input($_POST);

    if ($recipe['slug'] === '' && $recipe['title'] !== '') {
        $recipe['slug'] = recipe_slug($recipe['title']);
    }

    $errors = recipe_errors($recipe, $_POST);

    if (!$errors) {
        try {
            $statement = $db->prepare(
                'INSERT INTO recipes (lineage_id, title, slug, introduced_by, ingredients, instructions, prep_time_minutes, servings)
                 VALUES (:lineage_id, :title, :slug, :introduced_by, :ingredients, :instructions, :prep_time_minutes, :servings)'
            );
            $statement->execute($recipe);
            header('Location: index.php?message=created');
            exit;
        } catch (PDOException $error) {
            $errors[] = 'The URL slug must be unique. Choose a different slug.';
        }
    }
}

$lineages = $db->query('SELECT lineage_id, lineage_name FROM lineages ORDER BY lineage_name')->fetchAll();
$submit_label = 'Save recipe';

require '../header.php';
?>

<main class="container admin-main">
    <p class="eyebrow">Recipe CRUD</p>
    <h1>Add a recipe</h1>
    <?php require 'recipe_form.php'; ?>
</main>

<?php require '../footer.php'; ?>
