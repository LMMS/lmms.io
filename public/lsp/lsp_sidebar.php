<?php 
require_once('lsp_utils.php');
require_once('inc/mysql.inc.php');
global $LSP_URL;
?>

<div class="page-header"><h1>LMMS Sharing Platform</h1></div>
<div class="row lsp-row">
<div class="col-md-3">
	<div class="panel panel-default">
	<div class="panel-heading">
	<form action="<?php echo $LSP_URL; ?>" method="post" role="search">
		<input type="hidden" name="category" value="<?php echo GET('category'); ?>" />
		<input type="hidden" name="subcategory" value="<?php echo GET('subcategory'); ?>" />
		<input type="text" id="search" name="search" class="lsp-search form-control textin" maxlength="64" placeholder="Search Content" />
		<div class="lsp-search-button">
			<button type="submit" id="ok" name="ok" class="lsp-search btn btn-default textin"><span class="fa fa-search"></span></button>
		</div>
	</form>
	</div>
	<?php get_categories(); ?>
	</div>

	<div id="login-panel" class="panel panel-default">
		<div class="panel-heading"><h3 class="panel-title">
			<a data-toggle="collapse" data-parent="#login-panel" href="#login-collapse">
			<span class="fa fa-user"></span>&nbsp;My Account<?php echo SESSION_EMPTY() ? '' : ' (' . SESSION() . ')'; ?></a>
		</h3></div>
		<div id="login-collapse" class="panel-collapse collapse in">
		<div id="login" class="panel-body overflow-hidden">
			<?php
			if (GET('action') == 'logout') {
				unset ($_SESSION["remote_user"]);
				session_destroy();
				$_GET["action"] = GET('oldaction');
				if (GET('action') != "browse" && GET('action') != "show" &&	GET('action') != "" ) {
					$_GET["action"] = "show";
				}
			}

			if (SESSION_EMPTY() && GET('action') == 'login') {
				if (password_match(POST('password'), POST('login'))) {
					$_SESSION["remote_user"] = POST('login');
					$_GET["action"] = POST('oldaction');
					set_get_post('category');
					set_get_post('subcategory');
				} else /*if ($_POST["ok"] == 'Login')*/	{
					echo '<span class="text-danger"><strong>Authentication failure.</strong></span><br />';
				}
			}
			
			/*
			 * Hide or show the Login Dialog/My Account Panel
			 */
			if (SESSION_EMPTY()) {
				echo '<form action="' . $LSP_URL . '?action=login" method="post" role="form">';
				echo '<div class="form-group">';
				echo '<label for="login">Username</label>';
				echo '<input type="text" id="login" name="login" class="form-control textin" maxlength="10" placeholder="username" />';
				echo '</div>';
				echo '<div class="form-group">';
				echo '<label for="password">Password</label>';
				echo '<input type="password" id="password" name="password" class="form-control textin" maxlength="15" placeholder="password"/>';
				echo '</div>';
				echo '<button type="submit" name="ok" class="btn btn-default textin" />Login</button>';
				echo '</form>';

				echo '<input type="hidden" name="file" value="' . GET('file') . '" />'."\n";
				echo '<input type="hidden" name="category" value="' . GET('category') . '" />'."\n";
				echo '<input type="hidden" name="subcategory" value="' . GET('subcategory') . '" />'."\n";
				echo '<input type="hidden" name="oldaction" value="' . GET('action') . '" />'."\n";
				echo '</p></form><br />';
				echo '<a href="?action=register"><span class="fa  fa-chevron-circle-right"></span>&nbsp;Not registered yet?</a>';
			} else {
				//echo 'Hello ' . SESSION() . '!<br />';
				echo '<div><ul style="list-style: none; margin-left: -2.5em;">';
				echo '<li><a href="?content=add"><span class="fa fa-upload"></span>&nbsp;&nbsp;Add file</a></li>';
				echo '<li><a href="?action=browse&user=' . SESSION() . '"><span class="fa fa-user"></span>&nbsp;&nbsp;My files</a></li>';
				echo '<li><a href="?account=settings"><span class="fa fa-gear"></span>&nbsp;&nbsp;Settings</a></li>';
				echo '<li><a href="?action=logout&oldaction=' . GET('action') . '&file=' . 
					GET('file') .'&f=' . GET('category') . '&subcategory=' . GET('subcategory') . 
					'"><span class="fa fa-power-off"></span>&nbsp;&nbsp;Logout</a></li>';
				echo '</ul></div>';
			}
			?>
		</div>
		</div>
	</div>
</div>
<script>
$(window).bind('resize load',function(){
	if( $(this).width() < 962 ) {
		$('.collapse').removeClass('in');
		$('.collapse').addClass('out');
	}
	else {
		$('.collapse').removeClass('out');
		$('.collapse').addClass('in');
	}   
});
</script>
