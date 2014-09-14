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
function list_sort_options($additional_html = '') {
	global $LSP_URL;
	$sortings = array(
		'date' => '<span class="fa fa-calendar"></span>&nbsp;DATE',
		'downloads' => '<span class="fa fa-download"></span>&nbsp;DOWNLOADS',
		'rating' => '<span class="fa fa-star"></span>&nbsp;RATING' //,
		// TODO:  Add comment sorting support
		//'comments' => '<span class="fa fa-comment"></span>&nbsp;COMMENTS'
	);
	
	// Get the active sort, or use 'date' if none is defined
	if (GET_EMPTY('sort')) {
		$_GET['sort'] = 'date';
	}

	// List all sort options
	echo '<ul class="nav nav-pills lsp-sort">';
	foreach ($sortings as $s => $v) {
		echo '<li class="' . (GET('sort') == $s ? 'active' : '') . '">';
		echo '<a href="' . $LSP_URL . '?' . rebuild_url_query('sort', $s) . '">' . $v . '</a></li>';
	}
	
	if ($additional_html != '') {
		echo '<li style="margin-left: 2em; margin-top:-1em;">' . $additional_html . '</li>';
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
		if (!array_key_exists($k, $new_query)) {
			array_push($new_query, $k . "=" . $v);
		}
	}
	$_GET[$key] = $old;
	return implode("&amp;", $new_query);
}

function file_show_query_string() {
	return 'action=show&file=' . GET("file");
}

/*
 * For show-file page, creates a <li> for Comment/Edit/Delete/Rate tool-bar
 * with tool-tip text "Login to Comment", etc.
 */
function create_toolbar_item($text, $href = '#', $font_awesome = '', $enabled = true) {
	$href = $enabled ? htmlentities($href) : '#';
	$tooltip = $enabled ? '' : 'Login to ' . strtolower(sanitize(remove_after_lt($text)));
	$font_awesome = $font_awesome == '' ? '' : 'fa ' . $font_awesome;
	echo '<li class="' . ($enabled ? '' : 'disabled') . '"><a class="pull-left" href="' . $href . '" title="' . $tooltip . '"><span class="' . $font_awesome . '"></span>&nbsp;' . $text . '</a></li>';
}

/*
 * For show-file page, creates 5 stars which may be click-able to set file rating
 */
function get_stars($fid = -1, $href = '#', $font_awesome = '', $enabled = true) {
	$ret_val = 'Rate:' . ($enabled ? '' : '&nbsp; &nbsp;');
	$urating =  SESSION_EMPTY() ? get_user_rating($fid, SESSION()) : -1;
	$font_awesome = ($font_awesome = '' ? '' : 'fa ' . $font_awesome);
	$title = $enabled ? '' : 'Login to rate';
	$href = $enabled ? htmlentities($href) : '#';
	for( $i = 1; $i < 6; ++$i ) {
		$ret_val .= ($enabled ? '<a href="' . ($href == '#' ? '#' : $href . $i) . '" class="clearfix pull-left lsp-ratelink" ' : '<span class="lsp-ratelink" ');
		$ret_val .=  'title="' . $title . '">';	
		$ret_val .= '<span class="' . ($urating == $i ? 'text-primary ' : '') . $font_awesome . '"></span>';
		$ret_val .= ($enabled ? '</a>' : '</span>');
	}
	return $ret_val;
}

/*
 * Creates a bread-crumb style title for the table content
 * i.e All Content > Projects > Tutorials
 */
function create_title($array) {
	global $LSP_URL;
	if (!is_array($array)) {
		$array = array($array);
	} else {
		$one_element = one_element($array);
		if ($one_element) {
			$array = array($one_element);
		}
	}
	
	$title = "<a href=\"$LSP_URL\">All Content</a>";
	foreach ($array as $element) {
		if (isset($element) && trim($element) != '' && trim($element) != '""' && trim($element) != "()") {
			$title .= '&nbsp;&nbsp;<span class="fa fa-caret-right lsp-caret-right"></span>&nbsp;&nbsp;';
			$title .= trim($element);
		}
	}
	echo '<h3 class="lsp-title">' . $title . '</h3>';
}

/*
 * Returns the single element of an array
 * where only one element is not empty (null, or trimmed to blank)
 * or false if this does not apply
 */
function one_element($array) {
	if (is_array($array)) {
		$count = 0;
		foreach ($array as $element) {
			if (isset($element) && trim($element) != '' && trim($element) != '""') {
				$count++;
			}
		}
		if ($count == 1) {
			foreach ($array as $element) {
				if (isset($element) && trim($element) != '' && trim($element) != '""') {
					return $element;
				}
			}
		}
	}
	return false;
}


/*
 * Return a very basic "pagination" area for paging between search results
 * TODO:  Add <, 1, 2, ... 44, 45, 46, > support rather than listing all 46 pages.
 */
function get_pagination($count) {
	global $PAGE_SIZE, $LSP_URL;
	$user=!GET_EMPTY('user') ? '&amp;user=' . GET('user') : '';
	$category=!GET_EMPTY('category') ? '&amp;category=' . GET('category') : '';
	$subcategory=!GET_EMPTY('subcategory') ? '&amp;subcategory=' . GET('subcategory') : '';
	$browse = strlen("$user$category$subcategory") ? "?action=browse$user$category$subcategory" : '';
	$search = !GET_EMPTY('search') ? '?search=' . GET('search') : '';
	$sort=!GET_EMPTY('sort') ? '&amp;sort=' . GET('sort') : '';
	$pagination = '';
	$pagination .= '<div class="lsp-pagination center"><ul class="pagination pagination-sm">';
	$pages = $count / $PAGE_SIZE;
	$page = GET('page', 0);
	if ($pages > 1) {
		for($j=0; $j < $count / $PAGE_SIZE; ++$j ) {
			$class = $j==$page ? 'active' : '';	
			$pagination .= '<li class="' . $class . '"><a href=' . $LSP_URL . "$search$browse&amp;page=$j$sort>" . ($j+1) . '</a></li>';
		}
	}
	$pagination .= '</ul></div>';
	return $pagination;
}


?>
