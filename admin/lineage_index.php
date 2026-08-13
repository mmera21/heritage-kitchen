<?php
require 'auth.php';
require '../connect.php';
require '../functions.php';

$admin_page = true;
$statement = $db->query(
    'SELECT lineages.lineage_id, lineages.lineage_name, lineages.region,
            COUNT(recipes.recipe_id) AS recipe_count
    FROM lineages
    LEFT JOIN recipes ON recipes.lineage_id = lineages.lineage_id
    GROUP BY lineages.lineage_id, lineages.lineage_name, lineages.region
    ORDER BY lineages.lineage_name ASC'
);
$lineages = $statement->fetchAll();

require '../header.php';
?>

<main class="container admin-main">
    <p class="eyebrow">Category management</p>
    <h1>Manage family lineages</h1>
    <p>Lineages are the categories used to organize recipes in The Heritage Kitchen.</p>

    <div class="admin-actions">
        <a class="button" href="lineage_new.php">Add a lineage</a>
        <a class="button secondary" href="index.php">Manage recipes</a>
    </div>

    <?php if ($lineages): ?>
        <div class="panel">
            <table>
                <thead>
                    <tr>
                        <th>Lineage</th>
                        <th>Region</th>
                        <th>Recipes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lineages as $lineage): ?>
                        <tr>
                            <td><?php echo e($lineage['lineage_name']); ?></td>
                            <td><?php echo e($lineage['region']); ?></td>
                            <td><?php echo $lineage['recipe_count']; ?></td>
                            <td>
                                <a href="lineage_edit.php?id=<?php echo $lineage['lineage_id']; ?>">Edit</a>
                                <a href="lineage_delete.php?id=<?php echo $lineage['lineage_id']; ?>">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</main>

<?php require '../footer.php'; ?>
