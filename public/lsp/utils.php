<?php
require_once('../utils.php');
require_once('../feed/json_common.php');

/*
 * Prevent PHP warnings by first checking to see if a variable is set, or returns null
 */
function GET($var, $default_val = null) {
	if (!GET_EMPTY($var)) {
		return htmlentities($_GET[$var]);
	}
	return htmlentities($default_val);
}

function SESSION($var = 'remote_user', $default_val = null) {
	if (!SESSION_EMPTY($var)) {
		return htmlentities($_SESSION[$var]);
	}
	return htmlentities($default_val);
}

function POST($var, $default_val = null) {
	if (!POST_EMPTY($var)) {
		return htmlentities($_POST[$var]);
	}
	return htmlentities($default_val);
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

function newline_to_br($text, $decode_special_chars = false) {
	if ($decode_special_chars) {
		return htmlspecialchars_decode(str_replace("\n", "<br>", $text), ENT_COMPAT);
	}
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
			$_GET[$param] = htmlentities($default);
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
		'date' => '<span class="fas fa-calendar"></span>&nbsp;DATE',
		'downloads' => '<span class="fas fa-download"></span>&nbsp;DOWNLOADS / AGE',
		'rating' => '<span class="fas fa-star"></span>&nbsp;RATING' //,
		// TODO:  Add comment sorting support
		//'comments' => '<span class="fas fa-comment"></span>&nbsp;COMMENTS'
	);
	
	// Catch singular/plural
	switch (sanitize(GET('sort'), true)) {
		case 'download' : $_GET['sort'] = 'download'; break;
		case 'ratings' : $_GET['sort'] = 'rating'; break;
		//case 'comment' : $_GET['sort'] = 'comments'; break;
	}
	
	// Get the active sort, or use 'date' if none is defined
	if (array_key_exists(sanitize(GET('sort'), true), $sortings)) {
		$_GET['sort'] = sanitize(GET('sort'), true);
	}
	
	// Get the order (asc/desc) 'desc' if none is defined
	if (!GET_EMPTY('order')) {
		$order = sanitize(GET('order'), true);
	} else {
		$order = null;
	}
	
	if (GET_EMPTY('sort') || !array_key_exists(GET('sort'), $sortings)) {
		$_GET['sort'] = 'date';
	}

	// List all sort options
	echo '<ul class="nav nav-pills lsp-sort">';
	foreach ($sortings as $s => $v) {
		if (GET('sort') == $s) {
			switch ($order) {
				case 'asc':
					unset($_GET['order']);
					$v .= '&nbsp;(<span class="fas fa-long-arrow-alt-up"></span>)';
					break;
				case 'desc':
					// move down
				default:
					$_GET['order'] = 'asc';
					$v .= '&nbsp;(<span class="fas fa-long-arrow-alt-down"></span>)';
			}
		} else {
			// Don't allow order to be defined for other buttons
			unset($_GET['order']);
		}
		echo '<li id="sort-' . strtolower(sanitize($s)) . '" class="' . (GET('sort') == $s ? 'active' : '') . '">';
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
	$_GET[$key] = htmlentities($value);
	$new_query = array();
	foreach($_GET as $k => $v) {
		if (!array_key_exists($k, $new_query)) {
			array_push($new_query, $k . "=" . $v);
		}
	}
	$_GET[$key] = htmlentities($old);
	return implode("&amp;", $new_query);
}

function file_show_query_string() {
	return 'action=show&file=' . GET("file");
}


/*
 * Basic column/field-name sanitization by removing non alpha-numeric characters from the input string
 */
function sanitize($string, $tolower = false) {
	$return_val = preg_replace('/[^A-Za-z0-9_]+/', '', $string);
	return $tolower ? $return_val : strtolower($return_val);
}


/*
 * For show-file page, creates a <li> for Comment/Edit/Delete/Rate tool-bar
 * with tool-tip text "Login to Comment", etc.
 */
function create_toolbar_item($text, $href = '#', $font_awesome = '', $enabled = true, $rate_self = false) {
	if ($rate_self) {
		$enabled = false;
		$tooltip = 'Cannot ' . strtolower(sanitize(remove_after_lt($text))) . ' own file';
	} else {
		$tooltip = $enabled ? '' : 'Login to ' . strtolower(sanitize(remove_after_lt($text)));
	}
	$href = $enabled ? htmlentities($href) : '#';
	
	$font_awesome = $font_awesome == '' ? '' : 'fas ' . $font_awesome;
	echo '<li class="' . ($enabled ? '' : 'disabled') . '"><a class="pull-left" href="' . $href . '" title="' . $tooltip . '"><span class="' . $font_awesome . '"></span>&nbsp;' . $text . '</a></li>';
}

/*
 * For show-file page, creates 5 stars which may be click-able to set file rating
 */
function get_stars($fid = -1, $href = '#', $enabled = true) {
	$ret_val = 'Rate:' . ($enabled ? '' : '&nbsp; &nbsp;');
	$userRating =  SESSION_EMPTY() ? -1 : get_user_rating($fid, SESSION());
	$class = 'far fa-star lsp-star';
	$title = $enabled ? '' : 'Login to rate';
	$href = $enabled ? htmlentities($href) : '#';
	$ret_val .= '<div class="lsp-starrating clearfix pull-left">';
	for( $i = 5; $i > 0; --$i ) {
		if ($enabled) {
			$ret_val .= '<a href="' . ($href == '#' ? '#' : $href . $i) . '"><i class="' . ($userRating >= $i ? "fas lsp-star fa-star" : $class) . '" title="' . $title . '"></i></a>';
		} else {
			$ret_val .= '<a href="" class="' . $class . ' disabled"></a>';
		}
	}
	$ret_val .= '</div>';
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
			$title .= '&nbsp;&nbsp;<span class="fas fa-caret-right lsp-caret-right"></span>&nbsp;&nbsp;';
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
 * Displays a formatted error message in a small dialogue to the right of the sidebar
 */
function display_message($message, $severity = 'danger', $title = 'Error', $title_array = null, $redirect = null, $counter = 5) {
	
	switch ($severity) {
		case 'info': $icon = 'fa-info-circle'; break;
		case 'success': $icon = 'fa-check-circle'; break;
		case 'warning': // move down
		default: $icon = 'fa-exclamation-circle';
	}
	
	$icon = '<span class="fas ' . $icon . '"></span>&nbsp;';

	echo '<div class="col-md-9">';
	create_title(isset($title_array) ? $title_array : $title);
	echo '<div data-redirect="' . (isset($redirect) ? htmlentities($redirect) : '') . '" ' .
		'class="alert alert-' . $severity . ' text-center"><strong>' . $icon .
		($title == '' ? '' : "$title:") . '</strong> ' . $message . '</div>';
	if (isset($redirect)) {
		echo '<p class="text-center">You will automatically be redirected in <strong>' . 
			'<span class="redirect-counter">' . $counter . '</span> seconds</strong></p>';
	}
	echo '</div>';
}

function display_error($message, $title_array = null, $redirect = null, $counter = 15) {
	return display_message($message, 'danger', 'Error', $title_array, $redirect, $counter);
}

function display_warning($message, $title_array = null, $redirect = null, $counter = 15) {
	return display_message($message, 'warning', 'Warning', $title_array, $redirect, $counter);
}

function display_info($message, $title_array = null, $redirect = null, $counter = 5) {
	return display_message($message, 'info', '', $title_array, $redirect, $counter);
}

function display_success($message, $title_array = null, $redirect = null, $counter = 5) {
	return display_message($message, 'success', '', $title_array, $redirect, $counter);
}

/*
 * Return a very basic "pagination" area for paging between search results
 * TODO:  Add <, 1, 2, ... 44, 45, 46, > support rather than listing all 46 pages.
 */
function get_pagination($count) {
	global $PAGE_SIZE, $LSP_URL;
	$commentsearch=GET('commentsearch', false) ? '&amp;commentsearch=true' : '';
	$user=!GET_EMPTY('user') ? '&amp;user=' . rawurlencode(GET('user')) : '';
	$category=!GET_EMPTY('category') ? '&amp;category=' . rawurlencode(GET('category')) : '';
	$subcategory=!GET_EMPTY('subcategory') ? '&amp;subcategory=' . rawurlencode(GET('subcategory')) : '';
	$browse = strlen("$user$category$subcategory") ? "?action=browse$user$category$subcategory" : '';
	$search = !GET_EMPTY('search') ? '?search=' . rawurlencode(GET('search')) : '';
	$sort=!GET_EMPTY('sort') ? '&amp;sort=' . rawurlencode(GET('sort')) : '';
	$pagination = '';
	$pagination .= '<div class="lsp-pagination center"><ul class="pagination pagination-sm">';
	$pages = $count / $PAGE_SIZE;
	$page = GET('page', 0);
	if ($pages > 1) {
		for($j=0; $j < $count / $PAGE_SIZE; ++$j ) {
			$class = $j==$page ? 'active' : '';	
			$pagination .= '<li class="' . $class . '"><a href=' . $LSP_URL . "$search$browse&amp;page=$j$sort$commentsearch>" . ($j+1) . '</a></li>';
		}
	}
	$pagination .= '</ul></div>';
	return $pagination;
}

/*
 * Clears session data and performs a user logout
 */
function logout() {
	unset ($_SESSION["remote_user"]);
	session_destroy();
	$_GET["action"] = GET('oldaction');
	if (GET('action') != "browse" && GET('action') != "show" &&	GET('action') != "" ) {
		$_GET["action"] = "show";
	}
}

/*
 * Verifies the POST is a password match and logs in the user
 */
function login() {
	if (SESSION_EMPTY() && GET('action') == 'login') {
		if (password_match(POST('password'), POST('login'))) {
			$_SESSION["remote_user"] = POST('login');
			$_GET["action"] = POST('oldaction');
			set_get_post('category');
			set_get_post('subcategory');
			return true;
		}
	}
	return false;
}

/*
 * Attempts to build a file hyperlink from the GET('file') value
 */
function get_file_url($file_id = null) {
	global $LSP_URL;
	$url = $LSP_URL . '?action=show&file=' . (isset($file_id) ? $file_id : GET('file'));
	$name = get_file_name((isset($file_id) ? $file_id : GET('file')));
	return '<a href="' . $url . '">' . $name . '</a>';
}

/*
 * Scrapes a message for a link to a service such as YouTube or SoundCloud and
 * embeds a player. Also turns links into appropriate hyperlinks.
 */
function parse_links($message, $width = "100%", $height = 160) {
	// Global pattern to find distinctive links
	$pattern = "%[a-zA-Z\/\/:\.\"\=]*(                                 # group 1, contains all the other groups
				(soundcloud.com\/[\w\*\-\?\&\%\=\.]+\/[\w\*\-\?\&\%\=\.]+)|  # group 2, match links like soundcloud.com/user/sound
				(youtube.com\/watch\?v\=)|                                   # group 3, match links like youtube-com/watch?v=videocode
				(youtu.be)|                                                  # group 4, match links like youtu.be/videocode
				(clyp.it(?!\/user)\/\S+)|                                    # group 5, match links like clyp.it/somehash, do not capture user links
				(https?:\/\/\S*\/\S*\.(jpe?g|png|gif)\b)|                    # groups 6 & 7, match links like http://mylink.domain/image.png
				(https?:\/\/)                                                # group 8, match links like http://example.com/page
				)+\S*%xi";
	preg_match_all($pattern, $message, $matched, PREG_SET_ORDER);

	if ($matched) {
		for ($i = 0; $i < count($matched); $i++) {
			// Soundcloud links
			if ($matched[$i][2]) {
				if (strpos($matched[$i][0], 'src="') !== false) {
				// If there is old iframe code, skip
 				}
				// If the link is not a playlist, embed
				elseif (strpos($matched[$i][0], '/sets/') === false) {
					$sc = parse_url_ext($matched[$i][0]);
					$message = str_replace($matched[$i][0], '<sc>'. substr($sc["path"], 1) .'</sc>', $message);
					$message = soundcloud_iframe($message, $width, $height);
				}
				// If the link is a playlist, create a normal link
				else {
					$message = str_replace($matched[$i][0], create_link($matched[$i][0]) , $message);
				}
			}
			// Youtube links
			elseif ($matched[$i][3]) {
				$yt = parse_url_ext($matched[$i][0]);
				// Get a clean embed code, without garbage like "&feature=youtu.be"
				if (strpos($yt["query"], "&") === false) {
					$length = strlen($yt["query"]) - 2;
				 } else {
					$length = strpos($yt["query"], "&") - 2;
				}
				$message = str_replace($matched[$i][0], youtube_iframe(substr($yt["query"], 2, $length), $width, $height), $message);
			}
			// Youtu.be links
			elseif ($matched[$i][4]) {
				$ytbe = parse_url_ext($matched[$i][0]);
				$message = str_replace($matched[$i][0], youtube_iframe(substr($ytbe["path"], 1), $width, $height), $message);
			}
			// clyp.it links
			elseif ($matched[$i][5]) {
				$clyp = parse_url_ext($matched[$i][0]);
				$message = str_replace($matched[$i][0], clyp_iframe(substr($clyp["path"], 1), $width, $height), $message);
			}
			// Image links
			elseif ($matched[$i][6]) {
				$message = str_replace($matched[$i][0], create_img($matched[$i][0]) , $message);
			}
			// Regular links
			elseif ($matched[$i][8]) {
				$message = str_replace($matched[$i][0], create_link($matched[$i][0]) , $message);
			}
		}
	}

	return $message;
}

/*
 * Gets the content type of an file based on extension.
 * Necessary for interpreting a LSP image attachment as an image
 */
function get_content_type($file_path) {
	switch (parse_extension($file_path)) {
		case '.jpg' :
		case '.jpeg' : return 'image/jpeg';
		case '.gif' : return 'image/gif';
		case '.png' : return 'image/png';
		case '.svg' : return 'image/svg+xml';
		case '.tiff' : return 'image/tiff';
		default: return 'application/force-download';
	}
}

/*
 * Reads an '.mmp' or '.mmpz' project from disk and returns the simplexml object
 */
function read_project($file_id) {
	global $DATA_DIR;
	$extension = parse_extension(get_file_name($file_id));
	switch ($extension) {
		case '.mmp' : 
			// Treat as plain XML
			return simplexml_load_file($DATA_DIR . $file_id);
		case '.mmpz' :
			// Open binary file for reading
			$handle = fopen($DATA_DIR . $file_id, "rb");
			// Skip the first 4 bytes for compressed mmpz files
			fseek($handle, 4);
			$data = fread($handle, filesize($DATA_DIR . $file_id) - 4);
			return simplexml_load_string(zlib_decode($data));
		default:
			return null;
	}
}
/*
 * Extended url_parse function, it can parse links that appear
 * as relative, like "google.com/path?query" properly.
 */
function parse_url_ext ($url) {
	if(strpos($url, 'http') === false) {
		$parsed = parse_url('//' . $url);
	} else {
		$parsed = parse_url($url);
	}
	return $parsed;
}

/*
 *  Turns links such as http://example.com into the proper a tag
 */
function create_link ($url) {
	// if the url has no protocol, use a protocol relative link
	if(strpos($url, 'http') === false) {
		$html = '<a href="//' . $url . '" target=_blank >' . $url . '</a>';
	} else {
		$html = '<a href="' . $url . '" target=_blank >' .$url . '</a>';
	}
	return $html;
}
/*
 *  Turns links such as http://example.com/pic.jpg into the proper img tag
 */
function create_img ($url) {
	// if the url has no protocol, use a protocol relative link
	if(strpos($url, 'http') === false) {
		$html = '<img src="//' . $url . '" class="lsp-image" alt="' .$url . '">';
	} else {
		$html = '<img src="' . $url . '" class="lsp-image" alt="' .$url . '">';
	}
	return $html;
}

/*
 * Replaces soundcloud links such as <sc>soundcloud.com/artist1/track1</sc> with the proper iframe tags
 */
function soundcloud_iframe($message, $width, $height) {
	$parts = explode('<sc>', $message);
	$html = '';
	
	foreach ($parts as $part) {
		if (strpos($part, '</sc>') !== false) {
			$url_parts = explode('</sc>', $part);
			if (sizeof($url_parts) > 0) {
				$object = get_json_data('soundcloud', '../resolve', "?url=https://soundcloud.com/$url_parts[0]", '.');
				if (is_object($object) && property_exists($object, 'id')) {
					$html .= '<iframe width="' . $width.'" height="' . $height.'" scrolling="no" frameborder="no" ' . 
						'src="https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/' . 
						$object->id . '&amp;auto_play=false&amp;hide_related=true&amp;show_comments=false&amp;' . 
						'show_user=true&amp;show_reposts=false&amp;visual=false"></iframe>';
					$html .= $url_parts[1];
				} else {
					$html .= 'https://soundcloud.com/' . $url_parts[0] . ' <i class="fas fa-exclamation-circle" title="Link is no longer valid"></i>';
					$html .= $url_parts[1];
				}
			} else {
				continue;
			}
		} else {
			$html .= $part;
		}
	}

	return $html;
}

/*
 * Converts clyp.it links to the appropriate iframe
 */
function clyp_iframe($url, $width, $height) {
	$html = '<iframe width="' . $width . '" height="' . $height . '" 
		src="https://clyp.it/' . $url . '/widget" frameborder="0"></iframe>';

	return $html;
}

/**
 * Generate a random string, using a cryptographically secure 
 * pseudorandom number generator (random_int)
 * Taken from https://3v4l.org/IMJGF
 * 
 * For PHP 7, random_int is a PHP core function
 * For PHP 5.x, depends on https://github.com/paragonie/random_compat
 * 
 * @param int $length      How many characters do we want?
 * @param string $keyspace A string of all possible characters
 *                         to select from
 * @return string
 */
function random_str(
    int $length = 64,
    string $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
): string {
    if ($length < 1) {
        throw new \RangeException("Length must be a positive integer");
    }
    $pieces = [];
    $max = mb_strlen($keyspace, '8bit') - 1;
    for ($i = 0; $i < $length; ++$i) {
        $pieces []= $keyspace[random_int(0, $max)];
    }
    return implode('', $pieces);
}
?>
