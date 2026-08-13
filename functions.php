<?php
// MEOW : Function e is for removed the weird text and validate strings, Before i u
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function posted_trim(string $field): string
{
    $value = filter_input(INPUT_POST, $field, FILTER_UNSAFE_RAW);

    return is_string($value) ? trim($value) : '';
}
// MEOW : 5.4. this one es tambien para el sglug, validamos en el textp con int y que no sea null o menor a uno.
function valid_id(mixed $value): ?int
{
    $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    return $id === false ? null : $id;
}
// MEOW : Validation security 4.1.
// MEOW : Validation security 4.3. the strip_ tags is for clean. 
function recipe_input(array $source): array
{
    return [
        'lineage_id' => valid_id($source['lineage_id'] ?? null),
        'title' => trim(strip_tags($source['title'] ?? '')),
        'slug' => recipe_slug(trim(strip_tags($source['slug'] ?? ''))),
        'introduced_by' => trim(strip_tags($source['introduced_by'] ?? '')) ?: 'N/A',
        'ingredients' => trim(strip_tags($source['ingredients'] ?? '')),
        'instructions' => sanitize_rich_text($source['instructions'] ?? ''),
        'prep_time_minutes' => valid_id($source['prep_time_minutes'] ?? null),
        'servings' => valid_id($source['servings'] ?? null),
    ];
}
// MEOW : Validation security 4.3. Rich-text instructions use a controlled HTML allowlist so safe formatting remains, when the unsafe text is removed deleted blabla. 
function sanitize_rich_text(string $html): string
{
    $allowed_tags = ['p', 'br', 'strong', 'em', 'u', 's', 'ul', 'ol', 'li', 'h2', 'h3', 'blockquote'];
    $remove_entirely = ['script', 'style', 'iframe', 'object', 'embed'];
    $document = new DOMDocument('1.0', 'UTF-8');
    libxml_use_internal_errors(true);
    $document->loadHTML('<?xml encoding="UTF-8"><div id="rich-root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();

    $root = $document->getElementById('rich-root');

    if (!$root) {
        return '';
    }

    $clean_node = function (DOMNode $node) use (&$clean_node, $allowed_tags, $remove_entirely): void {
        for ($index = $node->childNodes->length - 1; $index >= 0; $index--) {
            $child = $node->childNodes->item($index);

            if ($child->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }

            $tag = strtolower($child->nodeName);

            if (in_array($tag, $remove_entirely, true)) {
                $node->removeChild($child);
                continue;
            }

            $clean_node($child);

            if (!in_array($tag, $allowed_tags, true)) {
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);
                continue;
            }

            while ($child->attributes->length > 0) {
                $child->removeAttributeNode($child->attributes->item(0));
            }
        }
    };

    $clean_node($root);
    $clean_html = '';

    foreach ($root->childNodes as $child) {
        $clean_html .= $document->saveHTML($child);
    }

    return trim($clean_html);
}

function rich_text_for_editor(string $value): string
{
    if ($value === '') {
        return '';
    }

    if (strip_tags($value) === $value) {
        $paragraphs = preg_split('/\R{2,}/', $value) ?: [];
        $html = '';

        foreach ($paragraphs as $paragraph) {
            $html .= '<p>' . nl2br(e($paragraph)) . '</p>';
        }

        return $html;
    }

    return sanitize_rich_text($value);
}

