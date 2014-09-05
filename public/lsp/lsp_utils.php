<?php

/*
 * Prevent PHP warnings by first checking to see if a variable is set, or returns null
 */
function GET($var) {
	if (!GET_EMPTY($var)) {
		return $_GET[$var];
	}
	return null;
}

function SESSION($var = 'REMOTE_USER') {
	if (!SESSION_EMPTY($var)) {
		return $_SESSION[$var];
	}
	return null;
}

function POST($var) {
	if (!POST_EMPTY($var)) {
		return $_POST[$var];
	}
	return null;
}

/*
 * Check for non-blank values
 */
function GET_EMPTY($var) {
	return isset($_GET[$var]) ? trim($_GET[$var]) == '' : true;
}

function POST_EMPTY($var) {
	return isset($_POST[$var]) ? trim($_POST[$var]) == '' : true;
}

function SESSION_EMPTY($var = 'REMOTE_USER') {
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

?>
