<?php
require 'auth.php';
require '../connect.php';
require '../functions.php';
require '../image_functions.php';

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
// MEOW : Validation security 4.1. 
// MEOW : Tambien validacion de imagenes y te tira un erorr por aqui 6.1. 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipe = recipe_input($_POST);
    $image_file = $_FILES['image_file'] ?? null;
    $has_image_upload = $image_file && ($image_file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
    $image_caption = image_caption_input($_POST);

    if ($recipe['slug'] === '' && $recipe['title'] !== '') {
        $recipe['slug'] = recipe_slug($recipe['title']);
    }

    $errors = recipe_errors($recipe, $_POST);

    if ($has_image_upload) {
        $errors = array_merge($errors, image_upload_errors($image_file), image_caption_errors($image_caption));
    }

    if (!$errors) {
        $stored_image = null;

        try {
            if ($has_image_upload) {
                $stored_image = store_resized_image($image_file, 0);
            }

            $db->beginTransaction();
            $statement = $db->prepare(
                'INSERT INTO recipes (lineage_id, title, slug, introduced_by, ingredients, instructions, prep_time_minutes, servings)
                VALUES (:lineage_id, :title, :slug, :introduced_by, :ingredients, :instructions, :prep_time_minutes, :servings)'
            );
            $statement->execute($recipe);
            $recipe_id = (int) $db->lastInsertId();
// MEOW : 6.1. Todas las weas de imagenes hace has aqui y el sttament de abajo. 
            if ($has_image_upload) {
                $statement = $db->prepare(
                    'INSERT INTO images (recipe_id, image_name, image_path, image_caption)
                    VALUES (:recipe_id, :image_name, :image_path, :image_caption)'
                );
                $statement->execute([
                    'recipe_id' => $recipe_id,
                    'image_name' => $stored_image['image_name'],
                    'image_path' => $stored_image['image_path'],
                    'image_caption' => $image_caption ?: null,
                ]);
            }

            $db->commit();
            header('Location: index.php?message=created');
            exit;
        } catch (Throwable $error) {
            if ($db->inTransaction()) {
                try {
                    $db->rollBack();
                } catch (PDOException $rollback_error) {
                }
            }

            if ($stored_image !== null) {
                try {
                    delete_stored_image($stored_image);
                } catch (Throwable $cleanup_error) {
                }
            }

            $errors[] = $error instanceof PDOException
                ? 'The URL slug must be unique. Choose a different slug.'
                : 'The recipe could not be saved with that image. Please try again.';
        }
    }
}

$lineages = $db->query('SELECT lineage_id, lineage_name FROM lineages ORDER BY lineage_name')->fetchAll();
$images = [];
$submit_label = 'Save recipe';

require '../header.php';
?>

<main class="container admin-main">
    <p class="eyebrow">Recipe CRUD</p>
    <h1>Add a recipe</h1>
    <?php require 'recipe_form.php'; ?>
</main>

<?php require '../footer.php'; ?>
