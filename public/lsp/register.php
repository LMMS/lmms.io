<?php
require_once('utils.php');
require_once('dbo.php');
require_once('polyfill.php');

global $LSP_URL;

/*
 * Adds the specified user to the database
 */
function try_add_user($login , $pass, $pass2, $realname, $session, $is_admin, $antispam) {
	$return_val = false;
	// Help prevent robot registrations
	if (!check_antispam($antispam)) {
		display_error("Invalid security code");
	} else if ($session != md5(session_id() . $_SERVER['REMOTE_ADDR'])) {
		display_error("Invalid session.");
	} else if ($pass != $pass2) {
		display_warning("Password mismatch");
	} else if($realname == '' || $pass == '' || $pass2 == '' || $login == '') {
		display_warning("Please fill out all fields");
	} else if(get_user_id($login) > 0) {
		display_error("The user <strong>$login</strong> already exists.");
	} else {
		add_user($login, $realname, $pass, $is_admin);
		$return_val = display_success("<strong>$login</strong> has been successfully created");
	}
	return $return_val;
}

/*
 * Just a quick hash verification.  Obscure, not secure.
 */
function check_antispam($antispam) {
	if (isset($antispam)) {
		for ($i = 0; $i < 25; $i++) {
			$md5 = md5(session_id() + $i);
			if (strpos($antispam, substr($md5, strlen("$md5") - 4, strlen("$md5"))) !== false) {
				return true;
			}
		}
	}
	return false;
}

$control = POST("control", false);

/*
 * Create the HTML form used for registration
 */
if ((POST("adduser") != "Register") || (!try_add_user(POST("login"), POST("password"), POST("password2"), POST("realname"), POST("session"), $control, POST("antispam")))) {
	echo twig_render('lsp/register.twig', [
		'session_id' => md5(session_id() . $_SERVER['REMOTE_ADDR'])
	]);
}
?>
