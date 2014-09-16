<br><div class="lsp-table">
<?php
require_once('utils.php');
global $LSP_URL;
/*
 * Adds the specified user to the database
 */
function try_add_user($login , $pass, $pass2, $realname, $is_admin) {
	$message = '';
	$class = 'warning';
	$return_val = false;
	if ($pass != $pass2) { 
		$message = "Password mismatch.";
		$class = 'warning';
	} else if($realname == '' || $pass == '' || $pass2 == '' || $login == '') {
		$message = "Please fill out all fields.";
		$class = 'warning';
	} else if(get_user_id($login) > 0) {
		$message = "The user <strong>$login</strong> already exists.";
		$class = 'danger';
	} else {
		add_user($login, $realname, $pass, $is_admin);
		$message = "<strong>$login</strong> has been successfully created";
		$class = 'success';
		$return_val = true;
	}
	echo '<div class="col-md-9 center"><div class="alert alert-' . $class . '" role="alert">';
    echo "$message";
    echo '</div></div>';	
	return $return_val;
}

// FIXME TODO: This could be a security problem
$isadmin = (POST_EMPTY("isadmin")) ? true : false;

/*
 * Create the HTML form used for registration
 */
if ((POST("adduser") != "Register") || (!try_add_user(POST("login"), POST("password"), POST("password2"), POST("realname"), $isadmin))) {
	echo '<div class="col-md-9"><div class="panel panel-default"><div class="panel-heading"><h3 class="panel-title">Register</h3></div>';
	echo '<div class="panel-body">';
	$form = new form($LSP_URL . "?action=register");
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
		echo '<div class="checkbox"><label><input type="checkbox" name="isadmin" />Is administrator</label></div>';
	}
	echo '<input type="submit" class="btn btn-default" name="adduser" value="Register" />&nbsp;';
	echo '<a href="' . $LSP_URL . '" class="btn btn-warning">Cancel</a>';
	$form->close();
	echo "<a href=\"javascript:loginFocus();\"><span class=\"fa  fa-chevron-circle-left\"></span>&nbsp;Already registered?  Login here.</a>"; 
	echo '</div></div></div>';
}
?>
</div>

