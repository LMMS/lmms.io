<?php
require_once('utils.php');
require_once('dbo.php');
require_once('xhtml.php');

global $LSP_URL;
if (!SESSION_EMPTY() && 
	(get_user_id(SESSION()) == get_file_owner(GET('file')) || is_admin(get_user_id(SESSION())))) {
	if (GET('confirmation') == "true" ) {
		if (delete_file(GET('file'))) {
			display_success('File deleted successfully', array('Delete'));
		} else {
			display_error('Sorry, file ' . GET('file') . ' could not be deleted', array('Delete'));
		}
		get_latest();
	} else {
		echo twig_render('lsp/delete_file.twig', [
			'file_name' => get_file_name(GET('file')),
			'file_id' => GET('file')
		]);
	}
} else {
	redirect($LSP_URL . '?action=show&file=' . GET('file'));
}

?>
