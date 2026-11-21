<?php
require_once('utils.php');
require_once('dbo.php');
require_once('polyfill.php');
global $LSP_URL;

function apply_settings($password, $password2, $realname) {
	global $LSP_URL;
	if( $password != $password2 ) { 
		display_error('Password mismatch');
		return false;
	} else if(strlen($realname) > 50) {
		display_error("Full name cannot be more than 50 characters long");
		return false;
	} else {
		change_user(SESSION(), $realname, $password);
		display_success('Account settings have been updated', array('<a href="">User Settings</a>', 'Success'), $LSP_URL . "?account=settings");
		return true;
	}
}

if ((POST('settings') != "apply" ) || (!apply_settings(POST('password'), POST('password2'), POST('realname')))) {
	echo twig_render('lsp/user_settings.twig', [
		'realname' => get_user_realname(SESSION())
	]);
}
?>
