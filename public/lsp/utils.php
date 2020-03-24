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
function sanitize($string, $tolower = false, $replacewith='') {
	$return_val = preg_replace('/[^A-Za-z0-9_]+/', $replacewith, $string);
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

	echo twig_render('lsp/message.twig', [
		'titles' => isset($title_array) ? $title_array : $title,
		'title' => $title,
		'icon' => $icon,
		'severity' => $severity,
		'redirect' => (isset($redirect) ? htmlentities($redirect) : ''),
		'message' => $message,
		'counter' => $counter
	]);

	return true;
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
		if (password_match(POST('password'), htmlspecialchars_decode(POST('login')))) {
			$_SESSION["remote_user"] = htmlspecialchars_decode(POST('login'));
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
function get_file_url(string $file_id = null): string {
	global $LSP_URL;
	$url = $LSP_URL . '?action=show&file=' . (isset($file_id) ? $file_id : GET('file'));
	$name = get_file_name((isset($file_id) ? $file_id : GET('file')));
	return '<a href="' . $url . '">' . $name . '</a>';
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
	// sanitize the link
	$url = htmlspecialchars($url, ENT_QUOTES);
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
	// sanitize the link
	$url = htmlspecialchars($url, ENT_QUOTES);
	// if the url has no protocol, use a protocol relative link
	if(strpos($url, 'http') === false) {
		$html = '<img src="//' . $url . '" class="lsp-image" alt="' .$url . '">';
	} else {
		$html = '<img src="' . $url . '" class="lsp-image" alt="' .$url . '">';
	}
	return $html;
}


function redirect(string $url, int $statusCode = 303) {
   header('Location: ' . $url, true, $statusCode);
   die();
}
?>
