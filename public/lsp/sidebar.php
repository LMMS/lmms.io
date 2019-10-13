<?php 
require_once('utils.php');
require_once('dbo.php');
global $LSP_URL;

// Determine a successful login
$auth_failure = false;
switch (GET('action')) {
	case 'logout' : logout(); break;
	case 'login' : 
		if (!login()) {
			$auth_failure = true;
		}
		break;
}
?>
<div class="row lsp-row">
<div class="col-md-3">
	<div class="panel panel-custom">
	<div class="panel-heading">
	<form action="<?php echo $LSP_URL; ?>" method="post" role="search">
		<input type="hidden" name="category" value="<?php echo GET('category'); ?>" />
		<input type="hidden" name="subcategory" value="<?php echo GET('subcategory'); ?>" />
		<div class="input-group">
			<span class="input-group-addon">
				<?php $checked = GET('commentsearch', false) ? 'checked' : ''; ?>
				<input type="checkbox" id="commentsearch" name="commentsearch" aria-label="Search comments" 
				data-toggle="tooltip" data-placement="top" title="Search comments" <?php echo $checked; ?>>
      </span>
			<input type="text" id="search" name="search" class="form-control" maxlength="64" placeholder="Search Content" />
			<span class="input-group-btn">
				<button type="submit" id="ok" name="ok" class="btn btn-default"><span class="fas fa-search"></span></button>
			</span>
		</div>
	</form>
	</div>
	<?php get_categories(); ?>
	</div>

	<div id="login-panel" class="panel panel-custom">
		<div class="panel-heading">
			<h3 class="panel-title">
				<a data-toggle="collapse" data-parent="#login-panel" href="#login-collapse">
				<span class="fas fa-user"></span>&nbsp;My Account
				<?php
					// Append username and admin shield to title
					$shield = is_admin(get_user_id(SESSION())) ? '<span class="fas fa-shield-alt"></span>&nbsp;' : '';
					echo SESSION_EMPTY() ? '' : ' <span class="badge pull-right">' . $shield . SESSION() . '</span>';
					// Show auth-fail alert in title for smaller screens
					echo $auth_failure ? '&nbsp;<span class="pull-right fas fa-exclamation-circle"></span>' : '';
				?></a>
			</h3>
		</div>
		<div id="login-collapse" class="panel-collapse collapse in">
			<?php
			if ($auth_failure) {
				echo '<div class="alert alert-danger lsp" role="alert"><i class="fas fa-fw fa-exclamation-circle"></i>&nbsp;<strong>Authentication failure.</strong></div>';
			}

			/*
			 * Hide or show the Login Dialog/My Account Panel
			 */
			if (SESSION_EMPTY()) {
			?>
				<div id="login-div" class="panel-body overflow-hidden">
					<form action="<?php echo $LSP_URL; ?>?action=login" method="post" role="form">
					<div class="form-group">
						<input type="text" id="login" name="login" class="form-control" maxlength="16" placeholder="Username">
					</div>
					<div class="form-group">
					<input type="password" id="password" name="password" class="form-control" maxlength="20" placeholder="Password">
					</div>
					<button type="submit" name="ok" class="btn btn-primary"><span class="fas fa-check"></span>&nbsp;Login</button>
					<input type="hidden" name="file" value="<?php echo GET('file');?>" />
					<input type="hidden" name="category" value="<?php echo GET('category');?>" />
					<input type="hidden" name="subcategory" value="<?php echo GET('subcategory');?>" />
					<input type="hidden" name="oldaction" value="<?php echo GET('action');?>" />
					</form>
					<a href="?action=register"><span class="fas fa-chevron-circle-right"></span>&nbsp;Not registered yet?</a><br />
					<a href="?account=forget"><span class="fas fa-chevron-circle-right"></span>&nbsp;Forgot password?</a>
				</div>
			<?php
			} else {
			?>
				<div class="list-group">
				<a class="list-group-item" href="?content=add">
					<span class="fas fa-fw fa-upload"></span>&nbsp;&nbsp;Add file
				</a>
				<a class="list-group-item" href="?action=browse&user=<?php echo SESSION(); ?>">
					<span class="far fa-fw fa-copy"></span>&nbsp;&nbsp;My files
				</a>
				<a class="list-group-item" href="?account=settings">
					<span class="fas fa-fw fa-cog"></span>&nbsp;&nbsp;Settings
				</a>
				<a class="list-group-item" href="?action=logout
					&oldaction=<?php echo GET('action');?>
					&file=<?php echo GET('file');?>
					&f=<?php echo GET('category');?>
					&subcategory=<?php echo GET('subcategory');?>">
					<span class="fas fa-fw fa-power-off"></span>&nbsp;&nbsp;Logout</a>
				<?php
				if (is_admin(get_user_id(SESSION()))) {
					echo '<p class="badge pull-right"><span class="fas fa-shield-alt"></span>&nbsp;<strong>admin</strong></p>';
				}
				echo '</div>';
			}
			?>
		</div>
	</div>
</div>
