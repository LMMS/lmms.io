<?php
require_once('utils.php');
require_once('dbo.php');
require_once('xhtml.php');
global $LSP_URL;

function apply_settings($password, $password2, $realname, $email) {
	global $LSP_URL;
	if( $password != $password2 ) { 
		display_error('Password mismatch');
		return false;
	} else {
		change_user(SESSION(), $realname, $password, $email);
		display_success('Account settings have been updated', array('<a href="">User Settings</a>', 'Success'), $LSP_URL . "?account=settings");
		return true;
	}
}

if (SESSION() == null) {
	display_error("Please login first.",
	array("<a href=\"#\">User Settings</a>"),
	$LSP_URL, 5
	);
	return;
}

if ((POST('settings') != "apply" ) || (!apply_settings(POST('password'), POST('password2'), POST('realname'), POST('email')))) {
	echo '<div class="col-md-9">';
	create_title('<a href="">User Settings</a>');
	$form = new form("$LSP_URL?account=settings", 'User Settings', 'fa-cog'); ?>
	<div class="form-group">
	<label for="username" class="text-muted">User Name:</label>
	<input type="text" name="username" class="form-control" value="<?php echo SESSION(); ?>" disabled="disabled" />
	<p class="help-block">User name cannot be changed</p>
	</div>
	<div class="form-group">
	<label for="realname">Full Name:</label>
	<input type="text" name="realname" class="form-control" value="<?php echo get_user_realname(SESSION()); ?>" />
	</div>
	<div class="form-group">
	<label for="realname">Email address:</label>
	<input type="email" name="email" class="form-control" value="<?php echo get_user_email(SESSION()); ?>" />
	<p class="help-block">
		<?php
			echo '<div class="alert alert-';
			if (get_if_user_email_verified(SESSION()) < 1) {
				echo 'danger">';
				echo '<span class="fas fa-exclamation-circle"></span>&nbsp;Email address not yet verified.&nbsp;';
				echo '<a href="' . $LSP_URL . '/?email=send">Verify email</a>';
			} else {
				echo 'success">';
				echo '<span class="fas fa-check"></span>&nbsp;Email address has been verified';
			}
			echo '</div>';
		?>
	</p>
	</div>
	<div class="form-group">
	<label for="password">Password:</label>
	<input type="password" name="password" maxlength="20" class="form-control"/>
	</div>
	<div class="form-group">
	<label for="password2">Confirm Password:</label>
	<input type="password" class="form-control" maxlength="20" name="password2" />
	</div>
	<button class="btn btn-primary" type="submit" name="settings" value="apply">
	<span class="fas fa-check"></span>&nbsp;Apply</button>
	<a href="<?php echo $LSP_URL; ?>" class="btn btn-warning"><span class="fas fa-times"></span>&nbsp;Cancel</a>
	<?php $form->close(); echo '</div>';
}
?>
