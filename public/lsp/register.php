<?php
require_once('utils.php');
require_once('dbo.php');
require_once('xhtml.php');

global $LSP_URL;

/*
 * Adds the specified user to the database
 */
function try_add_user($login, $email, $pass, $pass2, $realname, $session, $is_admin, $antispam) {
	$return_val = false;
	// Help prevent robot registrations
	if (!check_antispam($antispam)) {
		display_error("Invalid security code");
	} else if ($session != md5(session_id() . $_SERVER['REMOTE_ADDR'])) {
		display_error("Invalid session.");
	} else if ($pass != $pass2) {
		display_warning("Password mismatch");
	} else if($realname == '' || $pass == '' || $pass2 == '' || $login == '' || $email == '') {
		display_warning("Please fill out all fields");
	} else if(get_user_id($login) > 0) {
		display_error("The user <strong>$login</strong> already exists.");
	} else {
		add_user($login, $email, $realname, $pass, $is_admin);
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
if ((POST("adduser") != "Register") || (!try_add_user(POST("login"), POST("email"), POST("password"), POST("password2"), POST("realname"), POST("session"), $control, POST("antispam")))) {
	echo '<div class="col-md-9">';
	$form = new form($LSP_URL . '?action=register', 'Register', 'fa-list-alt'); ?>
	<div class="form-group">
	<label for="realname">Real name</label>
	<input type="text" name="realname" class="form-control" maxlength="50" placeholder="real name" />
	</div><div class="form-group">
	<label for="login">Username</label>
	<input type="text" name="login" class="form-control" maxlength="16" placeholder="username" />
	</div><div class="form-group">
	<label for="realname">Email address</label>
	<input type="email" name="email" class="form-control" maxlength="64" placeholder="email address" />
	</div><div class="form-group">
	<label for="password">Password</label>
	<input type="password" name="password" class="form-control" maxlength="20" placeholder="password" />
	</div><div class="form-group">
	<label for="password2">Confirm password</label>
	<input type="password" name="password2" class="form-control" maxlength="20" placeholder="confirm password" />
	</div>
	<div class="form-group">
	<label for="password2">Security code</label>
	<img class="thumbnail" style="zoom: 200%" src="get_image.php" />
	<input type="text" name="antispam" class="form-control" maxlength="6" placeholder="type the security code above" />
	</div>
	<input type="hidden" name="session" value="<?php echo md5(session_id() . $_SERVER['REMOTE_ADDR']); ?>"/><?php
	/* // Admin user creation
	 * print_r ($_SERVER);
	 * if (is_admin(get_user_id(SESSION()))) {
	 *	echo '<div class="checkbox"><label><input type="checkbox" name="control" />Is administrator</label></div>';
	 * }
	 */
	?>
	<button type="submit" class="btn btn-primary" name="adduser" value="Register"><span class="fas fa-check"></span>&nbsp;Register</button>&nbsp;
	<a href="<?php echo $LSP_URL; ?>" class="btn btn-warning"><span class="fas fa-times"></span>&nbsp;Cancel</a>
	<?php $form->close(); ?>
	<a href="javascript:loginFocus();"><span class="fas fa-chevron-circle-left"></span>&nbsp;Already registered?  Login here.</a>
	</div><?php
}
?>
