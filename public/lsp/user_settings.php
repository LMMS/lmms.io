<?php
// TODO: Make this a boostrap form
global $LSP_URL;
function apply_settings($pass, $pass2, $realname) {
	if( $pass != $pass2 ) { 
		display_error('Password mismatch');
		return false;
	} else {
		change_user(SESSION(), $realname, $pass);
		display_success('Account settings have been updated');
		return true;
	}
}

if ((POST('settings') != "apply" ) || (!apply_settings(POST('password'), POST('password2'), POST('realname')))) {
	create_title('<a href="">User Settings</a>');
	?>
	<div class="col-md-9"><div class="panel panel-default">
	<div class="panel-heading"><h3 class="panel-title">
	<span class="fa fa-gear"></span>&nbsp;User Settings</h3></div>
	<?php
	echo '<div class="panel-body">';
	$form = new form( $LSP_URL."?account=settings" );
	echo '<div class="form-group">';
	echo "<label for=\"realname\">Real name:</label>";
	echo "<input type=\"text\" name=\"realname\" class=\"form-control\" value=\"" . get_user_realname(SESSION()) . "\" />";
	echo '</div>';
	echo '<div class="form-group">';
	echo "<label for=\"password\">Password:</label>";
	echo "<input type=\"password\" name=\"password\" class=\"form-control\"/>";
	echo '</div>';
	echo '<div class="form-group">';
	echo "<label for=\"password2\">Confirm Password:</label>";
	echo "<input type=\"password\" class=\"form-control\" name=\"password2\" />";
	echo '</div>';
	echo '<button class="btn btn-primary" type="submit" name="settings" value="apply"><span class="fa fa-check"></span>&nbsp;Apply</button>';

	$form->close();
	echo '</div></div></div>';
}

?>

