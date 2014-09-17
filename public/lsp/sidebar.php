<?php 
require_once('utils.php');
require_once('inc/mysql.inc.php');
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
			<span class="fa fa-user"></span>&nbsp;My Account&nbsp;
			<span id="caret" class="fa"></span>
			<?php 
				// Append username to title
				echo SESSION_EMPTY() ? '' : ' (' . SESSION() . ')'; 
				// Show auth-fail alert in title for smaller screens
				echo $auth_failure ? '&nbsp;<span class="pull-right fa fa-exclamation-circle text-danger"></span>' : '';
			?></a>
		</h3></div>
		<div id="login-collapse" class="panel-collapse collapse in">
		<div id="login-div" class="panel-body overflow-hidden">
			<?php
			
			if ($auth_failure) {
				echo '<span class="text-danger"><strong>Authentication failure.</strong></span><br />';
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
				echo '<a href="?action=register"><span class="fa fa-chevron-circle-right"></span>&nbsp;Not registered yet?</a>';
			} else {
				//echo 'Hello ' . SESSION() . '!<br />';
				echo '<div><ul style="list-style: none; margin-left: -2.5em;">';
				echo '<li><a href="?content=add"><span class="fa fa-upload"></span>&nbsp;&nbsp;Add file</a></li>';
				echo '<li><a href="?action=browse&user=' . SESSION() . '"><span class="fa fa-files-o "></span>&nbsp;&nbsp;My files</a></li>';
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
		$('#caret').removeClass('fa-caret-down');
		$('#caret').addClass('fa-caret-left');
	}
	else {
		$('.collapse').addClass('in');
		$('#caret').removeClass('fa-caret-left');
		$('#caret').addClass('fa-caret-down');
	}   
});

$('.collapse').on('shown.bs.collapse', function(){
	$('#caret').removeClass('fa-caret-left');
	$('#caret').addClass('fa-caret-down');
});

$('.collapse').on('hidden.bs.collapse', function(){
	$('#caret').removeClass('fa-caret-down');
	$('#caret').addClass('fa-caret-left');
});

$(document).ready(function() {
  /*
   * Redirect page if alert contains a data-redirect tag
   */
  $('.alert').each(function() {
		var o = $(this);
		var counter = $('.redirect-counter:first');
		var timeout = counter.length ? parseInt(counter.text()) * 1000 : 5000;
		if (o.data('redirect').length) {
			window.setTimeout(function() {window.location = o.data('redirect');}, timeout);
			countDown(false);
		}
  });
  
  /*
   * Focus to comment text-area if it is on the screen
   */
   commentFocus();
 
});

function countDown(decrement) {
	var counter = $('.redirect-counter:first');
	if (counter.length) {
		if (decrement) {
			counter.text(parseInt(counter.text()) - 1);
		}
		if (counter.text() == '-1') {
			counter.remove();
			return;
		} else {
			window.setTimeout(function() { countDown(true); }, 1000);
			return;
		}
	}
}

function commentFocus() {
	var comment = $('#comment');
	if (comment.length) {
		comment.focus();
	}
}

function loginFocus() {
	javascript:$('#login-collapse').addClass('in');
	$('#login').focus();
	$('#login').fadeIn(100).fadeOut(100).fadeIn(100).fadeOut(100).fadeIn(100);
}

</script>
