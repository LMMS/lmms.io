<?php
require_once('utils.php');
require_once('dbo.php');

global $LSP_URL;
// This next line was cherry-picked from index.html. Doesn't it do the same thing as what's already here?
//if(get_user_id(SESSION()) == get_object_by_id("files", $_GET['file'], "user_id"))
if (!SESSION_EMPTY() && 
	(get_user_id(SESSION()) == get_file_owner(GET('file')) || is_admin(get_user_id(SESSION())))) {
	if (GET('confirmation') == "true" ) {
		echo '<div class="alert alert-success center"><strong>Info:</strong> File has been deleted</div>';
		get_latest();
	} else {
		echo '<div class="col-md-9">';
		create_title(array('Delete', get_file_url()));
		echo '<table class="table table-striped">';
		echo "<br /><span>Please confirm deletion of <i>\"" . get_file_name(GET('file')) . "\"</i> with all its comments and ratings?</span> <br /><br />";
		echo '&nbsp; &nbsp;&nbsp; <b><a class="btn btn-danger href="'.$LSP_URL.'?content=delete&confirmation=true&file=' . GET('file') . '">Delete</a>';
		echo ' <a class="btn btn-warning" href="' . $LSP_URL . '?action=show&file=' . GET('file') . '">Cancel</a></b>';
		echo '</table></div>';
	}
}
else
{
	echo "<br /><span style=\"font-weight:bold; color:#f80; font-size:12pt;\">You're not allowed to delete this file!!!</span> <br /><br />";
	show_file( $_GET["file"], $_SESSION["remote_user"] );
}

?>

