<?php

/*
 * Prevent PHP warnings by first checking to see if a variable is set, or returns null
 */
function GET($var, $default_val = null) {
	if (!GET_EMPTY($var)) {
		return $_GET[$var];
	}
	return $default_val;
}

function SESSION($var = 'remote_user', $default_val = null) {
	if (!SESSION_EMPTY($var)) {
		return $_SESSION[$var];
	}
	return $default_val;
}

function POST($var, $default_val = null) {
	if (!POST_EMPTY($var)) {
		return $_POST[$var];
	}
	return $default_val;
}

/*
 * Check for non-blank $_GET[...] values
 */
function GET_EMPTY($var) {
	return isset($_GET[$var]) ? trim($_GET[$var]) == '' : true;
}

/*
 * Check for non-blank $_PST[...] values
 */
function POST_EMPTY($var) {
	return isset($_POST[$var]) ? trim($_POST[$var]) == '' : true;
}

/*
 * Check for non-blank $_SESSION[...] values
 */
function SESSION_EMPTY($var = 'remote_user') {
	return isset($_SESSION[$var]) ? trim($_SESSION[$var]) == '' : true;
}

/*
 * Cleanse out a prefixed name from an html element, used for the star rating
 * i.e:
 *   [input]
 *		'Rate <a href="#">click here to rate</a>'
 *   [output]
 *      'Rate'
 */
function remove_after_lt($text) {
	$text = str_replace('&nbsp;', '', $text);
	return trim(explode("<",$text)[0]);
}

function newline_to_br($text) {
	return str_replace("\n", "<br>", $text);
}

/*
 * Set's the $_GET[...] variable to the $_POST[...] variable of the same name
 * allowing code to rely on $_GET values exclusively for certain variables
 * falling back to the default variable $default if the $_POST[...] is empty
 */
function set_get_post($param, $default = null) {
	if (!POST_EMPTY($param)) {
		$_GET[$param] = POST($param);
	} else {
		if (isset($default)) {
			$_GET[$param] = $default;
		}
	}
}


/*
 * Creates a sort-by tool-bar at the top of the file listing
 * i.e. DATE, DOWNLOADS, RATING
 */
function list_sort_options($query_prefix = '') {
	global $LSP_URL;
	$sortings = array(
		'date' => '<span class="fa fa-calendar"></span>&nbsp;DATE',
		'downloads' => '<span class="fa fa-download"></span>&nbsp;DOWNLOADS',
		'rating' => '<span class="fa fa-star"></span>&nbsp;RATING' );
	
	// Get the active sort, or use 'date' if none is defined
	if (GET_EMPTY('sort')) {
		$_GET['sort'] = 'date';
	}

	// List all sort options
	echo '<ul class="nav nav-pills lsp-sort">';
	foreach ($sortings as $s => $v) {
		echo '<li class="' . (GET('sort') == $s ? 'active' : '') . '">';
		echo '<a href="' . $LSP_URL . '?' . $query_prefix . rebuild_url_query('sort', $s) . '">' . $v . '</a></li>';
	}
	echo '</ul>';
}

/*
 * Rebuilds the current URL into a new URL to be used in a link
 * replacing the specified key with a new key.
 */
function rebuild_url_query($key, $value) {
	if (GET_EMPTY($key)) {
		return '';
	}
	$old = GET($key);
	$_GET[$key] = $value;
	$new_query = array();
	foreach($_GET as $k => $v) {
		array_push($new_query, $k . "=" . $v);
	}
	$_GET[$key] = $old;
	return implode("&amp;", $new_query);
}

function file_show_query_string() {
	return 'action=show&file=' . GET("file");
}

?>
