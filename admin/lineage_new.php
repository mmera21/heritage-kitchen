<?php
require 'auth.php';
require '../connect.php';
require '../functions.php';

$admin_page = true;
$lineage = ['lineage_name' => '', 'region' => '', 'description' => ''];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lineage = lineage_input($_POST);
    $errors = lineage_errors($lineage);

    if (!$errors) {
        try {
            $statement = $db->prepare(
                'INSERT INTO lineages (lineage_name, region, description)
                 VALUES (:lineage_name, :region, :description)'
            );
            $statement->execute($lineage);
            header('Location: lineage_index.php');
            exit;
        } catch (PDOException $error) {
            $errors[] = 'This lineage name already exists.';
        }
    }
}

$submit_label = 'Save lineage';
require '../header.php';
?>

<main class="container admin-main">
    <p class="eyebrow">Category management</p>
    <h1>Add a family lineage</h1>
    <?php require 'lineage_form.php'; ?>
</main>

<?php require '../footer.php'; ?>
