<?php
require_once('utils.php');
global $LSP_URL;
if (!SESSION_EMPTY()) {
	if (!POST('addcomment', false) || POST_EMPTY('text')) {
		if (POST_EMPTY('text') && POST('addcomment', false)) {
			display_error('Please type a message', array('Comment', get_file_url()));
		} else {
			display_warning('Do not submit offending, pornographic, racist or violent content.', array('Comment', get_file_url()));
		}	
		?>
		<div class="col-md-9"><div class="panel panel-default">
		<div class="panel-heading"><h3 class="panel-title">
		<span class="fa fa-comment"></span>&nbsp;Comment</h3></div>
		<?php $form = new form($LSP_URL . '?comment=add&' . file_show_query_string()); ?>
		<div class="panel-body">
		<?php	$form = new form($LSP_URL . "?action=register"); ?>
		<div class="form-group">
		<label for="text">Add comment to "<?php echo get_file_name(GET('file')); ?>"</label>
		<textarea id="comment" name="text" class="form-control"></textarea>
		</div>
		<button type="submit" class="btn btn-primary" name="addcomment" value="Comment"><span class="fa fa-check"></span>&nbsp;Comment</button>&nbsp;
		<a href="<?php echo $LSP_URL . '?action=show&file=' . GET('file'); ?>" class="btn btn-warning"></span><span class="fa fa-close"></span>&nbsp;Cancel</a>
		<?php $form->close(); ?>
		</div></div>

		<?php
	} else {
		add_visitor_comment(GET('file'), POST('text'), SESSION());
		display_success('Comment posted successfully', array('Comment', get_file_url()), $LSP_URL . '?action=show&file=' . GET('file') . '#footer');
	}
} else {
	display_error('Not logged in', array('Comment', get_file_url()));
}

?>
