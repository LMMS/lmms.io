<?php
require_once('utils.php');
require_once('dbo.php');
require_once('xhtml.php');

global $LSP_URL;
if (!SESSION_EMPTY() && 
	(get_user_id(SESSION()) == get_file_owner(GET('file')) || is_admin(get_user_id(SESSION())))) {
	if (POST('confirmation') == "true") {
		if (delete_file(GET('file'))) {
			display_success('File deleted successfully', array('Delete'));
		} else {
			display_error('Sorry, file ' . GET('file') . ' could not be deleted', array('Delete'));
		}
		get_latest();
	} else {
		display_warning('This will delete all comments and ratings.', array('Delete', get_file_url()));
		echo '<div class="col-md-9">';
		$form = new form("$LSP_URL?content=delete&file=" . GET('file'), 'Confirm Delete', 'fa-trash'); ?>
		<input type="hidden" name="confirmation" value="true"></input>
		<p class="lead">Confirm deletion of <strong><?php echo get_file_name(GET('file')); ?></strong>?</p>
		<div class="form-group">
		<button type="submit" class="btn btn-danger">
		<span class="fas fa-check"></span>&nbsp;Delete</button>
		<a class="btn btn-warning" href="<?php echo "$LSP_URL?action=show&file=" . GET('file'); ?>">
		<span class="fas fa-times"></span>&nbsp;Cancel</a>
		</form>
		<?php $form->close(); echo '</div>';
	}
} else {
	show_file(GET('file'), SESSION(), false);
}

?>
