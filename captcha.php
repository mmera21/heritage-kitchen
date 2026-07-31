<?php
session_start();

if (!function_exists('imagecreatetruecolor')) {
    http_response_code(500);
    exit('GD image support is required for the CAPTCHA.');
}

$characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
$captcha_code = '';

for ($index = 0; $index < 6; $index++) {
    $captcha_code .= $characters[random_int(0, strlen($characters) - 1)];
}

$_SESSION['captcha_code'] = $captcha_code;

$image = imagecreatetruecolor(160, 50);
$background = imagecolorallocate($image, 255, 250, 243);
$ink = imagecolorallocate($image, 47, 41, 36);
$accent = imagecolorallocate($image, 156, 79, 45);

imagefill($image, 0, 0, $background);

for ($index = 0; $index < 8; $index++) {
    imageline(
        $image,
        random_int(0, 160),
        random_int(0, 50),
        random_int(0, 160),
        random_int(0, 50),
        $accent
    );
}

imagestring($image, 5, 27, 17, $captcha_code, $ink);

header('Content-Type: image/png');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
imagepng($image);
imagedestroy($image);
