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
	return is_secure_host() ? 'https://' : 'http://';
}

/*
 * Returns true if the host-name is explicitly defined as requiring HTTPS
 */
function is_secure_host() {
	global $SECURE_HOST;
	return $_SERVER['HTTP_HOST'] == $SECURE_HOST || preg_match("/\.$SECURE_HOST$/", $_SERVER['HTTP_HOST']);
}

?>
