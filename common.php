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

?>