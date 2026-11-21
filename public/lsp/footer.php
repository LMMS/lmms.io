</div>
<?php
require_once('polyfill.php');
echo $twig->render('foot.twig');
?>
<script>
$(function () {
	$("[data-toggle='tooltip']").tooltip();
});

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
