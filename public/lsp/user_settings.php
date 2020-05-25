<?php
require_once('utils.php');
require_once('dbo.php');
require_once('xhtml.php');
global $LSP_URL;

function apply_settings($password, $password2, $realname, $aboutme) {
	global $LSP_URL;
	if( $password != $password2 ) { 
		display_error('Password mismatch');
		return false;
	} else if(strlen($realname) > 50) {
		display_error("Full name cannot be more than 50 characters long");
		return false;
	} else {
		if (change_user(SESSION(), $realname, $password, $aboutme)) {
			display_success('Account settings have been updated', array('<a href="">User Settings</a>', 'Success'), $LSP_URL . "?account=settings");
		} else {
			return false;
		}
		return true;
	}
}

if ((POST('settings') != "apply" ) || (!apply_settings(POST('password'), POST('password2'), POST('realname'), POST('aboutme')))) {
	echo '<div class="col-md-9">';
	create_title('<a href="">User Settings</a>');
	$form = new form("$LSP_URL?account=settings", 'User Settings', 'fa-cog'); ?>
	<div class="form-group">
	<label for="username" class="text-muted">Username:</label>
	<input type="text" name="username" class="form-control" value="<?php echo SESSION(); ?>" disabled="disabled" />
	<p class="help-block">Username cannot be changed</p>
	</div>
	<div class="form-group">
	<label for="realname">Full Name:</label>
	<input type="text" name="realname" class="form-control" value="<?php echo get_user_realname(SESSION()); ?>" />
	</div>
	<div class="form-group">
	<label for="aboutme">About Me:</label>
	<textarea class="form-control" maxlength="1000" name="aboutme" style="resize: none;"><?php echo get_user_about(SESSION()); ?></textarea>
	</div>
	<div class="form-group">
	<label for="pfp">Profile Picture:</label>
	<input type="file" name="pfp" class="form-control" accept="image/png" />
	</div>
	<div class="form-group">
	<label for="password">New Password:</label>
	<input type="password" name="password" maxlength="20" class="form-control"/>
	</div>
	<div class="form-group">
	<label for="password2">Confirm New Password:</label>
	<input type="password" class="form-control" maxlength="20" name="password2" />
	</div>
	<button class="btn btn-primary" type="submit" name="settings" value="apply">
	<span class="fas fa-check"></span>&nbsp;Apply</button>
	<a href="<?php echo $LSP_URL; ?>" class="btn btn-warning"><span class="fas fa-times"></span>&nbsp;Cancel</a>
	<?php $form->close(); echo '</div>';
}
?>
