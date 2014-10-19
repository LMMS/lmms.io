<?php
require_once('utils.php');
require_once('dbo.php');
require_once('xhtml.php');

global $LSP_URL;
if (!SESSION_EMPTY() && 
	(get_user_id(SESSION()) == get_file_owner(GET('file')) || is_admin(get_user_id(SESSION())))) {
	if (GET('confirmation') == "true" ) {
		display_success('File deleted successfully', array('Delete'));
		get_latest();
	} else {
		display_warning('This will delete all comments and ratings.', array('Delete', get_file_url()));
		echo '<div class="col-md-9">';
		$form = new form(null, 'Confirm Delete', 'fa-trash'); ?>
		<p class="lead">Confirm deletion of <strong><?php echo get_file_name(GET('file')); ?></strong>?</p>
		<div class="form-group">
		<a class="btn btn-danger" href="<?php echo "$LSP_URL?content=delete&confirmation=true&file=" . GET('file'); ?>">
		<span class="fa fa-check"></span>&nbsp;Delete</a>
		<a class="btn btn-warning" href="<?php echo "$LSP_URL?action=show&file=" . GET('file'); ?>">
		<span class="fa fa-close"></span>&nbsp;Cancel</a>
		</form>
		<?php $form->close(); echo '</div>';
	}
} else {
	show_file(GET('file'), SESSION(), false);
}

?>

