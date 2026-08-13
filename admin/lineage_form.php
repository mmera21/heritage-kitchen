<?php if ($errors): ?>
<!-- Principalmente como dice su nombre esto es para las lineages, mostrarlas y que se deberia saber sobre ellas cuando registras una nueva.-->
    <div class="error-list">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" class="panel">
    <label for="lineage_name">Family lineage name *</label>
    <input id="lineage_name" name="lineage_name" type="text" value="<?php echo e($lineage['lineage_name']); ?>" required>

    <label for="region">Region</label>
    <input id="region" name="region" type="text" value="<?php echo e($lineage['region']); ?>">

    <label for="description">Family history / description</label>
    <textarea id="description" name="description"><?php echo e($lineage['description']); ?></textarea>

    <div class="admin-actions">
        <button class="button" type="submit"><?php echo e($submit_label); ?></button>
        <a class="button secondary" href="lineage_index.php">Cancel</a>
    </div>
</form>
