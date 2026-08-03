<?php
require 'auth.php';
require '../connect.php';
require '../functions.php';
require '../image_functions.php';

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

$image_statement = $db->prepare(
    'SELECT image_id, image_name, image_path, image_caption
     FROM images
     WHERE recipe_id = :recipe_id
     ORDER BY image_id ASC'
);
$image_statement->execute(['recipe_id' => $recipe_id]);
$images = $image_statement->fetchAll();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipe = recipe_input($_POST);
    $image_file = $_FILES['image_file'] ?? null;
    $has_image_upload = $image_file && ($image_file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
    $image_caption = image_caption_input($_POST);
    $delete_image_ids = [];

    foreach ((array) ($_POST['delete_image_ids'] ?? []) as $image_id) {
        $valid_image_id = valid_id($image_id);

        if ($valid_image_id !== null) {
            $delete_image_ids[] = $valid_image_id;
        }
    }

    $images_to_delete = array_filter(
        $images,
        fn (array $image): bool => in_array((int) $image['image_id'], $delete_image_ids, true)
    );

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
            $db->beginTransaction();
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

            if ($has_image_upload) {
                $stored_image = store_resized_image($image_file, $recipe_id);
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

            foreach ($images_to_delete as $image) {
                delete_stored_image($image);
                $statement = $db->prepare('DELETE FROM images WHERE image_id = :image_id AND recipe_id = :recipe_id');
                $statement->execute(['image_id' => $image['image_id'], 'recipe_id' => $recipe_id]);
            }

            $db->commit();
            header('Location: index.php?message=updated');
            exit;
        } catch (Throwable $error) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            if ($stored_image !== null) {
                delete_stored_image($stored_image);
            }

            $errors[] = $error instanceof PDOException
                ? 'The URL slug must be unique. Choose a different slug.'
                : 'The recipe could not be updated with that image. Please try again.';
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
