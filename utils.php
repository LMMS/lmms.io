<?php

function str_contains($haystack, $needle, $ignorecase = FALSE) {
	if ($ignorecase) {
		return strpos(strtolower($haystack), strtolower($needle)) !== FALSE;
	}
	return strpos($haystack, $needle) !== FALSE;
}

function str_startswith($haystack, $needle, $ignorecase = FALSE) {
	if ($ignorecase) {
		return $needle === "" || strpos(strtolower($haystack), strtolower($needle)) === 0;
	}
    return $needle === "" || strpos($haystack, $needle) === 0;
}

function str_endswith($haystack, $needle, $ignorecase = FALSE) {
	if ($ignorecase) {
		return $needle === "" || substr(strtolower($haystack), -strlen($needle)) === strtolower($needle);
	}
    return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
}

function make_reflection($image_path, $thumbnail_path = NULL, $blackorwhite = 'black', $class = '') {
	// If no thumbnail is supplied, try 'th_' . $image_path
	if (!$thumbnail_path) {
		$pieces = explode('/', $image_path);
		$pieces[count($pieces) -1] = 'th_' . $pieces[count($pieces) -1];
		$thumbnail_path = implode('/', $pieces);
	}
	echo '<div id="reflect-' . $blackorwhite . '" class="image-block ' . $class . '">';
	echo '<a target="_blank" href="' . $image_path . '" data-lightbox="image"><img src="' . $thumbnail_path . '" alt="" /></a>';
	echo '<div class="reflection visible-lg">';
	echo '	<img src="' . $thumbnail_path . '" alt="" />';
	echo '	<div class="overlay"></div>';
	echo '</div>';
	echo '</div>';
}

?>
