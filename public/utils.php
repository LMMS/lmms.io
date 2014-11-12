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

/*
 * Helper function to replace first occurance
 */
function str_replace_first($find, $replace, $subject) {
	return implode($replace, explode($find, $subject, 2));
}

function make_reflection($image_path, $thumbnail_path = NULL, $blackorwhite = 'black', $class = '') {
	// If no thumbnail is supplied, try 'th_' . $image_path
	if (!$thumbnail_path) {
		$pieces = explode('/', $image_path);
		$pieces[count($pieces) -1] = 'th_' . $pieces[count($pieces) -1];
		$thumbnail_path = implode('/', $pieces);
	}
	echo '<div id="reflect-' . $blackorwhite . '" class="image-block ' . $class . '">';
	echo '<a href="' . $image_path . '" data-lightbox="image" data-title="&lt;a target=&quot;_blank&quot; href=&quot;' . $image_path . '&quot; title=&quot;Download full size image&quot; download&gt;&lt;i class=&quot;fa fa-arrow-circle-down &quot;/&gt;&lt;/a&gt;" ><img src="' . $thumbnail_path . '" alt="" /></a>';
	echo '<div class="reflection visible-lg">';
	echo '	<img src="' . $thumbnail_path . '" alt="" />';
	echo '	<div class="overlay"></div>';
	echo '</div>';
	echo '</div>';
}

// Prints an FontAwesome icon
function icon($icon)
{
	return "<span class='fa $icon'></span>";
}

// Prints an FontAwesome icon stack with two icons and a tooltip
function icon_stack($icon1, $icon2, $parentclass, $tooltip = '')
{
	return "<span class='fa-stack $parentclass' data-toggle='tooltip' data-placement='bottom' title='$tooltip'>" .
		icon($icon1) . icon($icon2) . "</span>";
}

// Prints an icon stack with the lower one being a double sized circle
// and the upper one being inversed
function circle_stack($icon, $class = '', $tooltip = '')
{
	return icon_stack('fa-circle fa-stack-2x', "$icon fa-stack-1x fa-inverse", $class, $tooltip);
}

?>
