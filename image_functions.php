<?php

const IMAGE_MAX_BYTES = 5_000_000;
const IMAGE_MAX_DIMENSION = 1200;
// Validate the uploaded file before attempting to resize or store it.
function image_upload_errors(?array $file): array
{
    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return [];
    }

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return ['The image could not be uploaded. Please choose another file.'];
    }

    if (!is_uploaded_file($file['tmp_name'] ?? '')) {
        return ['The image upload could not be verified.'];
    }

    if (($file['size'] ?? 0) < 1 || $file['size'] > IMAGE_MAX_BYTES) {
        return ['The image must be smaller than 5 MB.'];
    }

    $image_info = @getimagesize($file['tmp_name']);
    $allowed_types = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP];

    if (!$image_info || !in_array($image_info[2], $allowed_types, true)) {
        return ['Please upload a real JPEG, PNG, or WebP image.'];
    }

    if ($image_info[2] === IMAGETYPE_WEBP && !function_exists('imagecreatefromwebp')) {
        return ['WebP images are not supported by this PHP installation. Please use JPEG or PNG.'];
    }

    return [];
}

function image_caption_input(array $source): string
{
    return trim(strip_tags($source['image_caption'] ?? ''));
}

function image_caption_errors(string $caption): array
{
    return strlen($caption) > 255 ? ['The image caption must be 255 characters or fewer.'] : [];
}

function store_resized_image(array $file, int $recipe_id): array
{
    $image_info = getimagesize($file['tmp_name']);
    $source_width = $image_info[0];
    $source_height = $image_info[1];
    $image_type = $image_info[2];
    $scale = min(1, IMAGE_MAX_DIMENSION / max($source_width, $source_height));
    $target_width = max(1, (int) round($source_width * $scale));
    $target_height = max(1, (int) round($source_height * $scale));

    $source = match ($image_type) {
        IMAGETYPE_JPEG => imagecreatefromjpeg($file['tmp_name']),
        IMAGETYPE_PNG => imagecreatefrompng($file['tmp_name']),
        IMAGETYPE_WEBP => imagecreatefromwebp($file['tmp_name']),
    };

    if (!$source) {
        throw new RuntimeException('The image could not be read.');
    }

    $target = imagecreatetruecolor($target_width, $target_height);

    if (in_array($image_type, [IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
        imagealphablending($target, false);
        imagesavealpha($target, true);
        $transparent = imagecolorallocatealpha($target, 0, 0, 0, 127);
        imagefilledrectangle($target, 0, 0, $target_width, $target_height, $transparent);
    }

    imagecopyresampled($target, $source, 0, 0, 0, 0, $target_width, $target_height, $source_width, $source_height);

    $extension = match ($image_type) {
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG => 'png',
        IMAGETYPE_WEBP => 'webp',
    };
    $filename = 'recipe-' . $recipe_id . '-' . bin2hex(random_bytes(8)) . '.' . $extension;
    $upload_directory = __DIR__ . '/uploads';

    if (!is_dir($upload_directory) && !mkdir($upload_directory, 0755, true) && !is_dir($upload_directory)) {
        imagedestroy($source);
        imagedestroy($target);
        throw new RuntimeException('The uploads folder could not be created.');
    }

    $destination = $upload_directory . '/' . $filename;
    $saved = match ($image_type) {
        IMAGETYPE_JPEG => imagejpeg($target, $destination, 85),
        IMAGETYPE_PNG => imagepng($target, $destination, 6),
        IMAGETYPE_WEBP => imagewebp($target, $destination, 85),
    };

    imagedestroy($source);
    imagedestroy($target);

    if (!$saved) {
        throw new RuntimeException('The resized image could not be saved.');
    }

    return [
        'image_name' => $filename,
        'image_path' => 'uploads/' . $filename,
    ];
}

// Delete only the stored file associated with the selected recipe image.
function delete_stored_image(array $image): void
{
    $filename = basename($image['image_path'] ?? '');

    if ($filename === '') {
        return;
    }

    $file_path = __DIR__ . '/uploads/' . $filename;

    if (is_file($file_path) && !unlink($file_path)) {
        throw new RuntimeException('The image file could not be removed.');
    }
}
