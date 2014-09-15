<?php
// TODO: Make this a boostrap form
global $LSP_URL;
function apply_settings($pass, $pass2, $realname) {
	if( $pass != $pass2 ) { 
		echo "password mismatch !<br />\n";
		return false;
	} else {
		change_user(SESSION(), $realname, $pass);
		echo "<br /><span style=\"font-weight:bold; color:#0a0;\">Your account settings have been updated</span>";
		return true;
	}
}

if ((POST('settings') != "apply" ) || (!apply_settings(POST('password'), POST('password2'), POST('realname')))) {
	create_title('Account Settings');
	$form = new form( $LSP_URL."?account=settings" );
	echo "<table style=\"border:none;\" cellspacing=\"5\">";
	echo "<tr><td><b>Real name:</b></td><td><input type=\"text\" name=\"realname\" class=\"textin\" value=\"" . get_user_realname(SESSION()) . "\" /></td></tr>\n";
	echo "<tr><td><b>Password:</b></td><td><input type=\"password\" name=\"password\" /></td></tr>\n";
	echo "<tr><td><b>Confirm password:</b></td><td><input type=\"password\" name=\"password2\" /></td></tr>\n";
	echo "</table><p /><input type=\"submit\" name=\"settings\" value=\"apply\" /><p />\n";
	$form->close();
}

?>

