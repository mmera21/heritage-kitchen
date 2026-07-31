<?php
require 'auth.php';
require '../connect.php';
require '../functions.php';

$admin_page = true;
$lineage_id = valid_id($_GET['id'] ?? null);

if ($lineage_id === null) {
    exit('Invalid lineage ID.');
}

$statement = $db->prepare('SELECT * FROM lineages WHERE lineage_id = :lineage_id');
$statement->execute(['lineage_id' => $lineage_id]);
$lineage = $statement->fetch();

if (!$lineage) {
    exit('Lineage not found.');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lineage = lineage_input($_POST);
    $errors = lineage_errors($lineage);

    if (!$errors) {
        try {
            $lineage['lineage_id'] = $lineage_id;
            $statement = $db->prepare(
                'UPDATE lineages
                 SET lineage_name = :lineage_name, region = :region, description = :description
                 WHERE lineage_id = :lineage_id'
            );
            $statement->execute($lineage);
            header('Location: lineage_index.php');
            exit;
        } catch (PDOException $error) {
            $errors[] = 'This lineage name already exists.';
        }
    }
}

$submit_label = 'Update lineage';
require '../header.php';
?>

<main class="container admin-main">
    <p class="eyebrow">Category management</p>
    <h1>Edit family lineage</h1>
    <?php require 'lineage_form.php'; ?>
</main>

<?php require '../footer.php'; ?>
