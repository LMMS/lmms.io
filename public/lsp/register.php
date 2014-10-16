<?php
require_once('utils.php');
require_once('dbo.php');
require_once('xhtml.php');

global $LSP_URL;

/*
 * Adds the specified user to the database
 */
function try_add_user($login , $pass, $pass2, $realname, $is_admin) {
	$return_val = false;
	if ($pass != $pass2) {
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

$control = POST("control", false);

/*
 * Create the HTML form used for registration
 */
if ((POST("adduser") != "Register") || (!try_add_user(POST("login"), POST("password"), POST("password2"), POST("realname"), $control))) {
	echo '<div class="col-md-9">';
	$form = new form($LSP_URL . '?action=register', 'Register', 'fa-list-alt');
	echo '<div class="form-group">';
	echo '<label for="realname">Real name</label>';
	echo '<input type="text" name="realname" class="form-control textin" maxlength="50" placeholder="real name" />';
	echo '<label for="login">Username</label>';
	echo '<input type="text" name="login" class="form-control textin" maxlength="10" placeholder="username" />';
	echo '<label for="password">Password</label>';
	echo '<input type="password" name="password" class="form-control textin" maxlength="15" placeholder="password" />';
	echo '<label for="password2">Confirm password</label>';
	echo '<input type="password" name="password2" class="form-control textin" maxlength="15" placeholder="confirm password" />';
	echo '</div>';
	//print_r ($_SERVER);
	if (is_admin(get_user_id(SESSION()))) {
		echo '<div class="checkbox"><label><input type="checkbox" name="control" />Is administrator</label></div>';
	}
	echo '<button type="submit" class="btn btn-primary" name="adduser" value="Register"><span class="fa fa-check"></span>&nbsp;Register</button>&nbsp;';
	echo '<a href="' . $LSP_URL . '" class="btn btn-warning"><span class="fa fa-close"></span>&nbsp;Cancel</a>';
	$form->close();
	echo "<a href=\"javascript:loginFocus();\"><span class=\"fa  fa-chevron-circle-left\"></span>&nbsp;Already registered?  Login here.</a>"; 
	echo '</div>';
}
?>

