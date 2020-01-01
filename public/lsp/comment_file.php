<?php
require_once('utils.php');
require_once('dbo.php');
require_once('xhtml.php');

global $LSP_URL;
$error = null;
if (!SESSION_EMPTY()) {
	if (!POST('addcomment', false) || POST_EMPTY('text')) {
		if (POST_EMPTY('text') && POST('addcomment', false)) {
			$error = 'Please type a message';
		}
	} else {
		add_visitor_comment(GET('file'), POST('text'), SESSION());
		redirect($LSP_URL . '?action=show&file=' . GET('file') . '#footer');
	}
} else {
	$error = 'Not logged in';
}
echo twig_render('lsp/add_comment.twig', [
	'file_id' => GET('file'),
	'file_name' => get_file_name(GET('file')),
	'titles' => array('Comment', get_file_url()),
	'error' => $error
]);

?>
