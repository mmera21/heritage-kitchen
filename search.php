<?php
require 'connect.php';
require 'functions.php';

$search_term = trim(strip_tags($_GET['q'] ?? ''));
$selected_lineage_id = valid_id($_GET['lineage_id'] ?? null);
$recipes = [];
$total_results = 0;
$total_pages = 0;
$current_page = valid_id($_GET['page'] ?? null) ?? 1;

// Change this number during testing to demonstrate more or fewer results per page.
$results_per_page = 5;

if ($search_term !== '') {
    $where_clause = ' WHERE CONCAT_WS(" ", recipes.title, recipes.introduced_by, recipes.ingredients, recipes.instructions) LIKE :keyword';
    $parameters = ['keyword' => '%' . $search_term . '%'];

    if ($selected_lineage_id !== null) {
        $where_clause .= ' AND recipes.lineage_id = :lineage_id';
        $parameters['lineage_id'] = $selected_lineage_id;
    }

    $count_statement = $db->prepare('SELECT COUNT(*) FROM recipes' . $where_clause);
    $count_statement->execute($parameters);
    $total_results = (int) $count_statement->fetchColumn();
    $total_pages = (int) ceil($total_results / $results_per_page);

    if ($total_pages > 0 && $current_page > $total_pages) {
        $current_page = $total_pages;
    }

    $offset = ($current_page - 1) * $results_per_page;
    $sql =
        'SELECT recipes.recipe_id, recipes.title, recipes.slug, recipes.introduced_by,
                lineages.lineage_name
         FROM recipes
         LEFT JOIN lineages ON recipes.lineage_id = lineages.lineage_id' .
        $where_clause .
        ' ORDER BY recipes.title ASC LIMIT ' . $results_per_page . ' OFFSET ' . $offset;
    $statement = $db->prepare($sql);
    $statement->execute($parameters);
    $recipes = $statement->fetchAll();
}

require 'header.php';
?>

<main class="container recipe-page">
    <p class="eyebrow">Recipe search</p>
    <h1>Search results</h1>

    <?php if ($search_term === ''): ?>
        <p class="notice">Enter a word or phrase in the search box to find recipes.</p>
    <?php elseif ($recipes): ?>
        <p>
            Found <?php echo $total_results; ?> result(s) for <strong><?php echo e($search_term); ?></strong>
            <?php if ($selected_lineage_id !== null): ?>
                in the selected family.
            <?php else: ?>
                in all families.
            <?php endif; ?>
        </p>
        <div class="recipe-grid row g-4">
            <?php foreach ($recipes as $recipe): ?>
                <article class="recipe-card col-md-6 col-lg-4">
                    <p class="eyebrow"><?php echo e($recipe['lineage_name'] ?? 'Uncategorized'); ?></p>
                    <h2><?php echo e($recipe['title']); ?></h2>
                    <p>Introduced by <?php echo e($recipe['introduced_by']); ?></p>
                    <a href="recipe.php?id=<?php echo $recipe['recipe_id']; ?>&slug=<?php echo e($recipe['slug']); ?>">Read recipe</a>
                </article>
            <?php endforeach; ?>
        </div>

        <?php if ($total_pages > 1): ?>
            <nav class="pagination" aria-label="Search result pages">
                <?php if ($current_page > 1): ?>
                    <a href="search.php?<?php echo e(http_build_query(['q' => $search_term, 'lineage_id' => $selected_lineage_id, 'page' => $current_page - 1])); ?>">Previous</a>
                <?php endif; ?>

                <?php for ($page = 1; $page <= $total_pages; $page++): ?>
                    <?php if ($page === $current_page): ?>
                        <span aria-current="page"><?php echo $page; ?></span>
                    <?php else: ?>
                        <a href="search.php?<?php echo e(http_build_query(['q' => $search_term, 'lineage_id' => $selected_lineage_id, 'page' => $page])); ?>"><?php echo $page; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($current_page < $total_pages): ?>
                    <a href="search.php?<?php echo e(http_build_query(['q' => $search_term, 'lineage_id' => $selected_lineage_id, 'page' => $current_page + 1])); ?>">Next</a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    <?php else: ?>
        <p class="notice">No recipes matched <strong><?php echo e($search_term); ?></strong>.</p>
    <?php endif; ?>
</main>

<?php require 'footer.php'; ?>
