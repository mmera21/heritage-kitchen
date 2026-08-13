<?php if ($errors): ?>
    <div class="error-list">
        <p><strong>Please correct the following:</strong></p>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
<!-- Referencia a los flacos de quilljs.com, los adoro muah muah-->
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">

<form method="post" class="panel" id="recipe-form" enctype="multipart/form-data">
    <label for="title">Recipe title *</label>
    <input id="title" name="title" type="text" value="<?php echo e($recipe['title']); ?>" required>

    <label for="slug">URL slug *</label>
    <input id="slug" name="slug" type="text" value="<?php echo e($recipe['slug']); ?>" required>
    <p>Use a descriptive slug. Spaces are automatically converted to hyphens, for example: <em>pastel-de-choclo</em>.</p>

    <label for="lineage_id">Family lineage</label>
    <select id="lineage_id" name="lineage_id">
        <option value="">Uncategorized</option>
        <?php foreach ($lineages as $lineage): ?>
            <option value="<?php echo $lineage['lineage_id']; ?>"<?php echo $recipe['lineage_id'] == $lineage['lineage_id'] ? ' selected' : ''; ?>>
                <?php echo e($lineage['lineage_name']); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="introduced_by">Introduced by</label>
    <input id="introduced_by" name="introduced_by" type="text" value="<?php echo e($recipe['introduced_by']); ?>">

    <div class="form-row">
        <div>
            <label for="prep_time_minutes">Preparation time (minutes)</label>
            <input id="prep_time_minutes" name="prep_time_minutes" type="number" min="1" value="<?php echo e((string) $recipe['prep_time_minutes']); ?>">
        </div>
        <div>
            <label for="servings">Servings</label>
            <input id="servings" name="servings" type="number" min="1" value="<?php echo e((string) $recipe['servings']); ?>">
        </div>
    </div>

    <label for="ingredients">Ingredients *</label>
    <textarea id="ingredients" name="ingredients" required><?php echo e($recipe['ingredients']); ?></textarea>

    <label for="instructions">Instructions *</label>
    <div id="instructions_editor" class="rich-editor"><?php echo rich_text_for_editor($recipe['instructions']); ?></div>
    <textarea id="instructions" name="instructions" hidden></textarea>
    <!-- MEOW : 6.1. Aqui te dice que solo podemos agregar JPEG, PNG o webp imagenes-->
    <fieldset class="image-upload-panel">
        <legend>Recipe image <span class="field-optional">(optional)</span></legend>
        <label for="image_file">Choose a JPEG, PNG, or WebP image</label>
        <input id="image_file" name="image_file" type="file" accept="image/jpeg,image/png,image/webp">
        <p class="form-help">Images must be smaller than 5 MB. PHP resizes large images to a maximum of 1200 pixels.</p>

        <label for="image_caption">Image caption <span class="field-optional">(optional)</span></label>
        <input id="image_caption" name="image_caption" type="text" maxlength="255" value="<?php echo e($image_caption ?? ''); ?>">

        <?php if (!empty($images)): ?>
            <div class="current-images">
                <p><strong>Current image<?php echo count($images) > 1 ? 's' : ''; ?></strong></p>
                <?php foreach ($images as $image): ?>
                    <figure class="current-image-card">
                        <img src="<?php echo e(application_url($image['image_path'])); ?>" alt="<?php echo e($image['image_caption'] ?: 'Current image for ' . $recipe['title']); ?>">
                        <?php if ($image['image_caption']): ?>
                            <figcaption><?php echo e($image['image_caption']); ?></figcaption>
                        <?php endif; ?>
                        <label>
                            <!-- MEOW : 6.2. Aqui borramos la imagen id y lo hacemos con nla checkbox--> 
                            <input name="delete_image_ids[]" type="checkbox" value="<?php echo $image['image_id']; ?>">
                            Remove this image
                        </label>
                    </figure>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </fieldset>

    <div class="admin-actions">
        <button class="button" type="submit"><?php echo e($submit_label); ?></button>
        <a class="button secondary" href="index.php">Cancel</a>
    </div>
</form>
<!-- Los weones de reddit me dijeron que para el rich text editor use quill, asi que lo hice, nada mas JAJAJAJ -->
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script>
    const instructionsEditor = new Quill('#instructions_editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ header: [2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['blockquote', 'clean']
            ]
        }
    });

    document.querySelector('#recipe-form').addEventListener('submit', () => {
        document.querySelector('#instructions').value = instructionsEditor.root.innerHTML;
    });
</script>
