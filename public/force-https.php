<?php

// Redirects all visitors to HTTPS for live site only
$SECURE_HOST='lmms.io';

/*
 * Redirect HTTP PORT 80 traffic to use HTTPS
 */
if (is_secure_host() && $_SERVER['HTTPS'] !== 'on' && $_SERVER['SERVER_PORT'] == '80') {
	header("Location: https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
    die();
}

/*
 * Returns the server protocol in the server request  "http://", "https://", etc.
 */
function get_protocol() {
	// Set some sane defaults
	$protocol = is_secure_host() ? 'https://' : 'http://';
	
	// Override with the value requested from the server
	if (isset($_SERVER['SERVER_PROTOCOL']) && strpos($_SERVER['SERVER_PROTOCOL'], '/') !== false) {
		$protocol = strtolower(explode('/', $_SERVER['SERVER_PROTOCOL'])[0] . '://');
	}
	
	return $protocol;
}

/*
 * Returns true if the host-name is explicitly defined as requiring HTTPS
 */
function is_secure_host() {
	global $SECURE_HOST;
	return $_SERVER['HTTP_HOST'] == $SECURE_HOST;
}

?>