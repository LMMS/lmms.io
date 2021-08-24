<?php
require_once('utils.php');
header("Content-type: image/png");
session_start();
$md5 = md5(session_id() . rand(0,25));
$default_string = substr($md5, strlen("$md5") - 4, strlen("$md5"));
$string = GET('text', get_random_string() . $default_string);
$font  = 6;
$width  = imagefontwidth($font) * strlen($string);
$height = imagefontheight($font);
$image = imagecreatetruecolor($width, $height);
$white = imagecolorallocate($image, 255, 255, 255);
$black = imagecolorallocate($image, 0, 0, 0);
imagefill($image, 0, 0,$white);
imagestring ($image, $font, 0, 0, $string, $black);
imagepng ($image);
imagedestroy($image);

function get_random_string($length = 2) {
    $characters = '0123456789abcdef';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

?>