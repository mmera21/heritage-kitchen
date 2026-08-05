<?php
require_once __DIR__ . '/functions.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$search_lineages = [];
$selected_lineage_id = $selected_lineage_id ?? null;
$application_base_url = rtrim(application_url(), '/');

if (isset($db) && $db instanceof PDO) {
    $search_lineages = $db->query('SELECT lineage_id, lineage_name FROM lineages ORDER BY lineage_name ASC')->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Heritage Kitchen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="<?php echo e($application_base_url); ?>/style.css">
</head>
<body>
    <header class="site-header">
        <div class="container nav">
            <a class="brand" href="<?php echo e($application_base_url); ?>/index.php">The Heritage Kitchen</a>
            <nav class="navbar navbar-expand-lg p-0" aria-label="Main navigation">
                <div class="navbar-nav">
                    <a class="nav-link px-2" href="<?php echo e($application_base_url); ?>/index.php">Recipes</a>
                    <a class="nav-link px-2" href="<?php echo e($application_base_url); ?>/admin/index.php">Admin</a>
                    <?php if (!empty($_SESSION['user_id'])): ?>
                        <a class="nav-link px-2" href="<?php echo e($application_base_url); ?>/admin/logout.php">Log out</a>
                    <?php else: ?>
                        <a class="nav-link px-2" href="<?php echo e($application_base_url); ?>/admin/login.php">Log in</a>
                        <a class="nav-link px-2" href="<?php echo e($application_base_url); ?>/register.php">Register</a>
                    <?php endif; ?>
                </div>
            </nav>

            <form class="site-search" method="get" action="<?php echo e($application_base_url); ?>/search.php" role="search">
                <label class="visually-hidden" for="site-search-input">Search recipes</label>
                <input id="site-search-input" name="q" type="search" value="<?php echo e($search_term ?? ''); ?>" placeholder="Search recipes">
                <label class="visually-hidden" for="lineage-filter">Family</label>
                <select id="lineage-filter" name="lineage_id">
                    <option value="">All families</option>
                    <?php foreach ($search_lineages as $search_lineage): ?>
                        <option value="<?php echo $search_lineage['lineage_id']; ?>"<?php echo $selected_lineage_id === (int) $search_lineage['lineage_id'] ? ' selected' : ''; ?>>
                            <?php echo e($search_lineage['lineage_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Search</button>
            </form>
        </div>
    </header>
