<?php
require 'auth.php';
require '../connect.php';
require '../functions.php';

$admin_page = true;
$lineage_id = valid_id($_GET['id'] ?? $_POST['id'] ?? null);

if ($lineage_id === null) {
    exit('Invalid lineage ID.');
}

$statement = $db->prepare('SELECT lineage_id, lineage_name FROM lineages WHERE lineage_id = :lineage_id');
$statement->execute(['lineage_id' => $lineage_id]);
$lineage = $statement->fetch();

if (!$lineage) {
    exit('Lineage not found.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $statement = $db->prepare('DELETE FROM lineages WHERE lineage_id = :lineage_id');
    $statement->execute(['lineage_id' => $lineage_id]);
    header('Location: lineage_index.php');
    exit;
}

require '../header.php';
?>

<main class="container admin-main">
    <div class="panel">
        <p class="eyebrow">Confirm deletion</p>
        <h1>Delete <?php echo e($lineage['lineage_name']); ?>?</h1>
        <p>Recipes in this lineage will remain in the cookbook, but will become Uncategorized.</p>
        <form method="post" class="admin-actions">
            <input type="hidden" name="id" value="<?php echo $lineage['lineage_id']; ?>">
            <button class="button danger" type="submit">Delete lineage</button>
            <a class="button secondary" href="lineage_index.php">Cancel</a>
        </form>
    </div>
</main>

<?php require '../footer.php'; ?>
