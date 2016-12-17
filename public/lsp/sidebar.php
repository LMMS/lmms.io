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

<div class="page-header text-center"><h1>LMMS Sharing Platform</h1></div>
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
		<div class="lsp-search-comments">
			<?php $checked = GET('commentsearch', false) ? 'checked' : ''; ?>
			<input type="checkbox" title="Search comments" id="commentsearch" class="pull-right" name="commentsearch" <?php echo $checked; ?>><span class="fa fa-comments"></span></input>
		</div>
	</form>
	</div>
	<?php get_categories(); ?>
	</div>

	<div id="login-panel" class="panel panel-default">
		<div class="panel-heading"><h3 class="panel-title">
			<a data-toggle="collapse" data-parent="#login-panel" href="#login-collapse">
			<span id="caret" class="fa"></span>&nbsp;<span class="fa fa-user"></span>&nbsp;My Account&nbsp;
			
			<?php 
				// Append username and admin shield to title
				$shield = is_admin(get_user_id(SESSION())) ? '<span class="fa fa-shield"></span>&nbsp;' : '';
				echo SESSION_EMPTY() ? '' : ' <span class="badge pull-right">' . $shield . SESSION() . '</span>'; 
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
			if (SESSION_EMPTY()) {?>
				<form action="<?php echo $LSP_URL; ?>?action=login" method="post" role="form">
				<div class="form-group">
				<label for="login">User Name</label>
				<input type="text" id="login" name="login" class="form-control textin" maxlength="16" placeholder="username" />
				</div>
				<div class="form-group">
				<label for="password">Password</label>
				<input type="password" id="password" name="password" class="form-control textin" maxlength="20" placeholder="password"/>
				</div>
				<button type="submit" name="ok" class="btn btn-primary textin"><span class="fa fa-check"></span>&nbsp;Login</button>
				</form>

				<input type="hidden" name="file" value="<?php echo GET('file');?>" />
				<input type="hidden" name="category" value="<?php echo GET('category');?>" />
				<input type="hidden" name="subcategory" value="<?php echo GET('subcategory');?>" />
				<input type="hidden" name="oldaction" value="<?php echo GET('action');?>" />
				</form>
				<a href="?action=register"><span class="fa fa-chevron-circle-right"></span>&nbsp;Not registered yet?</a><?php
			} else { ?>
				<div><ul style="list-style: none; margin-left: -2.5em;">
				<li><a href="?content=add"><span class="fa fa-upload"></span>&nbsp;&nbsp;Add file</a></li>
				<li><a href="?action=browse&user=<?php echo SESSION(); ?>"><span class="fa fa-files-o "></span>&nbsp;&nbsp;My files</a></li>
				<li><a href="?account=settings"><span class="fa fa-gear"></span>&nbsp;&nbsp;Settings</a></li>
				<li><a href="?action=logout&oldaction=<?php echo GET('action');?>&file=<?php echo GET('file');?>
					&f=<?php echo GET('category');?>&subcategory=<?php echo GET('subcategory');?>">
					<span class="fa fa-power-off"></span>&nbsp;&nbsp;Logout</a></li>
				</ul><?php
				if (is_admin(get_user_id(SESSION()))) {
					echo '<p class="badge pull-right"><span class="fa fa-shield"></span>&nbsp;<strong>admin</strong></p>';
				}
				echo '</div>';
			}
			?>
		</div>
		</div>
	</div>
</div>
<script>

$(window).bind('resize load',function(){
	if ($(this).width() < 962) { collapse_in('#caret'); }
	else { collapse_out('#caret'); }
});

function collapse_out(item_id) {
	$('.collapse').not('.navbar-collapse').addClass('in');
	$(item_id).removeClass('fa-caret-right');
	$(item_id).addClass('fa-caret-down');
}

function collapse_in(item_id) {
	if ($('#login').is(":focus") || $('#password').is(":focus")) {
		return;
	}
	$('.collapse').not('.navbar-collapse').removeClass('in');
	$(item_id).removeClass('fa-caret-down');
	$(item_id).addClass('fa-caret-right');
}

$('.collapse').not('.navbar-collapse').on('shown.bs.collapse', function(){collapse_out('#caret')});
$('.collapse').not('.navbar-collapse').on('hidden.bs.collapse', function(){collapse_in('#caret')});

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
	blink('#login');
}

function blink(item_id) {
	$(item_id).fadeIn(100).fadeOut(100).fadeIn(100).fadeOut(100).fadeIn(100).fadeOut(100).fadeIn(100);
}

/*
 * Bootstrap file button listener
 */
$(document).on('change', '.btn-file :file', function() {
    var input = $(this),
        numFiles = input.get(0).files ? input.get(0).files.length : 1,
        label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
		$('#file-selected').html(label ? label : "No file selected");
		$('#file-selected').removeClass().addClass(label ? 'text-primary' : 'text-danger');
		blink('#file-selected');
});

</script>
