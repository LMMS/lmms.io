<?php
global $LSP_URL;

function apply_settings($password, $password2, $realname) {
	global $LSP_URL;
	if( $password != $password2 ) { 
		display_error('Password mismatch');
		return false;
	} else {
		change_user(SESSION(), $realname, $password);
		display_success('Account settings have been updated', array('<a href="">User Settings</a>', 'Success'), $LSP_URL . "?account=settings");
		return true;
	}
}

if ((POST('settings') != "apply" ) || (!apply_settings(POST('password'), POST('password2'), POST('realname')))) {
	create_title('<a href="">User Settings</a>');
	?>
	<div class="col-md-9"><div class="panel panel-default">
	<div class="panel-heading"><h3 class="panel-title">
	<span class="fa fa-gear"></span>&nbsp;User Settings</h3></div>
	<div class="panel-body">
	<?php $form = new form($LSP_URL . "?account=settings"); ?>
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
	<label for="password">Password:</label>
	<input type="password" name="password" class="form-control"/>
	</div>
	<div class="form-group">
	<label for="password2">Confirm Password:</label>
	<input type="password" class="form-control" name="password2" />
	</div>
	<button class="btn btn-primary" type="submit" name="settings" value="apply">
	<span class="fa fa-check"></span>&nbsp;Apply</button>
	<?php $form->close(); ?>
	</div></div></div><?php
}
?>