function recipe_errors(array $recipe, array $source = []): array
{
    $errors = [];

    if ($recipe['title'] === '') {
        $errors[] = 'Recipe title is required.';
    } elseif (strlen($recipe['title']) > 150) {
        $errors[] = 'Recipe title must be 150 characters or fewer.';
    }

    if ($recipe['slug'] === '') {
        $errors[] = 'A URL slug is required.';
    } elseif (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $recipe['slug'])) {
        $errors[] = 'The URL slug can only use lowercase letters, numbers, and hyphens.';
    } elseif (strlen($recipe['slug']) > 180) {
        $errors[] = 'The URL slug must be 180 characters or fewer.';
    }

    if ($recipe['ingredients'] === '') {
        $errors[] = 'Ingredients are required.';
    }

    if (strlen($recipe['introduced_by']) > 150) {
        $errors[] = 'Introduced by must be 150 characters or fewer.';
    }

    if ($recipe['instructions'] === '') {
        $errors[] = 'Instructions are required.';
    }

    $raw_lineage_id = trim((string) ($source['lineage_id'] ?? ''));
    $raw_prep_time = trim((string) ($source['prep_time_minutes'] ?? ''));
    $raw_servings = trim((string) ($source['servings'] ?? ''));

    if ($raw_lineage_id !== '' && valid_id($raw_lineage_id) === null) {
        $errors[] = 'Please choose a valid family lineage.';
    }

    if ($raw_prep_time !== '' && valid_id($raw_prep_time) === null) {
        $errors[] = 'Preparation time must be a positive whole number.';
    }

    if ($raw_servings !== '' && valid_id($raw_servings) === null) {
        $errors[] = 'Servings must be a positive whole number.';
    }

    return $errors;
}
// MEOW : 5.4. lowercase slug spaces and punctuation, also The recipe query requires both the ID and slug to match.
function recipe_slug(string $title): string
{
    $slug = strtolower(trim($title));
    $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug);

    return trim($slug ?? '', '-');
}
// MEOW : 5.4. as well for the slug, show maryam. 
function recipe_permalink(int $recipe_id, string $slug): string
{
    return application_url($recipe_id . '/' . rawurlencode($slug) . '/');
}

function application_url(string $path = ''): string
{
    $application_path = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');

    if (preg_match('#/admin$#', $application_path)) {
        $application_path = rtrim(str_replace('\\', '/', dirname($application_path)), '/');
    }

    $application_path = str_replace(' ', '%20', $application_path);

    return $application_path . '/' . ltrim($path, '/');
}

function lineage_input(array $source): array
{
    return [
        'lineage_name' => trim(strip_tags($source['lineage_name'] ?? '')),
        'region' => trim(strip_tags($source['region'] ?? '')),
        'description' => trim(strip_tags($source['description'] ?? '')),
    ];
}

function lineage_errors(array $lineage): array
{
    if ($lineage['lineage_name'] === '') {
        return ['Family lineage name is required.'];
    }

    if (strlen($lineage['lineage_name']) > 100) {
        return ['Family lineage name must be 100 characters or fewer.'];
    }

    if (strlen($lineage['region']) > 150) {
        return ['Region must be 150 characters or fewer.'];
    }

    return [];
}

function comment_input(array $source): array
{
    return [
        'commenter_name' => trim(strip_tags($source['commenter_name'] ?? '')),
        'comment_text' => trim(strip_tags($source['comment_text'] ?? '')),
    ];
}

function comment_errors(array $comment): array
{
    $errors = [];

    if ($comment['commenter_name'] === '') {
        $errors[] = 'Your name is required.';
    }

    if ($comment['comment_text'] === '') {
        $errors[] = 'Your comment is required.';
    }

    if (strlen($comment['commenter_name']) > 100) {
        $errors[] = 'Your name must be 100 characters or fewer.';
    }

    return $errors;
}

function user_input(array $source): array
{
    $role = $source['role'] ?? 'member';

    if (!in_array($role, ['admin', 'member'], true)) {
        $role = 'member';
    }

    return [
        'username' => trim(strip_tags($source['username'] ?? '')),
        'email' => strtolower(trim($source['email'] ?? '')),
        'role' => $role,
    ];
}

function user_errors(array $user, string $password, string $password_confirm, bool $password_required): array
{
    $errors = [];

    if (!preg_match('/^[A-Za-z0-9_.-]{3,100}$/', $user['username'])) {
        $errors[] = 'Username must be 3-100 characters and use only letters, numbers, periods, hyphens, or underscores.';
    }

    if (!filter_var($user['email'], FILTER_VALIDATE_EMAIL) || strlen($user['email']) > 150) {
        $errors[] = 'Please enter a valid email address.';
    }

    if ($password_required || $password !== '' || $password_confirm !== '') {
        if (strlen($password) < 8) {
            $errors[] = 'Password must contain at least 8 characters.';
        }

        if ($password !== $password_confirm) {
            $errors[] = 'The two passwords do not match.';
        }
    }

    return $errors;
}
