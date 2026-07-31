<?php
require 'auth.php';
require '../connect.php';
require '../functions.php';

$admin_page = true;
$allowed_sorts = [
    'title' => ['sql' => 'recipes.title', 'label' => 'Recipe title'],
    'lineage' => ['sql' => 'lineages.lineage_name', 'label' => 'Family lineage'],
    'created_at' => ['sql' => 'recipes.created_at', 'label' => 'Date created'],
    'updated_at' => ['sql' => 'recipes.updated_at', 'label' => 'Date updated'],
];
$sort = $_GET['sort'] ?? 'created_at';
$direction = strtolower($_GET['direction'] ?? 'desc');

if (!array_key_exists($sort, $allowed_sorts)) {
    $sort = 'created_at';
}

if (!in_array($direction, ['asc', 'desc'], true)) {
    $direction = 'desc';
}

$direction_label = $direction === 'asc' ? 'Ascending (A-Z / oldest first)' : 'Descending (Z-A / newest first)';

$statement = $db->query(
    'SELECT recipes.recipe_id, recipes.title, recipes.slug, recipes.created_at,
            recipes.updated_at, lineages.lineage_name
     FROM recipes
     LEFT JOIN lineages ON recipes.lineage_id = lineages.lineage_id
     ORDER BY ' . $allowed_sorts[$sort]['sql'] . ' ' . strtoupper($direction)
);
$recipes = $statement->fetchAll();

require '../header.php';
?>

<main class="container admin-main">
    <p class="eyebrow">Administrative area</p>
    <h1>Manage recipes</h1>
    <?php if (!empty($_SESSION['login_message'])): ?>
        <p class="notice"><?php echo e($_SESSION['login_message']); unset($_SESSION['login_message']); ?></p>
    <?php endif; ?>
    <p>Current sort: <strong><?php echo e($allowed_sorts[$sort]['label']); ?> — <?php echo e($direction_label); ?></strong></p>

    <div class="admin-actions">
        <a class="button" href="recipe_new.php">Add a recipe</a>
        <a class="button secondary" href="lineage_index.php">Manage family lineages</a>
        <a class="button secondary" href="comments.php">Moderate comments</a>
        <a class="button secondary" href="users.php">Manage users</a>
        <a class="button secondary" href="../index.php">View public site</a>
        <a class="button secondary" href="logout.php">Log out</a>
    </div>

    <form class="sort-form panel" method="get">
        <div>
            <label for="sort">Sort recipes by</label>
            <select id="sort" name="sort">
                <?php foreach ($allowed_sorts as $sort_key => $sort_option): ?>
                    <option value="<?php echo e($sort_key); ?>"<?php echo $sort === $sort_key ? ' selected' : ''; ?>>
                        <?php echo e($sort_option['label']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="direction">Order</label>
            <select id="direction" name="direction">
                <option value="asc"<?php echo $direction === 'asc' ? ' selected' : ''; ?>>Ascending</option>
                <option value="desc"<?php echo $direction === 'desc' ? ' selected' : ''; ?>>Descending</option>
            </select>
        </div>
        <div>
            <button class="button" type="submit">Apply sort</button>
        </div>
    </form>

    <?php if ($recipes): ?>
        <div class="panel">
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Lineage</th>
                        <th>Created</th>
                        <th>Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recipes as $recipe): ?>
                        <tr>
                            <td><?php echo e($recipe['title']); ?></td>
                            <td><?php echo e($recipe['lineage_name'] ?? 'Uncategorized'); ?></td>
                            <td><?php echo e($recipe['created_at']); ?></td>
                            <td><?php echo e($recipe['updated_at']); ?></td>
                            <td>
                                <a href="recipe_edit.php?id=<?php echo $recipe['recipe_id']; ?>">Edit</a>
                                <a href="recipe_delete.php?id=<?php echo $recipe['recipe_id']; ?>">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="notice">There are no recipes yet. Add one to start your cookbook.</p>
    <?php endif; ?>
</main>

<?php require '../footer.php'; ?>
