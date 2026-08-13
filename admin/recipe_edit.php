<?php
// Carga la verificacion para que el usuario este autenticado. 
require 'auth.php';
//Carga la conexion de la base de datos 
require '../connect.php';
// Validacion, sanitizacion, slugs and hash, all the extra functions. 
require '../functions.php';
//este es como la carpeta de funciones pero dirigidas mas a las fotografias. 
require '../image_functions.php';

// Panel administracion.
$admin_page = true;
// Entero positivo y no null.
$recipe_id = valid_id($_GET['id'] ?? null);

// Si la id no funciona o no esta la url se detiene toda la wea.
if ($recipe_id === null) {
    exit('Invalid recipe ID.');
}
// Hace la consulta con mysqlen busca de la receta. 
$statement = $db->prepare('SELECT * FROM recipes WHERE recipe_id = :recipe_id');
$statement->execute(['recipe_id' => $recipe_id]);
$recipe = $statement->fetch();
// Error, si no funciona, si no encuentra ni una receta con esas descripcion o id, detiene de nuevo la wea y muetsra el mensaje. 
if (!$recipe) {
    exit('Recipe not found.');
}

// Consulta con mysql pero para obtener las imagenes correctas. 
$image_statement = $db->prepare(
    'SELECT image_id, image_name, image_path, image_caption
    FROM images
    WHERE recipe_id = :recipe_id
    ORDER BY image_id ASC'
);
$image_statement->execute(['recipe_id' => $recipe_id]);
$images = $image_statement->fetchAll();

$errors = [];
// MEOW : Validation security 4.1.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipe = recipe_input($_POST);
    $image_file = $_FILES['image_file'] ?? null;
    $has_image_upload = $image_file && ($image_file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
    $image_caption = image_caption_input($_POST);
    $delete_image_ids = [];
    // MEOW : 6.2. Borramos foto y valida con cada id. 

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
            // Image resizing can be slow. Do it before opening the database
            if ($has_image_upload) {
                $stored_image = store_resized_image($image_file, $recipe_id);
            }

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
// MEOW : con sql borramos el path de la imagen. 
            foreach ($images_to_delete as $image) {
                $statement = $db->prepare('DELETE FROM images WHERE image_id = :image_id AND recipe_id = :recipe_id');
                $statement->execute(['image_id' => $image['image_id'], 'recipe_id' => $recipe_id]);
            }

            $db->commit();

            // Remove files with one condition. 
            foreach ($images_to_delete as $image) {
                try {
                    delete_stored_image($image);
                } catch (Throwable $file_error) {
                }
            }

            header('Location: index.php?message=updated');
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
